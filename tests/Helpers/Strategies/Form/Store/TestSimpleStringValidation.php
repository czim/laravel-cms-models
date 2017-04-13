<?php
namespace Czim\CmsModels\Test\Helpers\Strategies\Form\Store;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;

/**
 * Class TestSimpleStringValidation
 *
 * Returns string validation rules for create/update.
 */
class TestSimpleStringValidation extends AbstractTestSimpleValidation
{

    /**
     * Returns validation rules to use for submitted form data for this strategy.
     *
     * If the return array is associative, rules are expected nested per key,
     * otherwise the rules will be added to the top level key.
     *
     * @param ModelInformationInterface|null $modelInformation
     * @param bool                           $create whether the rules are for creating a new record
     * @return array|false false if no validation should be performed.
     */
    public function validationRules(ModelInformationInterface $modelInformation = null, $create)
    {
        return [
            'string',
            'size:' . ($create ? '10' : '20')
        ];
    }
}
