<?php
namespace Czim\CmsModels\Repositories\Criteria;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ContextStrategyInterface;
use Czim\CmsModels\View\Traits\ResolvesRepositoryContextStrategy;
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
        $strategy = $this->resolveContextStrategy($this->strategy);

        if ( ! $strategy) {
            return $model;
        }

        return $strategy->apply($model, $this->information);
    }

}
