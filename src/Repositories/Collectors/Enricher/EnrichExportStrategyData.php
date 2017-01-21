<?php
namespace Czim\CmsModels\Repositories\Collectors\Enricher;

class EnrichExportStrategyData extends AbstractEnricherStep
{

    /**
     * Performs enrichment.
     */
    protected function performEnrichment()
    {
        if ( ! count($this->info->export->strategies)) {
            return;
        }

        $strategies = $this->info->export->strategies;

        foreach ($strategies as $key => $strategyData) {

            if (empty($strategyData['strategy'])) {
                $strategies[ $key ]['strategy'] = $key;
            }
        }

        $this->info->export->strategies = $strategies;
    }

}
