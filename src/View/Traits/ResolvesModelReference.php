<?php
namespace Czim\CmsModels\View\Traits;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\View\ReferenceStrategyInterface;
use Illuminate\Database\Eloquent\Model;

trait ResolvesModelReference
{
    use ResolvesSourceStrategies;

    /**
     * Returns the model reference as a string.
     *
     * @param Model $model
     * @return string
     */
    protected function getReferenceValue(Model $model)
    {
        $strategy = $this->makeReferenceStrategy($model);

        if ( ! $strategy) {
            return $this->getReferenceFallback($model);
        }

        $source = $this->getReferenceSource($model);

        return $strategy->render($model, $source);
    }

    /**
     * Returns the fall-back (for when no reference strategy is available).
     *
     * @param Model $model
     * @return mixed|string
     */
    protected function getReferenceFallback(Model $model)
    {
        // Use reference method if the model has one
        if (method_exists($model, 'getReferenceAttribute')) {
            return $model->reference;
        }

        if (method_exists($model, 'getReference')) {
            return $model->getReference();
        }

        return (string) $model->getKey();
    }

    /**
     * Returns strategy instance for getting reference string.
     *
     * @param Model $model
     * @return null|ReferenceStrategyInterface
     */
    protected function makeReferenceStrategy(Model $model)
    {
        // Get model information for the model
        $information = $this->getInformationRepository()->getByModel($model);

        // If the model is not part of the CMS, fall back
        if ( ! $information) return null;

        $strategy = $information->reference->strategy;

        if ( ! $strategy) {
            $strategy = config('cms-models.strategies.reference.default-strategy');
        }

        if ( ! $strategy) return null;


        // If the strategy indicates the FQN of display strategy,
        // or a classname that can be found in the default strategy name path, use it.
        if ($strategyClass = $this->resolveReferenceStrategyClass($strategy)) {

            /** @var ReferenceStrategyInterface $instance */
            return app($strategyClass);
        }

        return null;
    }

    /**
     * Returns the source to feed to the reference strategy.
     *
     * @param Model $model
     * @return mixed
     */
    protected function getReferenceSource(Model $model)
    {
        // Get model information for the model
        $information = $this->getInformationRepository()->getByModel($model);

        if ($information && $information->reference->source) {
            $source = $information->reference->source;
        } else {
            $source = $model->getKeyName();
        }

        return $this->resolveModelSource($model, $source);
    }

    /**
     * Resolves strategy assuming it is the class name or FQN of a sort interface implementation,
     * or a configured alias.
     *
     * @param $strategy
     * @return string|false     returns full class namespace if it was resolved succesfully
     */
    protected function resolveReferenceStrategyClass($strategy)
    {
        if ( ! str_contains($strategy, '.')) {
            $strategy = config('cms-models.strategies.reference.aliases.' . $strategy, $strategy);
        }

        if (class_exists($strategy) && is_a($strategy, ReferenceStrategyInterface::class, true)) {
            return $strategy;
        }

        $strategy = $this->prefixReferenceStrategyNamespace($strategy);

        if (class_exists($strategy) && is_a($strategy, ReferenceStrategyInterface::class, true)) {
            return $strategy;
        }

        return false;
    }

    /**
     * @param string $class
     * @return string
     */
    protected function prefixReferenceStrategyNamespace($class)
    {
        return rtrim(config('cms-models.strategies.reference.default-namespace'), '\\') . '\\' . $class;
    }

    /**
     * @return ModelInformationRepositoryInterface
     */
    protected function getInformationRepository()
    {
        return app(ModelInformationRepositoryInterface::class);
    }

}
