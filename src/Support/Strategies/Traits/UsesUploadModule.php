<?php
namespace Czim\CmsModels\Support\Strategies\Traits;

use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsUploadModule\Contracts\Repositories\FileRepositoryInterface;

trait UsesUploadModule
{
    /**
     * Returns whether the upload module is loaded.
     *
     * @return bool
     */
    protected function isUploadModuleAvailable()
    {
        return  false !== $this->getUploadModule()
            &&  app()->bound(FileRepositoryInterface::class);
    }

    /**
     * Returns an instance of the upload module.
     *
     * @return ModuleInterface|false
     */
    protected function getUploadModule()
    {
        /** @var ModuleManagerInterface $modules */
        $modules = app(Component::MODULES);

        return $modules->get($this->getUploadModuleKey());
    }

    /**
     * Returns the module key for the upload module.
     *
     * @return string
     */
    protected function getUploadModuleKey()
    {
        return 'file-uploader';
    }

}
