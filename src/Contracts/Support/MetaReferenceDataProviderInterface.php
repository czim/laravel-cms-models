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

}
