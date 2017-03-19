<?php
namespace Czim\CmsModels\Strategies\Form\Display;

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
