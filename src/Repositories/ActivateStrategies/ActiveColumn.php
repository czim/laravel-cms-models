<?php
namespace Czim\CmsModels\Repositories\ActivateStrategies;

use Czim\CmsModels\Contracts\Repositories\ActivateStrategyInterface;
use Illuminate\Database\Eloquent\Model;

class ActiveColumn implements ActivateStrategyInterface
{

    /**
     * @var string
     */
    protected $column = 'active';

    /**
     * Updates a model's active status.
     *
     * @param Model $model
     * @param bool  $activate
     * @return bool
     */
    public function update(Model $model, $activate = true)
    {
        $model->{$this->column} = (bool) $activate;
        $model->save();

        return $model->{$this->column};
    }

    /**
     * @param string $column
     * @return $this
     */
    public function setColumn($column)
    {
        $this->column = $column;

        return $this;
    }

}
