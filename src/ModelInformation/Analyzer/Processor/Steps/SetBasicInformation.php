<?php
namespace Czim\CmsModels\ModelInformation\Analyzer\Processor\Steps;

class SetBasicInformation extends AbstractAnalyzerStep
{

    /**
     * Performs the analyzer step on the stored model information instance.
     */
    protected function performStep()
    {
        $this->info['verbose_name']           = strtolower(snake_case(class_basename($this->model()), ' '));
        $this->info['verbose_name_plural']    = str_plural($this->info['verbose_name']);
        $this->info['translated_name']        = 'models.name.' . $this->info['verbose_name'];
        $this->info['translated_name_plural'] = 'models.name.' . $this->info['verbose_name_plural'];

        $this->info['incrementing'] = $this->model()->getIncrementing();

        $this->info['timestamps']        = $this->model()->usesTimestamps();
        $this->info['timestamp_created'] = $this->model()->getCreatedAtColumn();
        $this->info['timestamp_updated'] = $this->model()->getUpdatedAtColumn();
    }

}
