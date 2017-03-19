<?php
namespace Czim\CmsModels\Strategies\Form\Display;

use Conner\Tagging\Taggable;
use Illuminate\Contracts\Support\Arrayable;

class TaggableAutocompleteStrategy extends AbstractDefaultStrategy
{
    /**
     * Thesholds for increasing minimum input length for autocomplete fields
     */
    const AUTOCOMPLETE_INPUT_THRESHOLD_ONE   = 50;
    const AUTOCOMPLETE_INPUT_THRESHOLD_TWO   = 250;
    const AUTOCOMPLETE_INPUT_THRESHOLD_THREE = 1000;

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.taggable_autocomplete';
    }

    /**
     * Normalizes a value to make sure it can be processed uniformly.
     *
     * @param mixed $value
     * @param bool  $original
     * @return mixed
     */
    protected function normalizeValue($value, $original = false)
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        return [ $value ];
    }

    /**
     * Enriches field data before passing it on to the view.
     *
     * @param array $data
     * @return array
     */
    protected function decorateFieldData(array $data)
    {
        // Get the available tags.

        /** @var Taggable $modelClass */
        $modelClass = get_class($this->model);

        $data['tags'] = $modelClass::existingTags()->pluck('name')->toArray();

        // Determine the min. input length to trigger autocomplete ajax lookups
        $data['minimumInputLength'] = array_get(
            $this->field->options(),
            'minimum_input_length',
            $this->determineBestMinimumInputLength(count($data['tags']))
        );

        return $data;
    }

    /**
     * Returns the best minimum input length for autocomplete input ajax triggers.
     *
     * @param int $total
     * @return int
     */
    protected function determineBestMinimumInputLength($total)
    {
        if (null === $total) {
            return 1;
        }

        if ($total > static::AUTOCOMPLETE_INPUT_THRESHOLD_THREE) {
            return 3;
        }

        if ($total > static::AUTOCOMPLETE_INPUT_THRESHOLD_TWO) {
            return 2;
        }

        if ($total > static::AUTOCOMPLETE_INPUT_THRESHOLD_ONE) {
            return 1;
        }

        return 0;
    }

}
