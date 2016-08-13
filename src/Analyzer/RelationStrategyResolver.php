<?php
namespace Czim\CmsModels\Analyzer;

use Czim\CmsModels\Support\Data\ModelRelationData;
use Czim\CmsModels\Support\Enums\AttributeFormStrategy;
use Czim\CmsModels\Support\Enums\RelationFormStrategy;
use Czim\CmsModels\Support\Enums\RelationType;

class RelationStrategyResolver
{

    /**
     * Determines a form field strategy string for given relation data.
     *
     * @param ModelRelationData $data
     * @return string|null
     */
    public function determineFormStrategy(ModelRelationData $data)
    {
        $type = null;

        switch ($data->type) {

            case RelationType::BELONGS_TO:
                $type = RelationFormStrategy::BELONGS_TO_DROPDOWN;
                break;

            case RelationType::HAS_ONE:
                $type = RelationFormStrategy::HAS_ONE_DROPDOWN;
                break;

            case RelationType::HAS_MANY:
                $type = RelationFormStrategy::HAS_MANY_DROPDOWN;
                break;

            case RelationType::BELONGS_TO_THROUGH:
                $type = RelationFormStrategy::BELONGS_TO_THROUGH_DROPDOWN;
                break;

        }

        return $type;
    }

}
