<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\PanelNotifications;
use App\Support\Permissions;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'avatar_path', 'role'])]
#[Hidden(['password', 'remember_token', 'owner_slot'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public const ROLE_OWNER = 'owner';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_MODERATOR = 'moderator';

    public const ROLE_MEMBER = 'member';

    /**
     * Every role with its rank. A higher number means more authority.
     *
     * @var array<string, int>
     */
    public const ROLE_LEVELS = [
        self::ROLE_OWNER => 4,
        self::ROLE_ADMIN => 3,
        self::ROLE_MODERATOR => 2,
        self::ROLE_MEMBER => 1,
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => self::ROLE_MEMBER,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $user): void {
            if (! $user->isDirty('role')) {
                return;
            }

            $wasOwner = $user->getOriginal('role') === self::ROLE_OWNER;

            if ($wasOwner && $user->role !== self::ROLE_OWNER) {
                throw ValidationException::withMessages([
                    'role' => 'The owner role cannot be removed. Transfer ownership to another user instead.',
                ]);
            }

            if ($user->role === self::ROLE_OWNER && ! $wasOwner) {
                $ownerExists = static::query()
                    ->where('role', self::ROLE_OWNER)
                    ->when($user->exists, fn ($query) => $query->whereKeyNot($user->getKey()))
                    ->exists();

                if ($ownerExists) {
                    throw ValidationException::withMessages([
                        'role' => 'There can only be one owner.',
                    ]);
                }
            }
        });

        static::created(function (self $user): void {
            $user->syncSpatieRole();

            PanelNotifications::userRegistered($user);
        });

        static::updated(function (self $user): void {
            if (! $user->wasChanged('role')) {
                return;
            }

            $user->syncSpatieRole();

            PanelNotifications::userRoleChanged($user, $user->getOriginal('role'), (string) $user->role);
        });

        static::deleting(function (self $user): ?bool {
            return $user->isOwner() ? false : null;
        });
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'favorites')
            ->withTimestamps()
            ->latest('favorites.created_at');
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path
            ? Storage::disk('public')->url($this->avatar_path)
            : null;
    }

    /**
     * Select options (value => label) for the given roles, or all roles when null.
     *
     * @param  list<string>|null  $only
     * @return array<string, string>
     */
    public static function roleOptions(?array $only = null): array
    {
        $roles = array_keys(self::ROLE_LEVELS);

        if ($only !== null) {
            $roles = array_values(array_intersect($roles, $only));
        }

        return array_combine($roles, array_map('ucfirst', $roles));
    }

    /**
     * Sort by role rank instead of alphabetically: owner, admin, moderator,
     * member. Unknown roles fall to the bottom.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeOrderByRole(Builder $query, string $direction = 'desc'): Builder
    {
        $cases = [];
        $bindings = [];

        foreach (self::ROLE_LEVELS as $role => $level) {
            $cases[] = 'when ? then ?';
            $bindings[] = $role;
            $bindings[] = $level;
        }

        $roleColumn = $query->getModel()->qualifyColumn('role');

        $sql = "case {$roleColumn} ".implode(' ', $cases).' else -1 end';

        return $query->orderByRaw("{$sql} {$direction}", $bindings);
    }

    /**
     * The username as it is written inside a search token (user:name, fav:name).
     * Tokens are split on spaces, so spaces become underscores.
     */
    public function searchName(): string
    {
        return str_replace(' ', '_', (string) $this->name);
    }

    /**
     * Where each profile stat leads to. Shared by the username hover card and
     * the account page so both open the same pages. null = nothing to open
     * (the feature does not exist yet).
     *
     * @return array<string, string|null>
     */
    public function statLinks(): array
    {
        return [
            'uploads' => route('posts.index', ['tags' => 'user:'.$this->searchName()]),
            'tag_edits' => null,
            'note_edits' => route('notes.changes', ['updater' => $this->name]),
            'favorites' => route('posts.index', ['tags' => 'fav:'.$this->searchName()]),
            'comments' => route('comments.search', ['commenter' => $this->name, 'user_id' => $this->id]),
            'forum_posts' => null,
        ];
    }

    public function roleLevel(): int
    {
        return self::ROLE_LEVELS[$this->role] ?? 0;
    }

    public function roleLabel(): string
    {
        return ucfirst((string) $this->role);
    }

    /**
     * Tailwind text color for this user's role. Shared by the role badge and
     * the username so both always match.
     */
    public function roleTextClass(): string
    {
        return match ($this->role) {
            self::ROLE_OWNER => 'text-amber-700',
            self::ROLE_ADMIN => 'text-red-700',
            self::ROLE_MODERATOR => 'text-green-700',
            default => 'text-sky-700',
        };
    }

    /**
     * Same role color as roleTextClass(), tuned for dark backgrounds
     * (used by the username hover card).
     */
    public function roleDarkTextClass(): string
    {
        return match ($this->role) {
            self::ROLE_OWNER => 'text-amber-400',
            self::ROLE_ADMIN => 'text-red-400',
            self::ROLE_MODERATOR => 'text-green-400',
            default => 'text-sky-400',
        };
    }

    /**
     * Tailwind background color for this user's role badge.
     */
    public function roleBadgeBgClass(): string
    {
        return match ($this->role) {
            self::ROLE_OWNER => 'bg-amber-100',
            self::ROLE_ADMIN => 'bg-red-100',
            self::ROLE_MODERATOR => 'bg-green-100',
            default => 'bg-sky-200',
        };
    }

    public function hasRoleAtLeast(string $role): bool
    {
        return $this->roleLevel() >= (self::ROLE_LEVELS[$role] ?? PHP_INT_MAX);
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    /**
     * Admin or above (owner).
     */
    public function isAdmin(): bool
    {
        return $this->hasRoleAtLeast(self::ROLE_ADMIN);
    }

    /**
     * Moderator or above (admin, owner). Can remove posts and comments, but a
     * plain moderator only by filing a deletion request (see mustRequestDeletion()).
     */
    public function isModerator(): bool
    {
        return $this->hasRoleAtLeast(self::ROLE_MODERATOR);
    }

    /**
     * Admin or owner: posts and comments are deleted straight away.
     */
    public function canDeleteDirectly(): bool
    {
        return $this->isAdmin();
    }

    /**
     * A moderator below admin level cannot delete a post or comment. They
     * file a deletion request with a reason, and an admin or owner decides.
     */
    public function mustRequestDeletion(): bool
    {
        return $this->isModerator() && ! $this->isAdmin();
    }

    /**
     * Moderators, admins and the owner have moderation numbers on their
     * account and profile pages (deleted posts, appeals).
     */
    public function hasModerationStats(): bool
    {
        return $this->isModerator();
    }

    /**
     * @return array{deleted_posts: int, appeals: int}
     */
    public function moderationStats(): array
    {
        return [
            'deleted_posts' => DeletionReport::deletedPostsCountFor($this),
            'appeals' => $this->approvedPostsCount(),
        ];
    }

    /**
     * Posts this user approved to appear on the public site. Posts that are
     * deleted or hidden by a pending deletion request are not counted.
     */
    public function approvedPostsCount(): int
    {
        return Post::query()->approved()->where('approved_by', $this->getKey())->count();
    }

    /**
     * Mirror the role column onto the Spatie role of the same name.
     *
     * The role column stays the source of truth for rank and the single owner
     * rule; the Spatie role is what carries the permissions.
     */
    public function syncSpatieRole(): void
    {
        $this->syncRoles([Role::findOrCreate((string) $this->role, 'web')]);
    }

    public function outranks(self $other): bool
    {
        return $this->roleLevel() > $other->roleLevel();
    }

    /**
     * A user can manage themselves and any user with a lower role.
     */
    public function canManage(self $target): bool
    {
        return $this->is($target) || $this->outranks($target);
    }

    /**
     * Roles this user is allowed to hand out: only roles below their own.
     * The owner role is never assignable, use becomeOwner() to transfer it.
     *
     * @return list<string>
     */
    public function assignableRoles(): array
    {
        return array_values(array_filter(
            array_keys(self::ROLE_LEVELS),
            fn (string $role): bool => $this->roleLevel() > self::ROLE_LEVELS[$role],
        ));
    }

    /**
     * Make this user the owner. The previous owner (if any) becomes an admin.
     */
    public function becomeOwner(): void
    {
        DB::transaction(function (): void {
            $previousOwnerIds = static::query()
                ->where('role', self::ROLE_OWNER)
                ->whereKeyNot($this->getKey())
                ->pluck('id');

            static::query()
                ->whereKey($previousOwnerIds)
                ->update(['role' => self::ROLE_ADMIN]);

            $this->role = self::ROLE_OWNER;
            $this->save();

            static::query()
                ->whereKey($previousOwnerIds)
                ->get()
                ->each(function (self $previousOwner): void {
                    $previousOwner->syncSpatieRole();

                    PanelNotifications::userRoleChanged($previousOwner, self::ROLE_OWNER, self::ROLE_ADMIN);
                });
        });
    }

    /**
     * The owner always gets in. An admin gets in while the admin role holds the
     * panel permission. Moderators and members never get in, even if the
     * permission is handed to their role.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isOwner()
            || ($this->isAdmin() && $this->can(Permissions::ACCESS_ADMIN_PANEL));
    }
}
