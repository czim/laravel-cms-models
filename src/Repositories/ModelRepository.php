<?php
namespace Czim\CmsModels\Repositories;

use Czim\Repository\BaseRepository;
use Illuminate\Support\Collection;

class ModelRepository extends BaseRepository
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
        parent::__construct(app(), new Collection);

        $this->modelClass = $modelClass;
    }

    /**
     * @return string
     */
    public function model()
    {
        return $this->modelClass;
    }

}
