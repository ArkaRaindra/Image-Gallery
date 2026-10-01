<?php

namespace App\Models\Scopes;

use App\Models\Comment;
use App\Models\DeletionReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Hides posts and comments that have a pending deletion request from the
 * public site. Admin screens opt out with withoutGlobalScope().
 */
class HidePendingDeletionScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereNotExists(function ($query) use ($model): void {
            $query->selectRaw('1')
                ->from('deletion_reports')
                ->whereColumn('deletion_reports.reportable_id', $model->getQualifiedKeyName())
                ->where('deletion_reports.reportable_type', $model->getMorphClass())
                ->where('deletion_reports.status', DeletionReport::STATUS_PENDING);
        });

        if (! $model instanceof Comment) {
            return;
        }

        // Replies disappear together with the comment they answer.
        $builder->whereNotExists(function ($query) use ($model): void {
            $query->selectRaw('1')
                ->from('deletion_reports as parent_reports')
                ->whereColumn('parent_reports.reportable_id', $model->qualifyColumn('parent_id'))
                ->where('parent_reports.reportable_type', $model->getMorphClass())
                ->where('parent_reports.status', DeletionReport::STATUS_PENDING);
        });
    }
}
