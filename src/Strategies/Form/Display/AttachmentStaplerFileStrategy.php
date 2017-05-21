<?php
namespace Czim\CmsModels\Strategies\Form\Display;

use Czim\CmsModels\Support\Strategies\Traits\UsesUploadModule;

class AttachmentStaplerFileStrategy extends AbstractDefaultStrategy
{
    use UsesUploadModule;

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        if ($this->isUploadModuleAvailable()) {
            return 'cms-models::model.partials.form.strategies.attachment_stapler_file_uploader';
        }

        return 'cms-models::model.partials.form.strategies.attachment_stapler_file';
    }

    /**
     * Enriches field data before passing it on to the view.
     *
     * @param array $data
     * @return array
     */
    protected function decorateFieldData(array $data)
    {
        $data['accept'] = array_get($data['options'], 'accept');

        $data['uploadUrl']       = cms_route('fileupload.file.upload');
        $data['uploadDeleteUrl'] = cms_route('fileupload.file.delete', ['ID_PLACEHOLDER']);

        return $data;
    }

}
