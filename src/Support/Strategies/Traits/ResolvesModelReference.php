<?php
namespace Czim\CmsModels\Support\Strategies\Traits;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Strategies\ReferenceStrategyInterface;
use Illuminate\Database\Eloquent\Model;

trait ResolvesModelReference
{

    /**
     * Returns the model reference as a string.
     *
     * @param Model       $model
     * @param string|null $strategy optional reference strategy that overrides default
     * @param string|null $source   optional reference source that overrides default
     * @return string
     */
    protected function getReferenceValue(Model $model, $strategy = null, $source = null)
    {
        $strategy = $this->determineModelReferenceStrategy($model, $strategy);

        // If we have no strategy at all to fall back on, use a hard-coded reference
        if ( ! $strategy) {
            return $this->getReferenceFallback($model);
        }

        if ( ! $source) {
            $source = $this->determineModelReferenceSource($model);
        }

        return $strategy->render($model, $source);
    }

    /**
     * Returns the strategy reference instance for a model.
     *
     * @param Model       $model
     * @param string|null $strategy
     * @return ReferenceStrategyInterface|null
     */
    protected function determineModelReferenceStrategy(Model $model, $strategy = null)
    {
        if (null !== $strategy) {
            $strategy = $this->makeReferenceStrategyInstance($strategy);
        }

        if ( ! $strategy) {
            $strategy = $this->makeReferenceStrategy($model);
        }

        if ( ! $strategy) {
            $strategy = $this->getDefaultReferenceStrategyInstance();
        }

        return $strategy;
    }

    /**
     * Returns the reference source string.
     *
     * @param Model $model
     * @return null|string
     */
    protected function determineModelReferenceSource(Model $model)
    {
        $source = $this->getReferenceSource($model);

        // If we have no source to fall back on at all, use the model key
        if ( ! $source) {
            $source = $model->getKeyName();
        }

        return $source;
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

        return $this->makeReferenceStrategyInstance($strategy);
    }

    /**
     * @param string|null $strategy
     * @return ReferenceStrategyInterface|null
     */
    protected function makeReferenceStrategyInstance($strategy)
    {
        if (null === $strategy) {
            return null;
        }

        // If the strategy indicates the FQN of display strategy,
        // or a classname that can be found in the default strategy name path, use it.
        $strategyClass = $this->resolveReferenceStrategyClass($strategy);

        if ( ! $strategyClass) {
            return null;
        }

        /** @var  $instance */
        return app($strategyClass);
    }

    /**
     * Returns the source to feed to the reference strategy.
     *
     * @param Model       $model
     * @param string|null $source
     * @return string|null
     */
    protected function getReferenceSource(Model $model, $source = null)
    {
        if (null === $source) {
            // Get model information for the model
            $information = $this->getInformationRepository()->getByModel($model);

            if ($information && $information->reference->source) {
                $source = $information->reference->source;
            }
        }

        if ( ! $source) return null;

        return $source;
    }

    /**
     * Resolves strategy assuming it is the class name or FQN of a sort interface implementation,
     * or a configured alias.
     *
     * @param string $strategy
     * @return string|false     returns full class namespace if it was resolved succesfully
     */
    protected function resolveReferenceStrategyClass($strategy)
    {
        if ( ! empty($strategy)) {

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
     * @return ReferenceStrategyInterface|null
     */
    protected function getDefaultReferenceStrategyInstance()
    {
        $class = config('cms-models.strategies.reference.default-strategy');

        if ( ! $class) return null;

        return new $class;
    }

    /**
     * @return ModelInformationRepositoryInterface
     */
    protected function getInformationRepository()
    {
        return app(ModelInformationRepositoryInterface::class);
    }

}
