<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Keeps comments, notes and note versions of a hidden post off the public
 * site. The post relation already excludes posts with a pending deletion
 * request, so requiring it to exist is enough.
 */
class RequiresVisiblePostScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('post');
    }
}
