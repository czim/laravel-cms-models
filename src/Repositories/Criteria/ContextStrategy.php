<?php
namespace Czim\CmsModels\Repositories\Criteria;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ContextStrategyInterface;
use Czim\Repository\Contracts\BaseRepositoryInterface;
use Czim\Repository\Contracts\CriteriaInterface;
use Czim\Repository\Contracts\ExtendedRepositoryInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

class ContextStrategy implements CriteriaInterface
{

    /**
     * @var string
     */
    protected $strategy;

    /**
     * @var ModelInformationInterface
     */
    protected $information;


    /**
     * @param string                    $strategy context strategy
     * @param ModelInformationInterface $information
     */
    public function __construct($strategy, ModelInformationInterface $information)
    {
        $this->strategy    = $strategy;
        $this->information = $information;
    }


    /**
     * @param Model|DatabaseBuilder|EloquentBuilder               $model
     * @param BaseRepositoryInterface|ExtendedRepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, BaseRepositoryInterface $repository)
    {
        $strategy = $this->strategy;

        if ( ! $strategy) {
            $strategy = config('cms-models.strategies.repository.default-strategy');
        }

        // If the strategy indicates the FQN of display strategy,
        // or a classname that can be found in the default strategy name path, use it.
        if ( ! ($strategyClass = $this->resolveStrategyClass($strategy))) {
            return $model;
        }

        /** @var ContextStrategyInterface $instance */
        $instance = app($strategyClass);

        return $instance->apply($model, $this->information);
    }

    /**
     * Resolves strategy assuming it is the class name or FQN of a sort interface implementation,
     * or a configured alias.
     *
     * @param $strategy
     * @return string|false     returns full class namespace if it was resolved succesfully
     */
    protected function resolveStrategyClass($strategy)
    {
        if ( ! str_contains($strategy, '.')) {
            $strategy = config('cms-models.strategies.repository.aliases.' . $strategy, $strategy);
        }

        if (class_exists($strategy) && is_a($strategy, ContextStrategyInterface::class, true)) {
            return $strategy;
        }

        $strategy = $this->prefixStrategyNamespace($strategy);

        if (class_exists($strategy) && is_a($strategy, ContextStrategyInterface::class, true)) {
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
        return rtrim(config('cms-models.strategies.repository.default-namespace'), '\\') . '\\' . $class;
    }

}
