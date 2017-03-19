<?php
namespace Czim\CmsModels\ModelInformation\Analyzer\Processor;

use Czim\CmsModels\Contracts\ModelInformation\Analyzer\AnalyzerStepInterface;
use Czim\CmsModels\Contracts\ModelInformation\Analyzer\ModelAnalyzerInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use UnexpectedValueException;

class ModelAnalyzer implements ModelAnalyzerInterface
{

    /**
     * An instance of the model being analyzed.
     *
     * @var null|Model
     */
    protected $modelInstance;

    /**
     * The reflection of the model being analyzed.
     *
     * @var null|ReflectionClass
     */
    protected $modelReflection;

    /**
     * Analyzes a model and returns normalized information about it.
     *
     * @param string $modelClass FQN of model to analyze
     * @return ModelInformationInterface
     */
    public function analyze($modelClass)
    {
        $information = new ModelInformation;

        $information->model          = $modelClass;
        $information->original_model = $modelClass;

        $this->makeModelInstance($modelClass);

        foreach ($this->getSteps() as $stepClass) {

            /** @var AnalyzerStepInterface $step */
            $step = app($stepClass);

            $information = $step->setAnalyzer($this)->analyze($information);
        }

        return $information;
    }

    /**
     * Makes and sets instance of the model class and stores it.
     *
     * @param string $class
     * @return $this
     */
    protected function makeModelInstance($class)
    {
        if ( ! class_exists($class)) {
            throw new UnexpectedValueException("Class '{$class}' does not exist");
        }

        $instance = new $class;

        if ( ! $instance instanceof Model) {
            throw new UnexpectedValueException("Instance of '{$class}' is not a model");
        }

        $this->modelInstance = $instance;

        $this->modelReflection = new ReflectionClass($class);

        return $this;
    }

    /**
     * Returns a list of FQNs for analysis steps to be performed.
     *
     * @return string[]
     */
    protected function getSteps()
    {
        return config('cms-models.analyzer.steps', []);
    }

    /**
     * Returns instance of the model being analyzed.
     *
     * @return Model
     */
    public function model()
    {
        return $this->modelInstance;
    }

    /**
     * Returns reflection of the class being analyzed.
     *
     * @return ReflectionClass
     */
    public function reflection()
    {
        return $this->modelReflection;
    }

}
