<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Analyzer;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

interface ModelAnalyzerInterface
{

    /**
     * Analyzes a model and returns normalized information about it.
     *
     * @param string $modelClass    FQN of model to analyze
     * @return ModelInformationInterface
     */
    public function analyze($modelClass);

    /**
     * Returns instance of the model being analyzed.
     *
     * @return Model
     */
    public function model();

    /**
     * Returns reflection of the class being analyzed.
     *
     * @return ReflectionClass
     */
    public function reflection();
}
