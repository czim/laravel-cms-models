<?php
namespace Czim\CmsModels\Strategies\ListColumn;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Support\Strategies\Traits\GetsNestedRelations;
use Czim\CmsModels\Support\Strategies\Traits\ModifiesQueryForContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class RelationCount extends AbstractListDisplayStrategy
{
    use GetsNestedRelations,
        ModifiesQueryForContext;

    /**
     * Renders a display value to print to the list view.
     *
     * @param Model $model
     * @param mixed $source     source column, method name or value
     * @return string
     */
    public function render(Model $model, $source)
    {
        $count = $this->getCount(
            $this->getActualNestedRelation($model, $source)
        );

        if ( ! $count) {
            return '<span class="relation-count count-empty">&nbsp;</span>';
        }

        return '<span class="relation-count">' . $count . '</span>';
    }

    /**
     * Returns the count for a given relation.
     *
     * @param Relation $relation
     * @return int
     */
    protected function getCount(Relation $relation)
    {
        if ( ! $relation) return 0;

        $query = $this->modifyRelationQueryForContext($relation->getRelated(), $relation->getQuery());

        return $query->count();
    }

    /**
     * Returns an optional style string for the list display value container.
     *
     * @param Model  $model
     * @param string $source    source column, method name or value
     * @return string|null
     */
    public function style(Model $model, $source)
    {
        return 'text-right';
    }

    /**
     * @return ModelInformationRepositoryInterface
     */
    protected function getInformationRepository()
    {
        return app(ModelInformationRepositoryInterface::class);
    }

}
