<?php
namespace Czim\CmsModels\Support\Strategies\Traits;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormFieldDataInterface;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;

/**
 * Class HasMorphRelationStrategyOptions
 *
 * For form field strategy data that will have options according to this configuration pattern:
 * https://github.com/czim/laravel-cms-models/blob/master/documentation/FormFieldStoreStrategies/RelationSingleMorph.md
 */
trait HasMorphRelationStrategyOptions
{

    /**
     * Returns the model class names that the model may be related to.
     *
     * @param ModelFormFieldDataInterface|ModelFormFieldData $data
     * @return string[]
     */
    protected function getMorphableModelsForFieldData(ModelFormFieldDataInterface $data)
    {
        $modelsOption = array_get($data->options(), 'models', []);

        // Users may have set the string value with the model class, instead of a class => array value pair
        $modelClasses = array_map(
            function ($key, $value) {
                if (is_string($value)) {
                    return $value;
                }

                return $key;
            },
            array_keys($modelsOption),
            array_values($modelsOption)
        );

        return array_unique($modelClasses);
    }

}
