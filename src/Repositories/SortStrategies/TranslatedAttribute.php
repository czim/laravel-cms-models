<?php
namespace Czim\CmsModels\Repositories\SortStrategies;

use Czim\CmsModels\Contracts\Repositories\SortStrategyInterface;
use DB;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Class TranslatedAttribute
 *
 * Assumes 'translatable' translation strategy.
 */
class TranslatedAttribute implements SortStrategyInterface
{

    /**
     * Whether null will be forced to be ordered last always.
     *
     * @var bool
     */
    protected $nullLast = true;


    /**
     * Applies the sort to a query/model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param string $direction     asc|desc
     * @return Builder
     */
    public function apply($query, $column, $direction = 'asc')
    {
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        $modelTable = $query->getModel()->getTable();
        $modelKey   = $query->getModel()->getKeyName();

        $translationRelation = $this->getTranslationsRelation($query);

        $translationTable   = $translationRelation->getRelated()->getTable();
        $translationForeign = $translationRelation->getForeignKey();

        $localeKey = $this->getLocaleKey();

        $query = $query
            ->select("{$modelTable}.*")
            ->leftJoin(
                $translationTable,
                function ($join) use (
                    $translationForeign,
                    $translationTable,
                    $modelTable,
                    $modelKey,
                    $localeKey
                ) {
                    $join->on("{$translationForeign}", '=', "{$modelTable}.{$modelKey}");

                    // Check if we need to work with a fallback locale
                    $locale   = app()->getLocale();
                    $fallback = $this->getFallbackLocale();

                    $join->where("{$translationTable}.{$localeKey}", '=', $locale);

                    if ($fallback && $fallback != $locale) {
                        $join->orWhere("{$translationTable}.{$localeKey}", '=', $fallback);
                    }
                }

            )
            ->groupBy("{$modelTable}.{$modelKey}");

        if ($this->nullLast) {
            $query->orderBy(DB::raw("IF(`{$translationTable}`.`{$column}` IS NULL,1,0)"));
        }

        $query->orderBy("{$translationTable}.{$column}", $direction);

        return $query;
    }

    /**
     * Returns relation instance for translations.
     *
     * @param Builder $query
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    protected function getTranslationsRelation($query)
    {
        /** @var Builder $query */
        /** @var Model|Translatable $model */
        $model = $query->getModel();

        if ( ! method_exists($model, 'translations')) {
            throw new RuntimeException('Model ' . get_class($model) . ' is not a translated model.');
        }

        return $model->translations();
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
