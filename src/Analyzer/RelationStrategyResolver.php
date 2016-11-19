<?php
namespace Czim\CmsModels\Analyzer;

use Czim\CmsModels\Support\Data\ModelRelationData;
use Czim\CmsModels\Support\Enums\FormDisplayStrategy;
use Czim\CmsModels\Support\Enums\FormStoreStrategy;
use Czim\CmsModels\Support\Enums\ListDisplayStrategy;
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
            case RelationType::BELONGS_TO_THROUGH:
            case RelationType::HAS_ONE:
            case RelationType::MORPH_ONE:
            case RelationType::MORPH_TO:
                return ListDisplayStrategy::RELATION_REFERENCE;

            case RelationType::BELONGS_TO_MANY:
            case RelationType::HAS_MANY:
            case RelationType::MORPH_MANY:
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
            case RelationType::MORPH_ONE:
            case RelationType::HAS_ONE:
                $type = FormDisplayStrategy::RELATION_SINGLE_AUTOCOMPLETE;
                break;

            case RelationType::BELONGS_TO_MANY:
            case RelationType::HAS_MANY:
            case RelationType::MORPH_MANY:
            case RelationType::MORPH_TO_MANY:
            case RelationType::MORPHED_BY_MANY:
                $type = FormDisplayStrategy::RELATION_PLURAL_AUTOCOMPLETE;
                break;

            case RelationType::MORPH_TO:
                $type = FormDisplayStrategy::RELATION_SINGLE_MORPH_AUTOCOMPLETE;
                break;
        }

        return $type;
    }

    /**
     * Determines a form store strategy string for given relation data.
     *
     * @param ModelRelationData $data
     * @return string|null
     */
    public function determineFormStoreStrategy(ModelRelationData $data)
    {
        $type       = null;
        $parameters = [];

        // Determine strategy alias
        switch ($data->type) {

            case RelationType::BELONGS_TO:
            case RelationType::BELONGS_TO_THROUGH:
            case RelationType::MORPH_ONE:
            case RelationType::HAS_ONE:
                $type = FormStoreStrategy::RELATION_SINGLE_KEY;
                break;

            case RelationType::BELONGS_TO_MANY:
            case RelationType::HAS_MANY:
            case RelationType::MORPH_MANY:
            case RelationType::MORPH_TO_MANY:
            case RelationType::MORPHED_BY_MANY:
                $type = FormStoreStrategy::RELATION_PLURAL_KEYS;
                break;

            case RelationType::MORPH_TO:
                $type = FormStoreStrategy::RELATION_SINGLE_MORPH;
                break;
        }


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

    /**
     * Determines a form store's options for given relation data.
     *
     * @param ModelRelationData $data
     * @return array
     */
    public function determineFormStoreOptions(ModelRelationData $data)
    {
        $options = [];

        switch ($data->type) {

            case RelationType::MORPH_TO:
                // todo: set special morph options to mark the targetable model classes
                break;
        }

        return $options;
    }

}
