<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BusinessScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->bound('business')) {
            $builder->where(
                $model->getTable() . '.business_id',
                business()->id
            );
        }
    }
}
