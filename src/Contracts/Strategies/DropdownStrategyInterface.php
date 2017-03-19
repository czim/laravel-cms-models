<?php
namespace Czim\CmsModels\Contracts\Strategies;

interface DropdownStrategyInterface
{

    /**
     * Returns a list of dropdown option values.
     *
     * @return mixed[]
     */
    public function values();

    /**
     * Returns a list of display labels for option values.
     *
     * @return string[]     associative, keyed by option value
     */
    public function labels();

}
