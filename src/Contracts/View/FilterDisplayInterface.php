<?php
namespace Czim\CmsModels\Contracts\View;

interface FilterDisplayInterface
{

    /**
     * Applies a strategy to render a filter field.
     *
     * @param string $key
     * @param mixed  $value
     * @return string
     */
    public function render($key, $value);

}
