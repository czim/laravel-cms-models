<?php
namespace Czim\CmsModels\ModelInformation\Analyzer\Processor\Steps;

use Czim\CmsModels\ModelInformation\Analyzer\Features\TranslationAnalyzer;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;

class AnalyzeTranslation extends AbstractTraitAnalyzerStep
{

    /**
     * Performs the analyzer step on the stored model information instance.
     */
    protected function performStep()
    {
        // Detect whether the model is translated; if not, skip this step
        if ( ! $this->modelHasTrait($this->getTranslatableTraits())) {
            return;
        }

        // Model is translated using translatable
        $this->info->translated           = true;
        $this->info->translation_strategy = 'translatable';

        $this->addIncludesDefault('translations');

        $this->updateAttributesWithTranslated();
    }

    /**
     * Updates model information attributes with translated attribute data.
     */
    protected function updateAttributesWithTranslated()
    {
        $translationInfo = $this->translationAnalyzer()->analyze($this->model());

        $attributes = $this->info['attributes'];

        // Mark the fillable fields on the translation model
        foreach ($translationInfo['attributes'] as $key => $attribute) {

            /** @var ModelAttributeData $attribute */
            if ( ! $attribute->translated) {
                continue;
            }

            if ( ! isset($attributes[$key])) {
                $attributes[$key] = $attribute;
            }

            /** @var ModelAttributeData $attributeData */
            $attributeData = $attributes[$key];
            $attributeData->merge($attribute);

            $attributes[$key] = $attributeData;
        }

        $this->info['attributes'] = $attributes;
    }

    /**
     * @return TranslationAnalyzer
     */
    protected function translationAnalyzer()
    {
        /** @var TranslationAnalyzer $analyzer */
        $analyzer = app(TranslationAnalyzer::class);

        $analyzer->setModelAnalyzer(clone $this->analyzer);

        return $analyzer;
    }

    /**
     * @return string[]
     */
    protected function getTranslatableTraits()
    {
        return config('cms-models.analyzer.traits.translatable', []);
    }

}
