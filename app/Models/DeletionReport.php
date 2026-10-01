<?php

namespace App\Models;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A moderator's request to delete a post or comment. While the request is
 * pending the subject is hidden from the site; an admin or owner then either
 * approves it (the subject is deleted) or rejects it (the subject comes back).
 */
class DeletionReport extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'reportable_type',
        'reportable_id',
        'reporter_id',
        'subject_label',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForSubject(Builder $query, Model $subject): Builder
    {
        return $query
            ->where('reportable_type', $subject->getMorphClass())
            ->where('reportable_id', $subject->getKey());
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * The reported post or comment, even while it is hidden. null once it has
     * been deleted.
     */
    public function subject(): Post|Comment|null
    {
        $class = Relation::getMorphedModel($this->reportable_type) ?? $this->reportable_type;

        if (! in_array($class, [Post::class, Comment::class], true)) {
            return null;
        }

        return $class::withoutGlobalScopes()->find($this->reportable_id);
    }

    public function typeLabel(): string
    {
        $class = Relation::getMorphedModel($this->reportable_type) ?? $this->reportable_type;

        return class_basename($class);
    }

    /**
     * File a deletion request. The subject is hidden straight away. Filing a
     * second request for the same subject returns the one already pending.
     */
    public static function fileFor(Post|Comment $subject, User $reporter, string $reason): self
    {
        if (! $reporter->isModerator()) {
            throw new AuthorizationException('Only moderators can file a deletion request.');
        }

        return DB::transaction(function () use ($subject, $reporter, $reason): self {
            $existing = static::query()->pending()->forSubject($subject)->first();

            if ($existing) {
                return $existing;
            }

            $report = static::create([
                'reportable_type' => $subject->getMorphClass(),
                'reportable_id' => $subject->getKey(),
                'reporter_id' => $reporter->getKey(),
                'subject_label' => static::labelFor($subject),
                'reason' => $reason,
                'status' => self::STATUS_PENDING,
            ]);

            if ($subject instanceof Post) {
                Tag::recalculateAllPostCounts();
            }

            return $report;
        });
    }

    /**
     * Approve the request: the post or comment is deleted for good.
     */
    public function approve(User $reviewer, ?string $note = null): void
    {
        $this->assertCanReview($reviewer);

        DB::transaction(function () use ($reviewer, $note): void {
            if (! $this->fresh()?->isPending()) {
                return;
            }

            $subject = $this->subject();

            $this->markReviewed(self::STATUS_APPROVED, $reviewer, $note);

            $subject?->delete();
        });
    }

    /**
     * Reject the request: the post or comment is shown on the site again.
     */
    public function reject(User $reviewer, ?string $note = null): void
    {
        $this->assertCanReview($reviewer);

        DB::transaction(function () use ($reviewer, $note): void {
            if (! $this->fresh()?->isPending()) {
                return;
            }

            $this->markReviewed(self::STATUS_REJECTED, $reviewer, $note);

            if ($this->reportable_type === (new Post)->getMorphClass()) {
                Tag::recalculateAllPostCounts();
            }
        });
    }

    /**
     * Close the pending requests of a subject that was deleted directly
     * (for example by an admin), so they do not stay in the queue.
     */
    public static function closeForSubject(Model $subject, string $note): void
    {
        static::closePending($subject->getMorphClass(), [$subject->getKey()], $note);
    }

    /**
     * Same as closeForSubject() for comments that are removed together with
     * their post or parent comment (database cascade fires no model events).
     *
     * @param  iterable<int>  $commentIds
     */
    public static function closeForComments(iterable $commentIds, string $note): void
    {
        static::closePending((new Comment)->getMorphClass(), $commentIds, $note);
    }

    protected static function closePending(string $type, iterable $ids, string $note): void
    {
        $ids = collect($ids)->values()->all();

        if ($ids === []) {
            return;
        }

        static::query()
            ->pending()
            ->where('reportable_type', $type)
            ->whereIn('reportable_id', $ids)
            ->update([
                'status' => self::STATUS_APPROVED,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'review_note' => $note,
            ]);
    }

    protected function markReviewed(string $status, User $reviewer, ?string $note): void
    {
        $this->update([
            'status' => $status,
            'reviewed_by' => $reviewer->getKey(),
            'reviewed_at' => now(),
            'review_note' => filled($note) ? $note : null,
        ]);
    }

    protected function assertCanReview(User $reviewer): void
    {
        if (! $reviewer->isAdmin()) {
            throw new AuthorizationException('Only admins and the owner can review deletion requests.');
        }
    }

    protected static function labelFor(Post|Comment $subject): string
    {
        if ($subject instanceof Post) {
            $uploader = $subject->uploader?->name;

            return 'Post #'.$subject->getKey().($uploader ? ' by '.$uploader : '');
        }

        return 'Comment #'.$subject->getKey().' by '.$subject->author_name.': '
            .Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($subject->body))), 80);
    }
}
