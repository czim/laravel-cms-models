<?php
namespace Czim\CmsModels\Repositories\Criteria;

use Czim\CmsModels\Support\Strategies\Traits\ResolvesRepositoryContextStrategy;
use Czim\Repository\Contracts\BaseRepositoryInterface;
use Czim\Repository\Contracts\CriteriaInterface;
use Czim\Repository\Contracts\ExtendedRepositoryInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

class ContextStrategy implements CriteriaInterface
{
    use ResolvesRepositoryContextStrategy;

    /**
     * @var string
     */
    protected $strategy;

    /**
     * The parameters for the context strategy
     *
     * @var array
     */
    protected $parameters;


    /**
     * @param string $strategy      context strategy
     * @param array  $parameters
     */
    public function __construct($strategy, array $parameters = [])
    {
        $this->strategy   = $strategy;
        $this->parameters = $parameters;
    }


    /**
     * @param Model|DatabaseBuilder|EloquentBuilder               $model
     * @param BaseRepositoryInterface|ExtendedRepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, BaseRepositoryInterface $repository)
    {
        $strategy = $this->resolveContextStrategy($this->strategy);

        if ( ! $strategy) {
            return $model;
        }

        return $strategy->apply($model, $this->parameters);
    }

}
