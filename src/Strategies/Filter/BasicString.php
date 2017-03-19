<?php
namespace Czim\CmsModels\Strategies\Filter;

use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class BasicString extends AbstractFilterStrategy
{

    /**
     * Whether a single string match should be exact.
     *
     * @var bool
     */
    protected $exact = false;

    /**
     * Whether to split the terms and search for them separately
     *
     * @var bool
     */
    protected $splitTerms = false;

    /**
     * Whether conditions for split terms should be combined with 'or'.
     *
     * @var bool
     */
    protected $combineSplitTermsOr = true;


    /**
     * Applies a strategy to render a filter field.
     *
     * @param string  $key
     * @param mixed   $value
     * @return string|View
     */
    public function render($key, $value)
    {
        return view(
            'cms-models::model.partials.filters.basic-string',
            [
                'label' => $this->filterData ? $this->filterData->label() : $key,
                'key'   => $key,
                'value' => $value,
            ]
        );
    }

    /**
     * Applies a value directly to a builder object.
     *
     * @param Builder   $query
     * @param string    $target
     * @param mixed     $value
     * @param null|bool $combineOr    overrides global value if non-null
     * @param bool      $isFirst      whether this is the first expression (between brackets)
     * @return mixed
     */
    protected function applyValue($query, $target, $value, $combineOr = null, $isFirst = false)
    {
        // If we're splitting terms, the terms will first be split by whitespace
        // otherwise the whole search value will treated at a single string.
        // Array values will always be treated as split string search terms.

        $combineOr = ! $isFirst && ($combineOr === null ? $this->combineOr : $combineOr);

        if ( ! $this->splitTerms && ! is_array($value)) {
            return $this->applyTerm($query, $target, $value, $combineOr);
        }

        if ( ! is_array($value)) {
            $value = $this->splitTerms($value);
        }

        $whereMethod = ! $isFirst && $combineOr ? 'orWhere' : 'where';

        $query->{$whereMethod}(function ($query) use ($value, $target) {

            foreach ($value as $index => $term) {
                $this->applyTerm($query, $target, $term, $index < 1);
            }

        });

        return $query;
    }

    /**
     * Splits a search string into separate terms.
     *
     * @param $value
     * @return string[]
     */
    protected function splitTerms($value)
    {
        return array_filter(preg_split('#\s#', $value));
    }

    /**
     * Applies a single (potentially) split off value directly to a builder object.
     *
     * @param Builder   $query
     * @param string    $target
     * @param mixed     $value
     * @param null|bool $combineOr
     * @param bool      $isFirst      whether this is the first expression (between brackets)
     * @return mixed
     */
    protected function applyTerm($query, $target, $value, $combineOr = null, $isFirst = false)
    {
        $combineOr = ! $isFirst && ($combineOr === null ? $this->combineSplitTermsOr : $combineOr);
        $combine   = $combineOr ? 'or' : 'and';

        if (is_array($value)) {
            return $query->whereIn($target, $value, $combine);
        }

        if ( ! $this->exact) {
            return $query->where($target, 'like', '%' . $value . '%', $combine);
        }

        // @codeCoverageIgnoreStart
        return $query->where($target, '=', $value, $combine);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Returns whether given attribute data represents an attribute that is relevant
     * for performing the filter on.
     *
     * @param ModelAttributeData $attribute
     * @return bool
     */
    protected function isAttributeRelevant(ModelAttributeData $attribute)
    {
        // Only target string based attributes
        return $attribute->cast == AttributeCast::STRING;
    }

}
