<?php

namespace App\Support;

use App\Filament\Resources\DeletionReports\DeletionReportResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\DeletionReport;
use App\Models\Post;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Bell notifications shown in the Filament panel.
 *
 * Admins hear about work waiting for them (posts to approve, deletion
 * requests). The owner hears about changes to the user base (new users, role
 * changes). Each notification goes to one group only.
 */
class PanelNotifications
{
    public static function postNeedsApproval(Post $post): void
    {
        $uploader = $post->uploader?->name ?? 'Unknown';

        $notification = Notification::make()
            ->title('Post waiting for approval')
            ->body("Post #{$post->getKey()} by {$uploader} needs to be approved.")
            ->icon(Heroicon::Clock)
            ->iconColor('warning')
            ->actions([
                Action::make('review')
                    ->label('Review')
                    ->url(PostResource::getUrl('edit', ['record' => $post]))
                    ->markAsRead(),
            ]);

        self::deliver($notification, self::admins());
    }

    public static function deletionRequested(DeletionReport $report): void
    {
        $reporter = $report->reporter?->name ?? 'A moderator';
        $reason = Str::limit(trim(preg_replace('/\s+/', ' ', $report->reason)), 120);

        $notification = Notification::make()
            ->title('Deletion request')
            ->body("{$reporter} asked to delete {$report->subject_label}. Reason: {$reason}")
            ->icon(Heroicon::Flag)
            ->iconColor('danger')
            ->actions([
                Action::make('review')
                    ->label('Review')
                    ->url(DeletionReportResource::getUrl('index'))
                    ->markAsRead(),
            ]);

        self::deliver($notification, self::admins());
    }

    public static function userRegistered(User $user): void
    {
        $notification = Notification::make()
            ->title('New user')
            ->body("{$user->name} ({$user->email}) joined.")
            ->icon(Heroicon::UserPlus)
            ->iconColor('success')
            ->actions([
                Action::make('view')
                    ->label('View user')
                    ->url(UserResource::getUrl('edit', ['record' => $user]))
                    ->markAsRead(),
            ]);

        self::deliver($notification, self::owners(except: $user));
    }

    public static function userRoleChanged(User $user, ?string $from, string $to): void
    {
        $changedBy = auth()->user()?->name ?? 'the console';

        $notification = Notification::make()
            ->title('User role changed')
            ->body("{$user->name}: ".ucfirst((string) $from).' → '.ucfirst($to).", changed by {$changedBy}.")
            ->icon(Heroicon::ArrowsRightLeft)
            ->iconColor('info')
            ->actions([
                Action::make('view')
                    ->label('View user')
                    ->url(UserResource::getUrl('edit', ['record' => $user]))
                    ->markAsRead(),
            ]);

        self::deliver($notification, self::owners(except: $user));
    }

    /**
     * Store the notification for each recipient right away.
     *
     * Filament's database notification is queued by default, which means
     * nothing shows up until a queue worker runs. Delivering it now keeps the
     * bell working without a worker.
     *
     * @param  Collection<int, User>  $recipients
     */
    protected static function deliver(Notification $notification, Collection $recipients): void
    {
        foreach ($recipients as $recipient) {
            $recipient->notifyNow($notification->toDatabase());
        }
    }

    /**
     * Admins who can open the panel. The owner is not included.
     *
     * @return Collection<int, User>
     */
    protected static function admins(): Collection
    {
        return User::query()
            ->where('role', User::ROLE_ADMIN)
            ->get()
            ->filter(fn (User $admin): bool => $admin->can(Permissions::ACCESS_ADMIN_PANEL));
    }

    /**
     * The owner, unless the notification is about the owner themselves.
     *
     * @return Collection<int, User>
     */
    protected static function owners(User $except): Collection
    {
        return User::query()
            ->where('role', User::ROLE_OWNER)
            ->whereKeyNot($except->getKey())
            ->get();
    }
}