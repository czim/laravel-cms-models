<?php
namespace Czim\CmsModels\Support\Strategies;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Data\Strategies\ModelMetaReferenceInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Support\MetaReferenceDataProviderInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Support\Data\Strategies\ModelMetaReference;
use Czim\CmsModels\Support\Strategies\Traits\GetsNestedRelations;
use Czim\CmsModels\Support\Strategies\Traits\HasMorphRelationStrategyOptions;
use Czim\CmsModels\Support\Strategies\Traits\ResolvesSourceStrategies;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use UnexpectedValueException;

class MetaReferenceDataProvider implements MetaReferenceDataProviderInterface
{
    use HasMorphRelationStrategyOptions,
        GetsNestedRelations,
        ResolvesSourceStrategies;


    /**
     * Returns reference data for a model class, type and key.
     *
     * @param string      $modelClass
     * @param string      $type
     * @param string      $key
     * @param string|null $targetModel  for multiple/nested models, the target to use
     * @return ModelMetaReferenceInterface|false
     */
    public function getForModelClassByType($modelClass, $type, $key, $targetModel = null)
    {
        $info = $this->getModelInformation($modelClass);

        if ( ! $info) return false;

        return $this->getForInformationByType($info, $type, $key, $targetModel);
    }

    /**
     * Returns reference data for model information, type and key.
     *
     * @param ModelInformationInterface $info
     * @param string                    $type
     * @param string                    $key
     * @param string|null               $targetModel    for multiple/nested models, the target to use
     * @return ModelMetaReferenceInterface|false
     */
    public function getForInformationByType(ModelInformationInterface $info, $type, $key, $targetModel = null)
    {
        // Find the reference information for type and key specified
        switch ($type) {

            case 'form.field':
                return $this->getInformationReferenceDataForFormField($info, $key, $targetModel);

            // Default omitted on purpose
        }

        return false;
    }

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
    public function getNestedModelClassesByType(ModelInformationInterface $info, $type, $key)
    {
        // Find the reference information for type and key specified
        switch ($type) {

            case 'form.field':
                return $this->getInformationReferenceModelClassesForFormField($info, $key);

            // Default omitted on purpose
        }

        return false;
    }


    /**
     * @param ModelInformationInterface|ModelInformation $info
     * @param string   $key
     * @param string   $targetModel     the nested model class, if multiple model definitions set
     * @return ModelMetaReferenceInterface|false
     */
    protected function getInformationReferenceDataForFormField(
        ModelInformationInterface $info,
        $key,
        $targetModel = null
    ) {
        if ( ! array_key_exists($key, $info->form->fields)) {
            return false;
        }

        $formFieldData = $info->form->fields[ $key ];

        // Determine the target class, if not set in options

        $dataIsNested = null !== $targetModel;

        if ($dataIsNested) {
            // Find the nested data for this specific model

            $dataParent = $this->getNestedReferenceFieldOptionData($formFieldData->options(),$targetModel);

        } else {
            // Find the data in the top level form field data, and determine the model

            if ( ! ($targetModel = array_get($formFieldData->options(), 'model'))) {
                $targetModel = get_class(
                    $this->determineTargetModelFromSource($info->modelClass(), $formFieldData->source())
                );
            }

            $dataParent = $formFieldData->options();
        }

        $referenceData = new ModelMetaReference([
            'model'            => $targetModel,
            'strategy'         => array_get($dataParent, 'strategy'),
            'source'           => array_get($dataParent, 'source'),
            'target'           => array_get($dataParent, 'target'),
            'context_strategy' => array_get($dataParent, 'context_strategy'),
            'parameters'       => array_get($dataParent, 'parameters', []),
            'sort_direction'   => array_get($dataParent, 'sort_direction'),
        ]);

        return $this->enrichReferenceData($referenceData);
    }

    /**
     * @param ModelInformationInterface|ModelInformation $info
     * @param string                                     $key
     * @return false|string[]
     */
    protected function getInformationReferenceModelClassesForFormField(ModelInformationInterface $info, $key)
    {
        if ( ! array_key_exists($key, $info->form->fields)) {
            return false;
        }

        $formFieldData = $info->form->fields[ $key ];

        $nestedModels = $this->getMorphableModelsForFieldData($formFieldData);

        if ( ! $nestedModels || ! is_array($nestedModels) || ! count($nestedModels)) {
            return false;
        }

        return $nestedModels;
    }

    /**
     * Returns nested reference data for a given model class, if possible.
     *
     * @param array  $options
     * @param string $modelClass
     * @return array
     */
    protected function getNestedReferenceFieldOptionData(array $options, $modelClass)
    {
        $data = array_get($options, 'models.' . $modelClass, false);

        if (false === $data || ! is_array($data)) {
            // If we could not retrieve the model data by key,
            // it was either omitted are set as a string value (non-assiative).
            // In either case, there is no specified reference data.

            return [];
        }

        return $data;
    }

    /**
     * Resolves and returns model instance for a given source on a (CMS) model.
     *
     * @param string $modelClass
     * @param string $source
     * @return Model
     */
    protected function determineTargetModelFromSource($modelClass, $source)
    {
        if ( ! is_a($modelClass, Model::class, true)) {
            throw new UnexpectedValueException("{$modelClass} is not an Eloquent model");
        }

        $model = new $modelClass;

        if ( ! ($relation = $this->getNestedRelation($model, $source))) {
            $relation = $this->resolveModelSource($model, $source);
        }

        if ( ! ($relation instanceof Relation)) {
            throw new UnexpectedValueException(
                "Source {$source} on {$modelClass} does not resolve to an Eloquent relation instance"
            );
        }

        /** @var Relation $relation */
        return $relation->getRelated();
    }

    /**
     * Enriches refernce data object as required.
     *
     * @param ModelMetaReferenceInterface|ModelMetaReference $data
     * @return ModelMetaReferenceInterface
     */
    protected function enrichReferenceData(ModelMetaReferenceInterface $data)
    {
        // If the source/strategy are not set, check if we can use the model's reference data
        if (null === $data->source() || null === $data->strategy()) {

            // todo: take into account that this might be a morph relation...

            if ($info = $this->getModelInformation($data->model())) {

                if (null === $data->source()) {
                    $data->source = $info->reference->source;
                }
                if (null === $data->strategy()) {
                    $data->strategy = $info->reference->strategy;
                }
            }
        }

        return $data;
    }

    /**
     * @param string|Model $model
     * @return ModelInformationInterface|ModelInformation|false
     */
    protected function getModelInformation($model)
    {
        if ($model instanceof Model) {
            return $this->getModelInformationRepository()->getByModel($model);
        }

        return $this->getModelInformationRepository()->getByModelClass($model);
    }

    /**
     * @return ModelInformationRepositoryInterface
     */
    protected function getModelInformationRepository()
    {
        return app(ModelInformationRepositoryInterface::class);
    }
}
