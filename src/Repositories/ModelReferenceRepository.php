<?php
namespace Czim\CmsModels\Repositories;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Repositories\ModelReferenceRepositoryInterface;
use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;
use Czim\CmsModels\Http\Controllers\Traits\AppliesRepositoryContext;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Contracts\Data\Strategies\ModelMetaReferenceInterface;
use Czim\CmsModels\View\Traits\ModifiesQueryForContext;
use Czim\CmsModels\View\Traits\ResolvesModelReference;
use Czim\CmsModels\View\Traits\ResolvesSourceStrategies;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use UnexpectedValueException;

class ModelReferenceRepository implements ModelReferenceRepositoryInterface
{
    use AppliesRepositoryContext,
        ModifiesQueryForContext,
        ResolvesModelReference,
        ResolvesSourceStrategies;

    /**
     * @var ModelInformationRepositoryInterface
     */
    private $infoRepository;


    /**
     * @param ModelInformationRepositoryInterface $infoRepository
     */
    public function __construct(ModelInformationRepositoryInterface $infoRepository)
    {
        $this->infoRepository = $infoRepository;
    }


    /**
     * Returns reference for a given model instance.
     *
     * @param Model  $model
     * @param string $strategy
     * @param string $source
     * @return string
     */
    public function getReferenceForModel(Model $model, $strategy, $source)
    {
        return $this->getReferenceValue($model, $strategy, $source);
    }

    /**
     * Returns a reference for a model, by meta reference data.
     *
     * @param ModelMetaReferenceInterface $referenceData
     * @param                             $key
     * @return string|false     false if the model could not be found
     */
    public function getReferenceForModelMetaReferenceByKey(ModelMetaReferenceInterface $referenceData, $key)
    {
        $query = $this->getQueryBuilderForModelClass($referenceData->model());

        /** @var Model $model */
        $model = $query->find($key);

        if ( ! $model) return false;

        return $this->getReferenceForModel($model, $referenceData->strategy(), $referenceData->source());
    }

    /**
     * Returns references for models by meta reference data, keyed by the model keys.
     *
     * @param ModelMetaReferenceInterface $referenceData
     * @param string|null                 $search           optional search string to limit results
     * @return array associative
     */
    public function getReferencesForModelMetaReference(ModelMetaReferenceInterface $referenceData, $search = null)
    {
        $modelClass = $referenceData->model();

        $query = $this->getQueryBuilderForModelClass($modelClass);

        $this->applySortingToQueryBuilder($query, $referenceData->source(), $referenceData->sortDirection());

        if (null !== $search) {
            $this->applySearchTermFilterToQueryBuilder($query, $referenceData->target(), $search);
        }

        // If set, apply an (extra) contextual modification to the query builder
        if ($referenceData->contextStrategy()) {
            $this->applyContextStrategyToQueryBuilder(
                $query,
                $referenceData->contextStrategy(),
                $referenceData->parameters()
            );
        }

        /** @var Collection|Model[] $models */
        $models = $query->get();

        // Get references for query results, keyed by primary key
        $references = [];

        foreach ($models as $model) {

            $references[ $model->getKey() ] = $this->getReferenceForModel(
                $model,
                $referenceData->strategy(),
                $referenceData->source()
            );
        }

        return $references;
    }

    /**
     * Returns
     *
     * @param string $modelClass
     * @return Builder
     */
    protected function getQueryBuilderForModelClass($modelClass)
    {
        if ( ! is_a($modelClass, Model::class, true)) {
            throw new UnexpectedValueException("{$modelClass} is not an Eloquent model.");
        }

        // If the targeted model is a CMS model, we can default back to its reference data
        // if not specifics are given, otherwise, a custom fallback reference must be used.
        if ($info = $this->getCmsModelInformation($modelClass)) {

            // Get model records, filtered as necessary
            $repository = $this->getModelRepositoryForInformation($info);

            /** @var Model[] $models */
            return $repository->query();
        }

        // The model is not part of the CMS
        return $this->getQueryBuilderForNonCmsModelClass($modelClass);
    }

    /**
     * Returns a query builder instance for a given model class.
     *
     * @param string $modelClass
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getQueryBuilderForNonCmsModelClass($modelClass)
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
     * Applies search term based filtering to a query builder.
     *
     * @param Builder     $query
     * @param string|null $target
     * @param string      $search
     * @return $this
     */
    protected function applySearchTermFilterToQueryBuilder($query, $target, $search)
    {
        // Make sure we have a target to work with
        $target = $target ?: $query->getModel()->getKeyName();

        // todo: interpret/resolve target, build query for target somehow

        // todo: replace this debug code
        $query->where($target, 'like', '%' . $search . '%');

        return $this;
    }

    /**
     * Applies sorting to a query builder, based on source string.
     *
     * @param Builder $query
     * @param string  $source
     * @param string  $direction    sorting direction: 'asc' or 'desc'
     * @return $this
     */
    protected function applySortingToQueryBuilder($query, $source, $direction = 'asc')
    {
        // todo: replace this debug code
        $query->orderBy($source, strtolower($direction) === 'desc' ? 'desc' : 'asc');

        return $this;
    }

    /**
     * Returns model information for model class, if available.
     *
     * @param string $class
     * @return ModelInformationInterface|ModelInformation|false
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
