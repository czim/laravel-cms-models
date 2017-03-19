<?php
namespace Czim\CmsModels\ModelInformation\Analyzer\Processor\Steps;

class DetectActivatable extends AbstractAnalyzerStep
{

    /**
     * Performs the analyzer step on the stored model information instance.
     */
    protected function performStep()
    {
        $activeColumn = $this->getActivateColumnName();

        foreach ($this->info->attributes as $name => $attribute) {

            if ($name !== $activeColumn || ! $this->isAttributeBoolean($attribute)) {
                continue;
            }

            $this->info->list->activatable   = true;
            $this->info->list->active_column = $name;
        }
    }

    /**
     * @return string
     */
    protected function getActivateColumnName()
    {
        return 'active';
    }

}
