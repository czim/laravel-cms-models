<?php
namespace Czim\CmsModels\Analyzer;

use Czim\CmsModels\Support\Data\ModelRelationData;
use Czim\CmsModels\Support\Enums\FormDisplayStrategy;
use Czim\CmsModels\Support\Enums\ListDisplayStrategy;
use Czim\CmsModels\Support\Enums\RelationFormStrategy;
use Czim\CmsModels\Support\Enums\RelationType;

class RelationStrategyResolver
{

    /**
     * Determines a list display strategy string for given relation data.
     *
     * @param ModelRelationData $data
     * @return string|null
     */
    public function determineListDisplayStrategy(ModelRelationData $data)
    {
        switch ($data->type) {

            case RelationType::BELONGS_TO:
            case RelationType::HAS_ONE:
            case RelationType::MORPH_ONE:
            case RelationType::BELONGS_TO_THROUGH:
                return ListDisplayStrategy::RELATION_REFERENCE;

            case RelationType::HAS_MANY:
            case RelationType::MORPH_MANY:
            case RelationType::BELONGS_TO_MANY:
                return ListDisplayStrategy::RELATION_COUNT;

        }

        return null;
    }

    /**
     * Determines a form display strategy string for given relation data.
     *
     * @param ModelRelationData $data
     * @return string|null
     */
    public function determineFormDisplayStrategy(ModelRelationData $data)
    {
        $type = null;

        switch ($data->type) {

            case RelationType::BELONGS_TO:
            case RelationType::BELONGS_TO_THROUGH:
                $type = FormDisplayStrategy::SELECT_DROPDOWN_BELONGS_TO;
                break;

            case RelationType::HAS_ONE:
                $type = FormDisplayStrategy::SELECT_DROPDOWN_HAS_ONE;
                break;

            case RelationType::HAS_MANY:
                $type = FormDisplayStrategy::SELECT_MULTIPLE_HAS_MANY;
                break;
        }

        return $type;
    }

    /**
     * Determines a form store display strategy string for given attribute data.
     *
     * @param ModelRelationData $data
     * @return string|null
     */
    public function determineFormStoreStrategy(ModelRelationData $data)
    {
        $type       = null;
        $parameters = [];

        // Determine strategy alias
        // todo


        // Determine parameters

        if ($data->translated) {
            $parameters[] = 'translated';
        }

        if ($data->nullable) {
            $parameters[] = 'nullable';
        }

        if (count($parameters)) {
            $type .= ':' . implode(',', $parameters);
        }

        return $type;
    }

}
