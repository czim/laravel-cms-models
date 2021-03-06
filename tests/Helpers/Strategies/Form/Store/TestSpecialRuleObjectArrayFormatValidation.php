<?php
namespace Czim\CmsModels\Test\Helpers\Strategies\Form\Store;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;

/**
 * Class TestSpecialRuleObjectArrayFormatValidation
 *
 * Offers validation rule in the form of a validation rule data object.
 */
class TestSpecialRuleObjectArrayFormatValidation extends AbstractTestSimpleValidation
{

    /**
     * Returns validation rules to use for submitted form data for this strategy.
     *
     * If the return array is associative, rules are expected nested per key,
     * otherwise the rules will be added to the top level key.
     *
     * @param ModelInformationInterface|null $modelInformation
     * @param bool                           $create whether the rules are for creating a new record
     * @return array
     */
    public function validationRules(ModelInformationInterface $modelInformation = null, $create)
    {
        return [
            '**' => [
                'key'   => 'test_a',
                'rules' => ['required'],
            ],
            'test_b' => [
                '**' => [
                    'rules'        => ['max:2'],
                    'translated'   => true,
                    'locale_index' => 2,
                ]
            ]
        ];
    }
}
