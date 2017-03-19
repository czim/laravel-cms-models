<?php
namespace Czim\CmsModels\Strategies\ListColumn;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Support\Strategies\Traits\GetsNestedRelations;
use Czim\CmsModels\Support\Strategies\Traits\ModifiesQueryForContext;
use Czim\CmsModels\Support\Strategies\Traits\ResolvesModelReference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class RelationReference extends AbstractListDisplayStrategy
{
    use GetsNestedRelations,
        ResolvesModelReference,
        ModifiesQueryForContext;

    /**
     * @var ModelInformationInterface|ModelInformation|null
     */
    protected $referenceModelInformation;

    /**
     * Renders a display value to print to the list view.
     *
     * @param Model $model
     * @param mixed $source     source column, method name or value
     * @return string
     */
    public function render(Model $model, $source)
    {
        // Get all related records, if possible

        $relation = $this->getActualNestedRelation($model, $source);

        if ( ! $relation) {
            return $this->getEmptyReference();
        }

        // Note that there is no way we can reliably apply repository context for
        // a morph relation; we cannot know or sensibly generalize for the related models.
        if ( ! $this->isMorphRelation($relation)) {
            $query = $this->modifyRelationQueryForContext($relation->getRelated(), $relation);
        }

        /** @var Collection|Model[] $models */
        $models = $query->get();

        if ( ! count($models)) {
            return $this->getEmptyReference();
        }

        $references = [];

        // Convert records into reference strings
        foreach ($models as $model) {

            $references[] = $this->getReference($model);
        }

        return $this->implodeReferences($references);
    }

    /**
     * Returns a reference representation for a single model.
     *
     * @param Model $model
     * @return string
     */
    protected function getReference(Model $model)
    {
        $reference = $this->getReferenceValue($model);

        return $this->wrapReference($reference);
    }

    /**
     * Returns string with imploded references.
     *
     * @param string[] $references
     * @return string
     */
    protected function implodeReferences(array $references)
    {
        return implode('; ', $references);
    }

    /**
     * Returns an empty reference representation.
     *
     * @return string
     */
    protected function getEmptyReference()
    {
        return '<span class="relation-reference reference-empty">&nbsp;</span>';
    }

    /**
     * Wraps a reference string in a simple container.
     *
     * @param string $reference
     * @return string
     */
    protected function wrapReference($reference)
    {
        return '<span class="relation-reference">' . e($reference) . '</span>';
    }

    /**
     * Returns whether a given relation is (in a relevant way) polymorph.
     *
     * This will only return true for the relations that have undetermined related models.
     *
     * @param Relation $relation
     * @return bool
     */
    protected function isMorphRelation($relation)
    {
        return (    $relation instanceof MorphMany
                ||  $relation instanceof MorphOne);
    }

}
