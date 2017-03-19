<?php
namespace Czim\CmsModels\ModelInformation\Analyzer\Features;

use Czim\CmsModels\Contracts\Analyzer\ModelAnalyzerInterface;
use Czim\CmsModels\Support\Data\ModelInformation;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

class TranslationAnalyzer
{

    /**
     * @var ModelAnalyzerInterface
     */
    protected $analyzer;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var ModelInformation
     */
    protected $info;


    /**
     * @param ModelAnalyzerInterface $analyzer
     * @return $this
     */
    public function setModelAnalyzer(ModelAnalyzerInterface $analyzer)
    {
        $this->analyzer = $analyzer;

        return $this;
    }


    /**
     * Analyzes a model for its translations and returns relevant information.
     *
     * @param Model  $model
     * @param string $strategy
     * @return ModelInformation
     */
    public function analyze(Model $model, $strategy = 'translatable')
    {
        $this->model = $model;

        $this->info = new ModelInformation([]);

        switch ($strategy) {

            case 'translatable':
                $this->analyzeForTranslatable();
                break;

            default:
                // @codeCoverageIgnoreStart
                throw new UnexpectedValueException("Cannot handle translation strategy '{$strategy}");
                // @codeCoverageIgnoreEnd
        }

        return $this->info;
    }

    /**
     * Analyzes a model using the translatable trait strategy.
     */
    protected function analyzeForTranslatable()
    {
        /** @var Model|Translatable $model */
        $model = $this->model;

        $translationModel = $model->getTranslationModelName();

        $this->info = $this->analyzer->analyze($translationModel);

        $attributes = $this->info['attributes'];

        foreach ($attributes as $key => $attribute) {
            if ( ! $model->isTranslationAttribute($key)) continue;

            // mark the fields as translated for merging by model analyzer
            $attributes[$key]['translated'] = true;
        }

        $this->info['attributes'] = $attributes;
    }

}
