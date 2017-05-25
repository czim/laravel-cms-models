<?php
namespace Czim\CmsModels\Strategies\Form\Display;

class AttachmentStaplerFileStrategy extends AbstractStaplerStrategy
{

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        if ($this->useFileUploader()) {
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

        if ($this->useFileUploader()) {
            $data['uploadUrl']        = cms_route('fileupload.file.upload');
            $data['uploadDeleteUrl']  = cms_route('fileupload.file.delete', ['ID_PLACEHOLDER']);
            $data['uploadValidation'] = $this->getFileValidationRules();
        }

        return $data;
    }

}
