<?php
namespace Czim\CmsModels\Strategies\DeleteCondition;

/**
 * Class NotRelated
 *
 * Condition met only if the model is not currently connected to other
 * models through (relevant) relations.
 *
 * Parameters may be set and may be any number of relation method names.
 * If any of these relations have connections to this model, deletion
 * should be disallowed.
 *
 * @todo handle global scopes, or determine what model is on the
 *       other end of the relation and determine scope handling
 *       that way? Not sure yet, might not always make sense.
 */
class NotRelated extends AbstractDeleteConditionStrategy
{

    /**
     * Returns whether deletion is allowed.
     *
     * @return bool
     */
    protected function performCheck()
    {
        $relations = $this->determineRelations();

        if ( ! count($relations)) {
            return true;
        }

        foreach ($relations as $relation) {

            if ($this->model->{$relation}()->count()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns a failure message that may be displayed when the check fails.
     *
     * @return string
     */
    public function message()
    {
        return cms_trans('models.delete.failure.in-use');
    }

    /**
     * Determines and returns relation method names that should be checked.
     *
     * @return string[]
     */
    protected function determineRelations()
    {
        if (count($this->parameters)) {
            return $this->parameters;
        }

        if ( ! ($info = $this->getModelInformation())) {
            return [];
        }

        return array_pluck($info->relations, 'method');
    }

}
