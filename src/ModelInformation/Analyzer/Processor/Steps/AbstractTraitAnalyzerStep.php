<?php
namespace Czim\CmsModels\ModelInformation\Analyzer\Processor\Steps;

abstract class AbstractTraitAnalyzerStep extends AbstractAnalyzerStep
{

    /**
     * Returns whether the model class uses a trait by a name or list of names.
     *
     * @param string|string[] $names
     * @return bool
     */
    protected function modelHasTrait($names)
    {
        if ( ! is_array($names)) {
            $names = (array) $names;
        }

        return (bool) count(array_intersect($names, $this->getTraitNames()));
    }

    /**
     * Returns a list of all relevant traits.
     *
     * @return string[]
     */
    protected function getTraitNames()
    {
        return $this->classUsesDeepTraits($this->model());
    }

    /**
     * Returns all traits used by a class (at any level).
     *
     * @param mixed $class
     * @return string[]
     */
    protected function classUsesDeepTraits($class)
    {
        $traits = [];

        // Get traits of all parent classes
        do {
            $traits = array_merge(class_uses($class), $traits);
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traitsToSearch = $traits;
        while ( ! empty($traitsToSearch)) {
            $newTraits      = class_uses(array_pop($traitsToSearch));
            $traits         = array_merge($newTraits, $traits);
            $traitsToSearch = array_merge($newTraits, $traitsToSearch);
        };

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait), $traits);
        }

        return array_unique($traits);
    }

}
