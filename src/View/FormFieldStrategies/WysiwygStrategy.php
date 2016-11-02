<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

class WysiwygStrategy extends AbstractDefaultStrategy
{

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.wysiwyg';
    }

}
