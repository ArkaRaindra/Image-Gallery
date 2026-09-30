<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

#[Fillable(['name', 'email', 'password', 'avatar_path', 'role'])]
#[Hidden(['password', 'remember_token', 'owner_slot'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
     * Moderator or above (admin, owner). Can delete posts and comments.
     */
    public function isModerator(): bool
    {
        return $this->hasRoleAtLeast(self::ROLE_MODERATOR);
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
            static::query()
                ->where('role', self::ROLE_OWNER)
                ->whereKeyNot($this->getKey())
                ->update(['role' => self::ROLE_ADMIN]);

            $this->role = self::ROLE_OWNER;
            $this->save();
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }
}