<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Repositories\ModelReferenceRepositoryInterface;
use Czim\CmsModels\Contracts\Support\MetaReferenceDataProviderInterface;

abstract class AbstractRelationStrategy extends AbstractDefaultStrategy
{

    /**
     * Returns references for model keys as an array keyed per model key.
     *
     * @param mixed[] $keys
     * @return string[]     associative
     */
    protected function getReferencesForModelKeys(array $keys)
    {
        $keys = array_filter($keys);

        if ( ! count($keys)) {
            return [];
        }

        $referenceData = $this->getReferenceDataProvider()->getForModelClassByType(
            $this->model,
            'form.field',
            $this->field->key()
        );

        if ( ! $referenceData) {
            return [];
        }

        $references = [];

        foreach ($keys as $key) {
            $references[ $key ] = $this->getReferenceRepository()
                ->getReferenceForModelMetaReferenceByKey($referenceData, $key);
        }

        return $references;
    }


    /**
     * Get displayable text for a given model class.
     *
     * @param string $modelClass
     * @return string
     */
    protected function getModelDisplayLabel($modelClass)
    {
        $info = $this->getModelInformation($modelClass);

        if ($info) {
            return ucfirst($info->labelPlural());
        }

        return $this->makeModelDisplayValueFromModelClass($modelClass);
    }

    /**
     * Returns displayable text for a given model class, based only on the class name.
     *
     * @param string $modelClass
     * @return string
     */
    protected function makeModelDisplayValueFromModelClass($modelClass)
    {
        $stripPrefix = config('cms-models.collector.models-namespace');

        if ($stripPrefix && starts_with($modelClass, $stripPrefix)) {
            $modelClass = trim(substr($modelClass, 0, strlen($stripPrefix)), '\\');
        }

        return ucfirst(str_replace('\\', ' ', $modelClass));
    }


    /**
     * @return MetaReferenceDataProviderInterface
     */
    protected function getReferenceDataProvider()
    {
        return app(MetaReferenceDataProviderInterface::class);
    }

    /**
     * @return ModelReferenceRepositoryInterface
     */
    protected function getReferenceRepository()
    {
        return app(ModelReferenceRepositoryInterface::class);
    }

    /**
     * @param $modelClass
     * @return \Czim\CmsModels\Support\Data\ModelInformation|false
     */
    protected function getModelInformation($modelClass)
    {
        return $this->getModelInformationRepository()->getByModelClass($modelClass);
    }

    /**
     * @return ModelInformationRepositoryInterface
     */
    protected function getModelInformationRepository()
    {
        return app(ModelInformationRepositoryInterface::class);
    }

}
