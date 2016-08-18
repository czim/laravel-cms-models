<?php
namespace Czim\CmsModels\Repositories;

use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;
use Czim\Repository\BaseRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;

class ModelRepository extends BaseRepository implements ModelRepositoryInterface
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

}
