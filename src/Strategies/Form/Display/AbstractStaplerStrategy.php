<?php
namespace Czim\CmsModels\Strategies\Form\Display;

use Czim\CmsModels\Support\Strategies\Traits\UsesUploadModule;

abstract class AbstractStaplerStrategy extends AbstractDefaultStrategy
{
    use UsesUploadModule;

    /**
     * Returns whether the file uploader model can and should be used.
     *
     * @return bool
     */
    protected function useFileUploader()
    {
        return ! array_get($this->field->options, 'no_ajax') && $this->isUploadModuleAvailable();
    }

    /**
     * Returns validation rules that should be applied to the uploaded file.
     *
     * @return string|array
     */
    protected function getFileValidationRules()
    {
        $rules = array_get($this->field->options, 'validation', []);

        // If the 'image' rule is not set, make sure it is added.
        if (is_string($rules)) {
            if (empty($rules)) {
                $rules = 'image';
            } elseif ( ! preg_match('#(^|\|)image($|\|)#', $rules)) {
                $rules .= '|image';
            }
        } elseif ( ! in_array('image', $rules)) {
            $rules[] = 'image';
        }

        return $rules;
    }

}
