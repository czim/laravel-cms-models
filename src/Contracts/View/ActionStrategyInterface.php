<?php
namespace Czim\CmsModels\Contracts\View;

use Czim\CmsModels\Contracts\Data\ModelActionReferenceDataInterface;
use Illuminate\Database\Eloquent\Model;

interface ActionStrategyInterface
{

    /**
     * Initializes the strategy instance for further calls.
     *
     * @param ModelActionReferenceDataInterface $data
     * @param string                            $modelClass     FQN of current model
     * @return $this
     */
    public function initialize(ModelActionReferenceDataInterface $data, $modelClass);

    /**
     * Returns the action link for a given model instance.
     *
     * @param Model $model
     * @return string|false
     */
    public function link(Model $model);

}
