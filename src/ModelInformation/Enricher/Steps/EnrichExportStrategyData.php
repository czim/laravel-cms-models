<?php
namespace Czim\CmsModels\ModelInformation\Enricher\Steps;

class EnrichExportStrategyData extends AbstractEnricherStep
{

    /**
     * Performs enrichment.
     */
    protected function performEnrichment()
    {
        $strategies = $this->info->export->strategies;

        foreach ($strategies as $key => $strategyData) {

            if (empty($strategyData['strategy'])) {
                $strategies[ $key ]['strategy'] = $key;
            }
        }

        $this->info->export->strategies = $strategies;
    }

}
