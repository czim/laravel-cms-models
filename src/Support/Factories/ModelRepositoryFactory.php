<?php
namespace Czim\CmsModels\Support\Factories;

use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;
use Czim\CmsModels\Contracts\Support\Factories\ModelRepositoryFactoryInterface;
use Czim\CmsModels\Repositories\ModelRepository;

class ModelRepositoryFactory implements ModelRepositoryFactoryInterface
{

    /**
     * Makes a model repository instance.
     *
     * @param string $modelClass
     * @return ModelRepositoryInterface
     */
    public function make($modelClass)
    {
        return new ModelRepository($modelClass);
    }

}
