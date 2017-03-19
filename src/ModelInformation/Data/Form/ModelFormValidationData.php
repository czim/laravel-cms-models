<?php
namespace Czim\CmsModels\ModelInformation\Data\Form;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormValidationDataInterface;
use Czim\CmsModels\ModelInformation\Data\AbstractModelInformationDataObject;

/**
 * Class ModelFormValidationData
 *
 * Information about validation rules for the model's forms.
 *
 * @property array $create
 * @property array $update
 * @property bool  $create_replace
 * @property bool  $update_replace
 */
class ModelFormValidationData extends AbstractModelInformationDataObject implements ModelFormValidationDataInterface
{

    protected $attributes = [

        // Validation rules, when creating a record.
        'create' => [],

        // Validation rules, when updating a record.
        // If null, will default to create validation rules.
        'update' => null,

        // If true, will replace default create rules set under 'create' entirely.
        'create_replace' => null,
        // If true, will replace default update rules set under 'update' entirely.
        'update_replace' => null,
    ];

    protected $known = [
        'create',
        'update',
        'create_replace',
        'update_replace',
    ];


    /**
     * Returns default or create specific rules.
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
        return $this->getAttribute('update') ?: $this->create();
    }

    /**
     * @param ModelFormValidationDataInterface|ModelFormValidationData $with
     */
    public function merge(ModelFormValidationDataInterface $with)
    {
        $standardMergeKeys = [
            'create_replace',
            'update_replace',
        ];

        foreach ($standardMergeKeys as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }

        // Merge validation rules
        //
        // Note that actual _replace logic and generation of validation rules based on
        // attributes/relations and model analysis is done during enrichment.

        $withCreate = $with->create;
        if (count($withCreate)) {
            $this->create = $withCreate;
        }

        $withUpdate = $with->update;
        if ( ! empty($withUpdate)) {
            $this->update = $withUpdate;
        }
    }

}
