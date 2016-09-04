<?php
namespace Czim\CmsModels\View\Traits;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use UnexpectedValueException;

/**
 * Class HandlesTranslatedTarget
 *
 * Assumes 'translatable' as the translation strategy.
 */
trait HandlesTranslatedTarget
{

    /**
     * Returns whether a (nested) target attribute is translated.
     *
     * @param string|array       $target
     * @param Model|Translatable $model
     * @return bool
     */
    protected function isTranslatedTargetAttribute($target, Model $model)
    {
        // Normalize target as an array
        if (is_string($target)) {
            $target = explode('.', $target);
        }

        if ( ! is_array($target)) {
            throw new UnexpectedValueException("Target attribute/column should be a dot-notation string or array");
        }

        $current = array_shift($target);

        // If it is a direct attribute of the model, check whether it is translated
        if ( ! count($target)) {
            return (method_exists($model, 'isTranslationAttribute') && $model->isTranslationAttribute($current));
        }

        // Find the relations and the attribute, resolving relation methods where possible
        if (method_exists($model, $current)) {

            try {
                $relation = $model->{$current}();
            } catch (\Exception $e) {
                $relation = false;
            }

            if ( ! $relation || ! ($relation instanceof Relation)) {
                return false;
            }

            // Recursively check nested relation
            return $this->isTranslatedTargetAttribute($target, $relation->getRelated());
        }

        return false;
    }

    /**
     * Applies locale-based restriction to a translations relation query.
     *
     * @param Builder     $query
     * @param null|string $locale
     * @param bool        $allowFallback
     */
    protected function applyLocaleRestriction($query, $locale = null, $allowFallback = true)
    {
        $localeKey = $this->getLocaleKey();
        $locale    = $locale ?: app()->getLocale();
        $fallback  = $this->getFallbackLocale();

        $query->where($localeKey, $locale);

        if ($allowFallback && $fallback && $fallback != $locale) {
            $query->orWhere($localeKey, $fallback);
        }
    }


    /**
     * Returns the locale column name in the translations table.
     *
     * @return string
     */
    protected function getLocaleKey()
    {
        return config('translatable.locale_key', 'locale');
    }

    /**
     * Returns the locale that is used as fallback for translations.
     *
     * @return string|false
     */
    protected function getFallbackLocale()
    {
        if ( ! config('translatable.use_fallback')) {
            return false;
        }

        return config('translatable.fallback_locale');
    }

}
