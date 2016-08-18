<?php
namespace Czim\CmsModels\Contracts\View;

use Illuminate\Database\Eloquent\Model;

interface ListStrategyInterface
{

    /**
     * Applies a strategy to renders a list value from its source.
     *
     * @param Model             $model
     * @param string|array      $strategy
     * @param string|array|null $source
     * @return string
     */
    public function render(Model $model, $strategy, $source);

}
