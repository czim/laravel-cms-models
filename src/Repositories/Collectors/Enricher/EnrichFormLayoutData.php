<?php
namespace Czim\CmsModels\Repositories\Collectors\Enricher;

use Czim\CmsModels\Contracts\Data\ModelFormLayoutNodeInterface;
use Czim\CmsModels\Support\Data\ModelFormFieldGroupData;
use Czim\CmsModels\Support\Data\ModelFormFieldLabelData;
use Czim\CmsModels\Support\Data\ModelFormFieldsetData;
use Czim\CmsModels\Support\Data\ModelFormTabData;

class EnrichFormLayoutData extends AbstractEnricherStep
{

    /**
     * Performs enrichment.
     */
    protected function performEnrichment()
    {
        if ($this->info->form->layout && count($this->info->form->layout)) {
            $this->markRequiredForNestedLayoutChildren();
        }
    }

    /**
     * Enriches existing layout data with required state for parent nodes.
     *
     * @param mixed $nodes  an array, layout node or string field key
     * @return bool|null    true if any children are required, null if unknown
     */
    protected function markRequiredForNestedLayoutChildren($nodes = null)
    {
        if (null === $nodes) {
            $nodes = $this->info->form->layout();
        }

        // If the data is an array walk through it and return whether any children are required
        if (is_array($nodes)) {

            $required = null;

            foreach ($nodes as $key => $value) {

                $oneRequired = $this->markRequiredForNestedLayoutChildren($value);

                if ( ! $required && $oneRequired) {
                    $required = true;
                }
            }

            return $required;
        }

        if ($nodes instanceof ModelFormLayoutNodeInterface) {

            if ($nodes instanceof ModelFormFieldLabelData) {
                return false;
            }

            /** @var ModelFormTabData|ModelFormFieldsetData|ModelFormFieldGroupData $nodes */
            $nodes->required = $this->markRequiredForNestedLayoutChildren($nodes->children());

            return $nodes->required;
        }

        // If the node is a string, it's a field key
        if (is_string($nodes)) {

            if (array_key_exists($nodes, $this->info->form->fields)) {
                return $this->info->form->fields[ $nodes ]->required();
            }

            return false;
        }

        return null;
    }

}
