<?php
namespace Czim\CmsModels\Test\Helpers\Strategies\Dropdown;

use Czim\CmsModels\Contracts\Strategies\DropdownStrategyInterface;

class TestEnum implements DropdownStrategyInterface
{

    /**
     * Returns a list of dropdown option values.
     *
     * @return mixed[]
     */
    public function values()
    {
        return ['a', 'b', 'c'];
    }

    /**
     * Returns a list of display labels for option values.
     *
     * @return string[]     associative, keyed by option value
     */
    public function labels()
    {
        return [
            'a' => 'Label A',
            'b' => 'Label B',
        ];
    }
}
