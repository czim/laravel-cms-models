<?php
namespace Czim\CmsModels\Repositories\Collectors\Enricher;

class EnrichBasicListData extends AbstractEnricherStep
{

    /**
     * Performs enrichment.
     */
    protected function performEnrichment()
    {
        $this->setSortingOrder();
    }

    /**
     * @return $this
     */
    protected function setSortingOrder()
    {
        // Default sorting order
        if ($this->info->timestamps) {
            $this->info->list->default_sort = $this->info->timestamp_created;
        } elseif ($this->info->incrementing) {
            $this->info->list->default_sort = $this->model->getKeyName();
        }

        return $this;
    }

}
