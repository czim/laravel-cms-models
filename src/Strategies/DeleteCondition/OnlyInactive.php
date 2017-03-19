<?php
namespace Czim\CmsModels\Strategies\DeleteCondition;

/**
 * Class OnlyInactive
 *
 * Condition met only if the model is inactive. If it is an activatable model
 * and no column parameter is given, uses configured activatable column.
 *
 * Parameters:
 *  -   column to check
 */
class OnlyInactive extends AbstractDeleteConditionStrategy
{

    /**
     * Returns whether deletion is allowed.
     *
     * @return bool
     */
    protected function performCheck()
    {
        // Parameter overrules automatic detection
        $column = head($this->parameters) ?: $this->determineActiveColumn();

        return ! $this->model->{$column};
    }

    /**
     * Returns a failure message that may be displayed when the check fails.
     *
     * @return string
     */
    public function message()
    {
        return cms_trans('models.delete.failure.is-active');
    }

    /**
     * Determines and returns name of active column.
     *
     * @return string
     */
    protected function determineActiveColumn()
    {
        $info = $this->getModelInformation();

        if ($info && $info->list->activatable && $info->list->active_column) {
            return $info->list->active_column;
        }

        return 'active';
    }

}
