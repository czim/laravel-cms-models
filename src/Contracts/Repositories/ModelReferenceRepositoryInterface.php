<?php
namespace Czim\CmsModels\Contracts\Repositories;

use Czim\CmsModels\Contracts\Data\Strategies\ModelMetaReferenceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface ModelReferenceRepositoryInterface
{

    /**
     * Returns reference for a given model instance.
     *
     * @param Model       $model
     * @param string|null $strategy
     * @param string|null $source
     * @return string
     */
    public function getReferenceForModel(Model $model, $strategy = null, $source = null);

    /**
     * Returns list of references for a collection of model instances.
     *
     * Note: all models must be the same class!
     *
     * @param \ArrayAccess|Model[] $models
     * @param string|null          $strategy
     * @param string|null          $source
     * @return Collection|string[]  reference values, keyed by model key
     */
    public function getReferencesForModels($models, $strategy = null, $source = null);

    /**
     * Returns a reference for a model class, by meta reference data.
     *
     * @param ModelMetaReferenceInterface $referenceData
     * @param                             $key
     * @return string|false     false if the model could not be found
     */
    public function getReferenceForModelMetaReferenceByKey(ModelMetaReferenceInterface $referenceData, $key);

    /**
     * Returns a reference for a model instance, by meta reference data.
     *
     * @param ModelMetaReferenceInterface $referenceData
     * @param Model                       $model
     * @return string|false     false if the model could not be found
     */
    public function getReferenceForModelMetaReferenceByModel(ModelMetaReferenceInterface $referenceData, Model $model);

    /**
     * Returns references for models by meta reference data, keyed by the model keys.
     *
     * @param ModelMetaReferenceInterface $referenceData
     * @param string|null                 $search           optional search string to limit results
     * @return Collection   keyed by model key (or class:key)
     */
    public function getReferencesForModelMetaReference(ModelMetaReferenceInterface $referenceData, $search = null);

}
