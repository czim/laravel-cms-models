<?php
namespace Czim\CmsModels\ModelInformation\Enricher\Steps;

use Czim\CmsModels\Contracts\Data\ModelFormLayoutNodeInterface;
use Czim\CmsModels\Exceptions\ModelConfigurationDataException;
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
            $this->markRequiredForNestedLayoutChildren(null, 'layout');
        }
    }

    /**
     * Enriches existing layout data with required state for parent nodes.
     *
     * @param mixed       $nodes        an array, layout node or string field key
     * @param string|null $parentKey
     * @return bool|null true if any children are required, null if unknown
     * @throws ModelConfigurationDataException
     */
    protected function markRequiredForNestedLayoutChildren($nodes = null, $parentKey = null)
    {
        if (null === $nodes) {
            $nodes = $this->info->form->layout();
        }

        // If the data is an array walk through it and return whether any children are required
        if (is_array($nodes)) {

            $required = null;

            foreach ($nodes as $key => $value) {

                try {
                    $oneRequired = $this->markRequiredForNestedLayoutChildren($value, 'children.' . $key);

                } catch (ModelConfigurationDataException $e) {

                    throw $e->setDotKey(
                        $parentKey . ($e->getDotKey() ? '.' . $e->getDotKey() : null)
                    );
                }

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

            $required = $this->markRequiredForNestedLayoutChildren($nodes->children(), $parentKey);

            // Only set the required status if not explicitly set in configuration
            if (null === $nodes->required) {
                $nodes->required = $required;
            }

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
