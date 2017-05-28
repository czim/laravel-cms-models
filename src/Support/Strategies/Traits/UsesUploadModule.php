<?php
namespace Czim\CmsModels\Support\Strategies\Traits;

use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsUploadModule\Contracts\Repositories\FileRepositoryInterface;
use Czim\CmsUploadModule\Contracts\Support\Security\SessionGuardInterface;

trait UsesUploadModule
{

    /**
     * Remember result to only perform the check once.
     *
     * @var bool
     */
    protected $isUploadModuleAvailable;

    /**
     * Returns whether the upload module is loaded.
     *
     * @return bool
     */
    protected function isUploadModuleAvailable()
    {
        if ($this->isUploadModuleAvailable === null) {
            $this->isUploadModuleAvailable =    false !== $this->getUploadModule()
                                            &&  app()->bound(FileRepositoryInterface::class);
        }

        return $this->isUploadModuleAvailable;
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
     * Returns the upload modules file record by ID.
     *
     * @param int $id
     * @return \Czim\CmsUploadModule\Models\File|null
     */
    protected function getUploadedFileRecordById($id)
    {
        return $this->getFileUploadFileRepository()->findById($id);
    }

    /**
     * Deletes an upload modules file record (and its file) by ID.
     *
     * @param int $id
     * @return bool
     */
    protected function deleteUploadedFileRecordById($id)
    {
        return $this->getFileUploadFileRepository()->delete($id);
    }

    /**
     * Returns whether file upload is allowed to be used within this session.
     *
     * @param int $id
     * @return bool
     */
    protected function checkFileUploadWithSessionGuard($id)
    {
        $guard = $this->getFileUploadSesssionGuard();

        return ! $guard->enabled() || $guard->check($id);
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

    /**
     * @return FileRepositoryInterface
     */
    protected function getFileUploadFileRepository()
    {
        return app(FileRepositoryInterface::class);
    }

    /**
     * @return SessionGuardInterface
     */
    protected function getFileUploadSesssionGuard()
    {
        return app(SessionGuardInterface::class);
    }

}
