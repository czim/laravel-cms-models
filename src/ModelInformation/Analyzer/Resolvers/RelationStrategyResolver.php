<?php
namespace Czim\CmsModels\ModelInformation\Analyzer\Resolvers;

use Czim\CmsModels\ModelInformation\Data\ModelRelationData;
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
            case RelationType::MORPH_TO_MANY:
            case RelationType::MORPHED_BY_MANY:
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
                // Set special morph options to mark the targetable model classes
                $models = $this->determineMorphModelsForRelationData($data);

                if (count($models)) {
                    $options['models'] = $models;
                }
                break;
        }

        return $options;
    }

    /**
     * Determines models for MorphTo relation data.
     *
     * @param ModelRelationData $data
     * @return string[]
     */
    protected function determineMorphModelsForRelationData(ModelRelationData $data)
    {
        // If models were set during analysis, trust them
        if ($data->morphModels && count($data->morphModels)) {
            return $data->morphModels;
        }

        // We do not have access to modelinformation here, so
        // if it cannot be derived from data, the enrichment step must handle this using context info.
        return [];
    }

}
