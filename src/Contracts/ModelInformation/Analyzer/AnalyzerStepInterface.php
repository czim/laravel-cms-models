<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Analyzer;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;

interface AnalyzerStepInterface
{

    /**
     * Sets the parent model analyzer processor.
     *
     * @param ModelAnalyzerInterface $analyzer
     * @return $this
     */
    public function setAnalyzer(ModelAnalyzerInterface $analyzer);

    /**
     * Performs analysis.
     *
     * @param ModelInformationInterface $information
     * @return ModelInformationInterface
     */
    public function analyze(ModelInformationInterface $information);

}
