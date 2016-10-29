<?php
namespace Czim\CmsModels\Http\Controllers;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;
use Czim\CmsModels\Http\Controllers\Traits\AppliesRepositoryContext;
use Czim\CmsModels\Http\Requests\ModelMetaReferenceRequest;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\Strategies\ModelMetaReference;
use Czim\CmsModels\View\Traits\GetsNestedRelations;
use Czim\CmsModels\View\Traits\ModifiesQueryForContext;
use Czim\CmsModels\View\Traits\ResolvesModelReference;
use Czim\CmsModels\View\Traits\ResolvesSourceStrategies;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Class ModelMetaController
 *
 * Controller for meta functionality for CMS models pacakage.
 */
class ModelMetaController extends Controller
{
    use AppliesRepositoryContext,
        GetsNestedRelations,
        ModifiesQueryForContext,
        ResolvesModelReference,
        ResolvesSourceStrategies;


    /**
     * Looks up model references by an optional search term.
     *
     * Targets model information (such as a form field strategy definition) to
     * get the details for looking up references.
     *
     * Useful for relation (autocomplete) select strategies.
     *
     * @param ModelMetaReferenceRequest $request
     * @return mixed
     */
    public function references(ModelMetaReferenceRequest $request)
    {
        $modelClass = $request->input('model');
        $type       = $request->input('type');
        $key        = $request->input('key');

        $referenceData = $this->getModelMetaReferenceData($modelClass, $type, $key);

        if ( ! $referenceData) {
            abort(404, "Could not determine reference for {$modelClass} (type: {$type}, key: {$key})");
        }

        // If the targeted model is a CMS model, we can default back to its reference data
        // if not specifics are given, otherwise, a custom fallback reference must be used.
        if ($info = $this->getCmsModelInformation($referenceData->model)) {

            // Get model records, filtered as necessary
            $repository = $this->getModelRepositoryForInformation($info);

            /** @var Model[] $models */
            $query = $repository->query();

            // todo: apply search term

        } else {
            // The model is not part of the CMS

            $query = $this->getQueryBuilderForModelClass($referenceData->model);

            // todo: apply search term
        }

        // If set, apply an (extra) contextual modification to the query builder
        if ($referenceData->context_strategy) {
            $this->applyContextStrategyToQueryBuilder(
                $query,
                $referenceData->context_strategy,
                $referenceData->parameters
            );
        }


        $models = $query->get();

        // Get references for query results, keyed by primary key
        $references = [];

        foreach ($models as $model) {

            $references[ $model->getKey() ] = $this->getReferenceValue(
                $model,
                $referenceData->strategy,
                $referenceData->source
            );
        }

        if ($request->ajax()) {
            return response()->json($references);
        }

        // todo figure out what to return for non-ajax requests
        // consider using optional response strategy (for both ajax and non-ajax..)
    }

    /**
     * Returns a query builder instance for a given model class.
     *
     * @param string $modelClass
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getQueryBuilderForModelClass($modelClass)
    {
        if ( ! is_a($modelClass, Model::class, true)) {
            throw new UnexpectedValueException("{$modelClass} is not an Eloquent model");
        }

        /** @var Model $model */
        $model = new $modelClass;

        return $model->query();
    }

    /**
     * Applies a context strategy to a model query builder.
     *
     * @param Builder     $query
     * @param string|null $strategy
     * @param array       $parameters
     */
    protected function applyContextStrategyToQueryBuilder($query, $strategy, array $parameters = [])
    {
        $strategy = $this->resolveContextStrategy($strategy);

        if ( ! $strategy) return;

        $strategy->apply($query, $parameters);
    }

    /**
     * @param $modelClass
     * @param $type
     * @param $key
     * @return ModelMetaReference|false
     */
    protected function getModelMetaReferenceData($modelClass, $type, $key)
    {
        // Find out of the model is part of the CMS
        $info = $this->getCmsModelInformation($modelClass);

        // Model information is required, since it is used to determine the returned
        // reference strategy and source to use.
        if ( ! $info) {
            abort(404, "{$modelClass} is not a CMS model");
        }

        // Find the reference information for type and key specified
        switch ($type) {

            case 'form.field':
                return $this->getInformationReferenceDataForFormField($info, $key);

            // Default omitted on purpose
        }

        return abort(404, "Unknown reference type {$type}");
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
     * Returns model information for model class, if available.
     *
     * @param string $class
     * @return ModelInformation|false
     */
    protected function getCmsModelInformation($class)
    {
        return $this->infoRepository->getByModelClass($class);
    }

    /**
     * Returns an Eloquent model instance for a model class.
     *
     * @param string $class
     * @return Model
     */
    protected function getModelInstance($class)
    {
        if ( ! class_exists($class) || ! is_a($class, Model::class, true)) {
            throw new InvalidArgumentException("{$class} is not an Eloquent model");
        }

        return new $class;
    }

    /**
     * Returns instance of a model repository for given model information.
     *
     * @param ModelInformationInterface $information
     * @return ModelRepositoryInterface
     */
    protected function getModelRepositoryForInformation(ModelInformationInterface $information)
    {
        $modelRepository = app(ModelRepositoryInterface::class, [ $information->modelClass() ]);

        $this->applyRepositoryContext($modelRepository, $information);

        return $modelRepository;
    }

}
