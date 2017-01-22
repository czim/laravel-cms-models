<?php
namespace Czim\CmsModels\Support\Factories;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Contracts\Support\Factories\FilterStrategyFactoryInterface;
use Czim\CmsModels\Contracts\View\FilterStrategyInterface;
use RuntimeException;

class FilterStrategyFactory implements FilterStrategyFactoryInterface
{

    /**
     * Makes a filter display instance.
     *
     * @param string                        $strategy
     * @param string|null                   $key
     * @param ModelFilterDataInterface|null $info
     * @return FilterStrategyInterface
     */
    public function make($strategy, $key = null, ModelFilterDataInterface $info = null)
    {
        // A filter must have a resolvable strategy for displaying
        if ( ! ($strategyClass = $this->resolveStrategyClass($strategy))) {
            throw new RuntimeException(
                "Could not resolve display strategy class for {$key}: '{$strategy}'"
            );
        }

        /** @var FilterStrategyInterface $instance */
        $instance = app($strategyClass);

        if (null !== $info) {
            $instance->setFilterInformation($info);
        }

        return $instance;
    }


    /**
     * Resolves a filter strategy class.
     *
     * @param $strategy
     * @return bool|string
     */
    protected function resolveStrategyClass($strategy)
    {
        if ( ! str_contains($strategy, '.')) {
            $strategy = config('cms-models.strategies.filter.aliases.' . $strategy, $strategy);
        }

        if (class_exists($strategy) && is_a($strategy, FilterStrategyInterface::class, true)) {
            return $strategy;
        }

        $strategy = $this->prefixStrategyNamespace($strategy);

        if (class_exists($strategy) && is_a($strategy, FilterStrategyInterface::class, true)) {
            return $strategy;
        }

        return false;
    }

    /**
     * @param string $class
     * @return string
     */
    protected function prefixStrategyNamespace($class)
    {
        return rtrim(config('cms-models.strategies.filter.default-namespace'), '\\') . '\\' . $class;
    }

}
