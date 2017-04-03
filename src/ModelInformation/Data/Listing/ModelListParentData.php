<?php
namespace Czim\CmsModels\ModelInformation\Data\Listing;

use Czim\CmsModels\Contracts\ModelInformation\Data\Listing\ModelListParentDataInterface;
use Czim\CmsModels\ModelInformation\Data\AbstractModelInformationDataObject;

/**
 * Class ModelListParentData
 *
 * Data container that represents a parent data presence in an index/listing for a model.
 *
 * @property string $relation
 * @property string $field
 */
class ModelListParentData extends AbstractModelInformationDataObject implements ModelListParentDataInterface
{

    protected $attributes = [

        // The relation name for this model's relation to the parent.
        'relation' => null,

        // The key of the form field that allows selection of the parent model.
        // If not set, defaults to the relation name.
        'field' => null,
    ];

    protected $known = [
        'relation',
        'field',
    ];


    /**
     * Returns the relation method name.
     *
     * @return mixed
     */
    public function relation()
    {
        return $this->getAttribute('relation');
    }

    /**
     * Returns the field key.
     *
     * @return mixed
     */
    public function field()
    {
        $field = $this->getAttribute('field');

        if ($field || false === $field) {
            return $field;
        }

        return $this->getAttribute('relation');
    }

    /**
     * @param ModelListParentDataInterface|ModelListParentData $with
     */
    public function merge(ModelListParentDataInterface $with)
    {
        foreach ($this->getKeys() as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }
    }

}
