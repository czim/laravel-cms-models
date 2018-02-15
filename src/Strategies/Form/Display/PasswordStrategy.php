<?php
namespace Czim\CmsModels\Strategies\Form\Display;

class PasswordStrategy extends DefaultStrategy
{

    /**
     * {@inheritdoc}
     */
    protected function getDefaultFieldType()
    {
        return 'password';
    }

}
