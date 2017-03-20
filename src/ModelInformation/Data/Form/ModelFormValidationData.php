<?php
namespace Czim\CmsModels\ModelInformation\Data\Form;

use Czim\CmsModels\Contracts\Http\Requests\ValidationRulesInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormValidationDataInterface;
use Czim\CmsModels\ModelInformation\Data\AbstractModelInformationDataObject;

/**
 * Class ModelFormValidationData
 *
 * Information about validation rules for the model's forms.
 *
 * @property array  $shared
 * @property array  $create
 * @property array  $update
 * @property bool   $create_replace
 * @property bool   $update_replace
 * @property string $rules_class
 */
class ModelFormValidationData extends AbstractModelInformationDataObject implements ModelFormValidationDataInterface
{

    protected $attributes = [

        // Validation rules, base rules shared for create/update.
        'shared' => [],

        // Validation rules, when creating a record.
        // This decorates the default rules, unless create_replace is true.
        'create' => [],

        // Validation rules, when updating a record.
        // This decorates the default rules, unless update_replace is true,
        'update' => [],

        // If true, will replace default create rules set under 'create' and 'update' entirely, respectively.
        'create_replace' => null,
        'update_replace' => null,

        // Class to use for decorating (or providing) validation rules. Instance of ValidationRulesInterface.
        /** @see ValidationRulesInterface */
        'rules_class' => null,
    ];

    protected $known = [
        'shared',
        'create',
        'update',
        'create_replace',
        'update_replace',
        'rules_class',
    ];


    /**
     * Returns default/base rules shared by create and update.
     *
     * @return array
     */
    public function sharedRules()
    {
        return $this->getAttribute('shared') ?: [];
    }

    /**
     * Returns create specific rules.
     *
     * @return array
     */
    public function create()
    {
        return $this->getAttribute('create') ?: [];
    }

    /**
     * Returns update specific rules.
     *
     * @return array
     */
    public function update()
    {
        return $this->getAttribute('update') ?: [];
    }

    /**
     * Returns optional FQN of rules decorator/generator class.
     *
     * @return string
     */
    public function rulesClass()
    {
        return $this->getAttribute('rules_class');
    }

    /**
     * @param ModelFormValidationDataInterface|ModelFormValidationData $with
     */
    public function merge(ModelFormValidationDataInterface $with)
    {
        $standardMergeKeys = [
            'create_replace',
            'update_replace',
            'rules_class',
        ];

        foreach ($standardMergeKeys as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }

        // Merge validation rules
        //
        // Note that actual _replace logic and generation of validation rules based on
        // attributes/relations and model analysis is done during enrichment.

        $withRules = $with->shared;
        if (count($withRules)) {
            $this->shared = $withRules;
        }

        $withCreate = $with->create;
        if ( ! empty($withCreate)) {
            $this->create = $withCreate;
        }

        $withUpdate = $with->update;
        if ( ! empty($withUpdate)) {
            $this->update = $withUpdate;
        }

    }

}
