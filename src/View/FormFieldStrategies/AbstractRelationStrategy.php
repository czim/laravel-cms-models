<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

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

}
