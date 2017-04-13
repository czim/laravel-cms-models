<?php
namespace Czim\CmsModels\Test\Helpers\Strategies\Form\Store;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;

/**
 * Class TestSimpleStringFormatValidation
 *
 * Returns string validation rules for create/update in string format.
 */
class TestSimpleStringFormatValidation extends AbstractTestSimpleValidation
{

    /**
     * Returns validation rules to use for submitted form data for this strategy.
     *
     * If the return array is associative, rules are expected nested per key,
     * otherwise the rules will be added to the top level key.
     *
     * @param ModelInformationInterface|null $modelInformation
     * @param bool                           $create whether the rules are for creating a new record
     * @return string
     */
    public function validationRules(ModelInformationInterface $modelInformation = null, $create)
    {
        return 'string|size:' . ($create ? '10' : '20');
    }
}
