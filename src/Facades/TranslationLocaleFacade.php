<?php
namespace Czim\CmsModels\Facades;

use Illuminate\Support\Facades\Facade;

class TranslationLocaleFacade extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'cms-translation-locale-helper';
    }
}
