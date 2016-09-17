<?php
namespace Czim\CmsModels\Repositories\Criteria;

use Czim\Repository\Contracts\BaseRepositoryInterface;
use Czim\Repository\Contracts\CriteriaInterface;
use Czim\Repository\Contracts\ExtendedRepositoryInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

class DisableGlobalScopes implements CriteriaInterface
{

    /**
     * @var null|array
     */
    protected $scopes = null;


    /**
     * @param string[]|true $globalScopes
     */
    public function __construct($globalScopes)
    {
        if (true === $globalScopes) {
            $this->scopes = null;
        } else {
            $this->scopes = $globalScopes;
        }
    }


    /**
     * @param Model|DatabaseBuilder|EloquentBuilder               $query
     * @param BaseRepositoryInterface|ExtendedRepositoryInterface $repository
     * @return mixed
     */
    public function apply($query, BaseRepositoryInterface $repository)
    {
        return $query->withoutGlobalScopes($this->scopes);
    }

}
