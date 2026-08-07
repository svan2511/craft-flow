<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class WorkshopScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if ($user !== null && $user->workshop_id !== null) {
            $builder->where($model->qualifyColumn('workshop_id'), $user->workshop_id);
        }
    }
}
