<?php
namespace Czim\CmsModels\Strategies\Form\Display;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Repositories\ModelReferenceRepositoryInterface;
use Czim\CmsModels\Contracts\Support\MetaReferenceDataProviderInterface;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

abstract class AbstractRelationStrategy extends AbstractDefaultStrategy
{

    /**
     * Thesholds for increasing minimum input length for autocomplete fields
     */
    const AUTOCOMPLETE_INPUT_THRESHOLD_ONE   = 50;
    const AUTOCOMPLETE_INPUT_THRESHOLD_TWO   = 250;
    const AUTOCOMPLETE_INPUT_THRESHOLD_THREE = 1000;

    /**
     * Returns references for model keys as an array keyed per model key.
     *
     * @param mixed[]     $keys
     * @param string|null $targetModel  the nested model class, if multiple model definitions set
     * @return string[] associative
     */
    protected function getReferencesForModelKeys(array $keys, $targetModel = null)
    {
        $keys = array_filter($keys);

        if ( ! count($keys)) {
            return [];
        }

        $referenceData = $this->getReferenceDataProvider()->getForModelClassByType(
            $this->model,
            'form.field',
            $this->field->key(),
            $targetModel
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
     * Returns the best minimum input length for autocomplete input ajax triggers.
     *
     * @return int
     */
    protected function determineBestMinimumInputLength()
    {
        $info = $this->getModelInformation(get_class($this->model));

        // Check if this is a multiple-model (morphTo), get the target models.
        $models = $this->getReferenceDataProvider()->getNestedModelClassesByType(
            $info,
            'form.field',
            $this->field->key()
        );

        if ($models && count($models)) {

            $total = array_reduce($models, function ($carry, $modelClass) {
                return $carry + $this->getCountForModel($modelClass);
            });

        } else {
            // Otherwise, rely on the reference data to provide
            $referenceData = $this->getReferenceDataProvider()
                ->getForInformationByType($info, 'form.field', $this->field->key());

            if (false === $referenceData) {
                return 1;
            }

            $total = $this->getCountForModel($referenceData->model());
        }

        if (null === $total) {
            return 1;
        }

        if ($total > static::AUTOCOMPLETE_INPUT_THRESHOLD_THREE) {
            return 3;
        }

        if ($total > static::AUTOCOMPLETE_INPUT_THRESHOLD_TWO) {
            return 2;
        }

        if ($total > static::AUTOCOMPLETE_INPUT_THRESHOLD_ONE) {
            return 1;
        }

        return 0;
    }

    /**
     * Returns total count for a model classname.
     *
     * @param string $modelClass
     * @return int
     */
    protected function getCountForModel($modelClass)
    {
        if ( ! is_a($modelClass, Model::class, true)) {
            throw new UnexpectedValueException("'{$modelClass}' is not an Eloquent model class.");
        }

        return $modelClass::withoutGlobalScopes()->count() ?: 0;
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
     * @param string $modelClass
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
