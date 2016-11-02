<?php
namespace Czim\CmsModels\Support\Strategies;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Data\Strategies\ModelMetaReferenceInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Support\MetaReferenceDataProviderInterface;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\Strategies\ModelMetaReference;
use Czim\CmsModels\View\Traits\GetsNestedRelations;
use Czim\CmsModels\View\Traits\ResolvesSourceStrategies;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use UnexpectedValueException;

class MetaReferenceDataProvider implements MetaReferenceDataProviderInterface
{
    use ResolvesSourceStrategies,
        GetsNestedRelations;


    /**
     * Returns reference data for a model class, type and key.
     *
     * @param string $modelClass
     * @param string $type
     * @param string $key
     * @return ModelMetaReferenceInterface|false
     */
    public function getForModelClassByType($modelClass, $type, $key)
    {
        $info = $this->getModelInformation($modelClass);

        if ( ! $info) return false;

        return $this->getForInformationByType($info, $type, $key);
    }

    /**
     * Returns reference data for model information, type and key.
     *
     * @param ModelInformationInterface $info
     * @param string                    $type
     * @param string                    $key
     * @return ModelMetaReferenceInterface|false
     */
    public function getForInformationByType(ModelInformationInterface $info, $type, $key)
    {
        // Find the reference information for type and key specified
        switch ($type) {

            case 'form.field':
                return $this->getInformationReferenceDataForFormField($info, $key);

            // Default omitted on purpose
        }

        return false;
    }


    /**
     * @param ModelInformationInterface|ModelInformation $info
     * @param string                                     $key
     * @return ModelMetaReference|false
     */
    protected function getInformationReferenceDataForFormField(ModelInformationInterface $info, $key)
    {
        if ( ! array_key_exists($key, $info->form->fields)) {
            return false;
        }

        $data = $info->form->fields[ $key ];

        // Determine the target class, if not set in options
        if ( ! ($targetModelClass = array_get($data->options(), 'model'))) {
            $targetModelClass = get_class(
                $this->determineTargetModelFromSource($info->modelClass(), $data->source())
            );
        }

        return new ModelMetaReference([
            'model'            => $targetModelClass,
            'strategy'         => array_get($data->options(), 'strategy'),
            'source'           => array_get($data->options(), 'source'),
            'target'           => array_get($data->options(), 'target'),
            'context_strategy' => array_get($data->options(), 'context_strategy'),
            'parameters'       => array_get($data->options(), 'parameters', []),
            'sort_direction'   => array_get($data->options(), 'sort_direction'),
        ]);
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
