<?php
namespace Czim\CmsModels\Contracts\Repositories;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;

interface ContextStrategyInterface
{

    /**
     * Applies contextual setup to a query/model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param ModelInformationInterface             $information
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($query, ModelInformationInterface $information);

}
