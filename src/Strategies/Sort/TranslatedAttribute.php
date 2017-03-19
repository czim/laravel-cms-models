<?php
namespace Czim\CmsModels\Strategies\Sort;

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
class TranslatedAttribute extends AbstractSortStrategy
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

        $locale    = app()->getLocale();
        $localeKey = $this->getLocaleKey();

        $supportsIf = $this->databaseSupportsIf($query);

        // build translation subquery to join on, best match first
        $subQueryAlias = uniqid('trans');
        $keyAlias      = uniqid('fk');

        $subQuery = DB::table($translationTable)
            ->select([
                "{$translationForeign} as {$keyAlias}",
                "{$translationTable}.{$column}",
            ])
            ->where(function ($query) use ($locale, $localeKey) {

                // Check if we need to work with a fallback locale
                $fallback = $this->getFallbackLocale();

                $query->where($localeKey, '=', $locale);

                if ($fallback && $fallback != $locale) {
                    $query->orWhere($localeKey, '=', $fallback);
                }
            });

        if ($supportsIf) {
            $subQuery->orderByRaw("IF(`{$localeKey}` = ?,0,1)", [ $locale ]);
        }



        // build the main query, and join the sub
        $query = $query
            ->select("{$modelTable}.*")
            ->leftJoin(
                DB::raw("(" . $subQuery->toSql() . ") as `{$subQueryAlias}`"),
                function ($join) use (
                    $subQueryAlias,
                    $keyAlias,
                    $modelTable,
                    $modelKey
                ) {
                    $join->on("{$subQueryAlias}.{$keyAlias}", '=', "{$modelTable}.{$modelKey}");
                }
            )
            ->addBinding($subQuery->getBindings(), 'join');

        /** @var Builder $query */
        if ($this->nullLast && $supportsIf) {
            $query->orderBy(DB::raw("IF(`{$subQueryAlias}`.`{$column}` IS NULL OR `{$subQueryAlias}`.`{$column}` = '',1,0)"));
        }

        $query->orderBy("{$subQueryAlias}.{$column}", $direction);

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
