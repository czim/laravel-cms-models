<?php
namespace Czim\CmsModels\Repositories\Criteria;

use Czim\CmsModels\Contracts\Strategies\SortStrategyInterface;
use Czim\Repository\Contracts\BaseRepositoryInterface;
use Czim\Repository\Contracts\CriteriaInterface;
use Czim\Repository\Contracts\ExtendedRepositoryInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

class ModelOrderStrategy implements CriteriaInterface
{

    /**
     * @var string
     */
    protected $strategy;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $direction;


    /**
     * @param string $strategy  sorting strategy
     * @param string $source
     * @param string $direction
     */
    public function __construct($strategy, $source, $direction = 'asc')
    {
        $this->strategy  = $strategy;
        $this->source    = $source;
        $this->direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
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
            $strategy = config('cms-models.strategies.list.default-sort-strategy');
        }

        // If the strategy indicates the FQN of display strategy,
        // or a classname that can be found in the default strategy name path, use it.
        if ($strategyClass = $this->resolveStrategyClass($strategy)) {

            /** @var SortStrategyInterface $instance */
            $instance = app($strategyClass);

            return $instance->apply($model, $this->source, $this->direction);
        }

        // If no strategy is defined, fall back to a simple sort
        return $model->orderBy($this->source, $this->direction);
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
            $strategy = config('cms-models.strategies.list.sort-aliases.' . $strategy, $strategy);
        }

        if (class_exists($strategy) && is_a($strategy, SortStrategyInterface::class, true)) {
            return $strategy;
        }

        $strategy = $this->prefixStrategyNamespace($strategy);

        if (class_exists($strategy) && is_a($strategy, SortStrategyInterface::class, true)) {
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
        return rtrim(config('cms-models.strategies.list.default-sort-namespace'), '\\') . '\\' . $class;
    }

}
