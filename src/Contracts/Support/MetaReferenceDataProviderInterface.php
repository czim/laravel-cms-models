<?php
namespace Czim\CmsModels\Contracts\Support;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Data\Strategies\ModelMetaReferenceInterface;

interface MetaReferenceDataProviderInterface
{

    /**
     * Returns reference data for a model class, type and key.
     *
     * @param string      $modelClass
     * @param string      $type
     * @param string      $key
     * @param string|null $targetModel  for multiple/nested models, the target to use
     * @return ModelMetaReferenceInterface|false
     */
    public function getForModelClassByType($modelClass, $type, $key, $targetModel = null);

    /**
     * Returns reference data for model information, type and key.
     *
     * @param ModelInformationInterface $info
     * @param string                    $type
     * @param string                    $key
     * @param string|null               $targetModel    for multiple/nested models, the target to use
     * @return ModelMetaReferenceInterface|false
     */
    public function getForInformationByType(ModelInformationInterface $info, $type, $key, $targetModel = null);

    /**
     * Returns nested model classes for model information, type and key.
     *
     * This can be used to check whether a reference data is for multiple models, and if so, which.
     *
     * @param ModelInformationInterface $info
     * @param string                    $type
     * @param string                    $key
     * @return false|string[]   Returns false if the relation does not have nested model data.
     */
    public function getNestedModelClassesByType(ModelInformationInterface $info, $type, $key);

}
