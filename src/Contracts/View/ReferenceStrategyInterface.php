<?php
namespace Czim\CmsModels\Contracts\View;

use Illuminate\Database\Eloquent\Model;

interface ReferenceStrategyInterface
{

    /**
     * Returns model reference string
     *
     * @param Model $model
     * @param mixed $source
     * @return string
     */
    public function render(Model $model, $source);

}
