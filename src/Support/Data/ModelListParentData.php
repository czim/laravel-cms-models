<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelListParentDataInterface;

/**
 * Class ModelListParentData
 *
 * Data container that represents a parent data presence in an index/listing for a model.
 *
 * @property string $relation
 */
class ModelListParentData extends AbstractDataObject
{

    protected $attributes = [

        // The relation name for this model's relation to the parent
        'relation' => null,
    ];


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
