<?php
namespace Czim\CmsModels\Repositories;

use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;
use Czim\Repository\ExtendedRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ModelRepository extends ExtendedRepository implements ModelRepositoryInterface
{

    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @param string $modelClass
     */
    public function __construct($modelClass = null)
    {
        $this->modelClass = $modelClass;

        parent::__construct(app(Application::class), new Collection);
    }

    /**
     * @return string
     */
    public function model()
    {
        return $this->modelClass;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $query = parent::query();

        if ($query instanceof Model) {
            $query = $query->query();
        }

        return $query;
    }

}
