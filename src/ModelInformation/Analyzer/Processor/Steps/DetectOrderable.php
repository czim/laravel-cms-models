<?php
namespace Czim\CmsModels\ModelInformation\Analyzer\Processor\Steps;

use Czim\Listify\Listify;
use Illuminate\Database\Eloquent\Relations\Relation;

class DetectOrderable extends AbstractTraitAnalyzerStep
{

    /**
     * Performs the analyzer step on the stored model information instance.
     */
    protected function performStep()
    {
        if ( ! $this->modelHasTrait($this->getListifyTraits())) {
            return;
        }

        /** @var Listify $model */
        $model = $this->model();

        $this->info->list->orderable      = true;
        $this->info->list->order_strategy = 'listify';
        $this->info->list->order_column   = $model->positionColumn();


        // Determine whether an (interpretable) scope is configured
        if ( ! method_exists($model, 'getScopeName')) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        $scope = $model->getScopeName();

        if ($scope instanceof Relation) {
            // Attempt to resolve the relation method name from the relation instance
            $this->info->list->order_scope_relation = $this->getRelationNameFromRelationInstance($scope);
        }
    }

    /**
     * @return string[]
     */
    protected function getListifyTraits()
    {
        return config('cms-models.analyzer.traits.listify', []);
    }

}
