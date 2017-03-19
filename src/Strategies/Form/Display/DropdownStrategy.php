<?php
namespace Czim\CmsModels\Strategies\Form\Display;

use Czim\CmsModels\Contracts\View\DropdownStrategyInterface;
use MyCLabs\Enum\Enum;

class DropdownStrategy extends AbstractDefaultStrategy
{

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.dropdown';
    }

    /**
     * Enriches field data before passing it on to the view.
     *
     * @param array $data
     * @return array
     */
    protected function decorateFieldData(array $data)
    {
        $data['dropdownOptions'] = $this->getDropdownOptions();

        return $data;
    }

    /**
     * Returns dropdown options as an associative array with display labels for values.
     *
     * @return array
     */
    protected function getDropdownOptions()
    {
        $values = $this->getDropdownValues();
        $labels = $this->getDropdownLabels();

        // Make sure that labels are set for each value
        foreach ($values as $value) {
            if (isset($labels[ $value ])) continue;

            $labels[ $value ] = $value;
        }

        return array_intersect_key($labels, array_flip($values));
    }

    /**
     * Returns values to include in the dropdown.
     *
     * @return string[]
     */
    protected function getDropdownValues()
    {
        if ($source = array_get($this->field->options(), 'value_source')) {

            $values = $this->getDropdownValuesFromSource($source);

            if (false !== $values) {
                return $values;
            }
        }

        return array_get($this->field->options(), 'values', []);
    }

    /**
     * Returns values from a source FQN, if possible.
     *
     * @param string $source
     * @return string[]|false
     */
    protected function getDropdownValuesFromSource($source)
    {
        if (is_a($source, Enum::class, true)) {
            /** @var Enum $source */
            return array_map(
                function ($value) { return (string) $value; },
                $source::values()
            );
        }

        if (is_a($source, DropdownStrategyInterface::class, true)) {
            /** @var DropdownStrategyInterface $source */
            $source = app($source);
            return $source->values();
        }

        return false;
    }

    /**
     * Returns display labels to show in the dropdown, keyed by value.
     *
     * @return string[]
     */
    protected function getDropdownLabels()
    {
        if ($source = array_get($this->field->options(), 'label_source')) {

            $labels = $this->getDropdownLabelsFromSource($source);

            if (false !== $labels) {
                return $labels;
            }
        }

        $labels = array_get($this->field->options(), 'labels_translated', []);

        if (count($labels)) {
            return array_map('cms_trans', $labels);
        }

        return array_get($this->field->options(), 'labels', []);
    }

    /**
     * Returns display labels from a source FQN, if possible.
     *
     * @param string $source
     * @return string[]|false
     */
    protected function getDropdownLabelsFromSource($source)
    {
        if (is_a($source, DropdownStrategyInterface::class, true)) {
            /** @var DropdownStrategyInterface $source */
            $source = app($source);
            return $source->labels();
        }

        return false;
    }

}
