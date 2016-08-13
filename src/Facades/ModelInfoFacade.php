<?php
namespace Czim\CmsModels\Facades;

use Illuminate\Support\Facades\Facade;

class ModelInfoFacade extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'cms-models-modelinfo';
    }
}
