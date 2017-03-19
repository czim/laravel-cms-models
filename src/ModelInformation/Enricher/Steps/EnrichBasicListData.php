<?php
namespace Czim\CmsModels\ModelInformation\Enricher\Steps;

class EnrichBasicListData extends AbstractEnricherStep
{

    /**
     * Performs enrichment.
     */
    protected function performEnrichment()
    {
        $this->setReferenceSource()
             ->setSortingOrder();
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

    /**
     * Sets default reference source, better than primary key, if possible.
     *
     * @return $this
     */
    protected function setReferenceSource()
    {
        if (null !== $this->info->reference->source) {
            return $this;
        }

        // No source is set, see if we can find a standard match
        $matchAttributes = config('cms-models.analyzer.reference.sources', []);

        if ( ! count($matchAttributes)) {
            return $this;
        }

        foreach ($matchAttributes as $matchAttribute) {

            if (array_key_exists($matchAttribute, $this->info->attributes)) {
                $this->info->reference->source = $matchAttribute;
                break;
            }
        }

        return $this;
    }

}
