<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

use Czim\CmsModels\Contracts\Repositories\ModelReferenceRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use UnexpectedValueException;

abstract class AbstractRelationStrategy extends AbstractFormFieldStoreStrategy
{

    /**
     * Retrieves current values from a model
     *
     * @param Model  $model
     * @param string $source
     * @return mixed
     */
    public function retrieve(Model $model, $source)
    {
        if ($this->isTranslated()) {

            $keys      = [];
            $localeKey = config('translatable.locale_key', 'locale');

            foreach ($model->translations as $translation) {
                /** @var Relation $relation */
                $relation = $translation->{$source}();

                $keys[ $translation->{$localeKey} ] = $this->getValueFromRelationQuery($relation);
            }

            return $keys;
        }

        $relation = $this->resolveModelSource($model, $source);

        if ( ! ($relation instanceof Relation)) {
            throw new UnexpectedValueException(
                "{$source} did not resolve to a relation for " . get_class($this) . " on " . get_class($model)
            );
        }


        return $this->getValueFromRelationQuery($relation);
    }

    /**
     * Returns the value per relation for a given relation query builder.
     *
     * @param Builder|Relation $query
     * @return mixed|null
     */
    abstract protected function getValueFromRelationQuery($query);

    /**
     * Prepares the relation query builder for CMS retrieval.
     *
     * Call from getValueFromRelationQuery if required.
     *
     * @param Relation|Builder $query
     * @return Builder
     */
    protected function prepareRelationQuery($query)
    {
        return $query->withoutGlobalScopes();
    }

    /**
     * @return ModelReferenceRepositoryInterface
     */
    protected function getModelReferenceRepository()
    {
        return app(ModelReferenceRepositoryInterface::class);
    }

}
