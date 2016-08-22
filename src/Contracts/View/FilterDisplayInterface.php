<?php
namespace Czim\CmsModels\Contracts\View;

use Illuminate\Database\Eloquent\Model;

interface FilterDisplayInterface
{

    /**
     * Applies a strategy to render a filter field.
     *
     * @param Model  $model
     * @param string $key
     * @param mixed  $value
     * @return string
     */
    public function render(Model $model, $key, $value);

}
