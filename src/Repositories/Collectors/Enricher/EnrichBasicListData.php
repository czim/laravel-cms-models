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
     * Sets default sorting order, if empty.
     *
     * @return $this
     */
    protected function setSortingOrder()
    {
        if (null !== $this->info->list->default_sort) {
            return $this;
        }

        if ($this->info->list->orderable && $this->info->list->getOrderableColumn()) {
            $this->info->list->default_sort = $this->info->list->getOrderableColumn();
        } elseif ($this->info->timestamps) {
            $this->info->list->default_sort = $this->info->timestamp_created;
        } elseif ($this->info->incrementing) {
            $this->info->list->default_sort = $this->model->getKeyName();
        }

        return $this;
    }

}
