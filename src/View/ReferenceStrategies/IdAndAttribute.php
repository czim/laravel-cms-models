<?php
namespace Czim\CmsModels\View\ReferenceStrategies;

use Czim\CmsModels\Contracts\View\ReferenceStrategyInterface;
use Illuminate\Database\Eloquent\Model;

class IdAndAttribute implements ReferenceStrategyInterface
{

    /**
     * Returns model reference string
     *
     * @param Model $model
     * @param mixed $source
     * @return string
     */
    public function render(Model $model, $source)
    {
        return '#' . $model->getKey() . ': ' . $source;
    }

}
