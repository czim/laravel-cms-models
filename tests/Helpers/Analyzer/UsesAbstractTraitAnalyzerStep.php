<?php
namespace Czim\CmsModels\Test\Helpers\Analyzer;

use Czim\CmsModels\Analyzer\Processor\Steps\AbstractTraitAnalyzerStep;

class UsesAbstractTraitAnalyzerStep extends AbstractTraitAnalyzerStep
{

    /**
     * Performs the analyzer step on the stored model information instance.
     */
    protected function performStep()
    {
    }


    /**
     * Passthru for testing abstract.
     *
     * @param $names
     * @return bool
     */
    public function publicModelHasTrait($names)
    {
        return parent::modelHasTrait($names);
    }

    /**
     * Passthru for testing abstract.
     *
     * @return array
     */
    public function publicGetTraitNames()
    {
        return parent::getTraitNames();
    }

}
