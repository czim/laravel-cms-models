<?php
namespace Czim\CmsModels\Test\Helpers\Models\Analyzer\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TestActiveScope implements Scope
{

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('active', true);
    }

}
