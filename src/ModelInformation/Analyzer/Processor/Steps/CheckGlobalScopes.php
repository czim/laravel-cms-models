<?php
namespace Czim\CmsModels\ModelInformation\Analyzer\Processor\Steps;

/**
 * Class CheckGlobalScopes
 *
 * If the model has global scopes, the default CMS settings is to disable all of them.
 */
class CheckGlobalScopes extends AbstractAnalyzerStep
{

    /**
     * Performs the analyzer step on the stored model information instance.
     */
    protected function performStep()
    {
        if (count($this->model()->getGlobalScopes())) {
            $this->info->meta->disable_global_scopes = true;
        }
    }

}
