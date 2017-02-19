<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Exceptions\ModelConfigurationDataException;

abstract class AbstractModelInformationDataObject extends AbstractDataObject
{

    /**
     * Whether to throw an exception if unknown properties are set.
     *
     * @var bool
     */
    protected $exceptionOnUnknown = true;

    /**
     * A list of key names that are known and accepted if exceptionOnUnknown is true.
     *
     * @var array
     */
    protected $known = [];


    /**
     * Overridden to use for known nested attribute checks
     *
     * {@inheritdoc}
     * @throws ModelConfigurationDataException
     */
    protected function checkAttributeAssignable($attribute)
    {
        if ( ! $this->exceptionOnUnknown || empty($this->known)) {
            return;
        }

        if (is_array($attribute)) {

            foreach ($attribute as $singleAttribute) {
                $this->checkAttributeAssignable($singleAttribute);
            }

            return;
        }

        if ( ! in_array($attribute, $this->known)) {
            throw (new ModelConfigurationDataException("Unknown model configuration data key: '{$attribute}'"))
                ->setDotKey($attribute);
        }
    }

}
