<?php
namespace Czim\CmsModels\Contracts\Support\Factories;

use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;

interface ModelRepositoryFactoryInterface
{

    /**
     * Makes a model repository instance.
     *
     * @param string $modelClass
     * @return ModelRepositoryInterface
     */
    public function make($modelClass);

}
