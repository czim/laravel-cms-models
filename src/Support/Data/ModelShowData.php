<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsModels\Contracts\Data\ModelShowDataInterface;

/**
 * Class ModelShowData
 *
 * Data container that represents show page for the model.
 *
 * @property array                        $layout
 * @property array|ModelShowFieldData[]   $fields
 * @property array|ModelViewReferenceData $before
 * @property array|ModelViewReferenceData $after
 */
class ModelShowData extends AbstractModelInformationDataObject implements ModelShowDataInterface
{

    protected $objects = [
        'before' => ModelViewReferenceData::class,
        'after'  => ModelViewReferenceData::class,
        'fields' => ModelShowFieldData::class . '[]',
    ];

    protected $attributes = [

        // Views to show before and/or after the shown fields. Instance of ModelViewReferenceData.
        'before'      => null,
        'after'       => null,

        // Arrays (instances of ModelFormFieldData or ModelFormFieldGroupData) that define the editable fields for
        // the model's form in the order in which they should appear by default.
        'fields' => [],
    ];

    protected $known = [
        'before',
        'after',
        'fields',
    ];


    /**
     * @param ModelShowDataInterface|ModelShowData $with
     */
    public function merge(ModelShowDataInterface $with)
    {
        // Overwrite fields intelligently: keep only the fields for keys that were set
        // and merge those for which data is set.
        if ($with->fields && count($with->fields)) {

            $mergedFields = [];

            foreach ($with->fields as $key => $data) {

                if (array_has($this->fields, $key)) {
                    $data = $this->fields[ $key ]->merge($data);
                }

                $mergedFields[ $key ] = $data;
            }

            $this->fields = $mergedFields;
        }

        $standardMergeKeys = [
            'before',
            'after',
        ];

        foreach ($standardMergeKeys as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }
    }

}
