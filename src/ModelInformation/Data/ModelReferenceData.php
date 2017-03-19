<?php
namespace Czim\CmsModels\ModelInformation\Data;

use Czim\CmsModels\Contracts\Data\ModelReferenceDataInterface;

/**
 * Class ModelReferenceData
 *
 * Container for information about model references to use externally.
 *
 * @property string $strategy
 * @property string $source
 * @property string $search
 */
class ModelReferenceData extends AbstractModelInformationDataObject implements ModelReferenceDataInterface
{

    protected $attributes = [

        // The strategy for displaying the reference
        'strategy' => null,

        // The source attribute or attribute list to use in the strategy
        'source'   => null,

        // The attributes to search for matches on (f.i. selecting records to link to)
        'search'   => null,
    ];

    protected $known = [
        'strategy',
        'source',
        'search',
    ];


    /**
     * @param ModelReferenceDataInterface|ModelReferenceData $with
     */
    public function merge(ModelReferenceDataInterface $with)
    {
        foreach ($this->getKeys() as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }
    }

}
