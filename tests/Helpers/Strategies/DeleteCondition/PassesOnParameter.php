<?php
namespace Czim\CmsModels\Test\Helpers\Strategies\DeleteCondition;

use Czim\CmsModels\Contracts\Strategies\DeleteConditionStrategyInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PassesOnParameter
 *
 * Test condition that always allows deletion.
 */
class PassesOnParameter implements DeleteConditionStrategyInterface
{

    /**
     * Returns whether deletion is allowed.
     *
     * @param Model $model
     * @param array $parameters strategy-dependent parameters
     * @return bool
     */
    public function check(Model $model, array $parameters = [])
    {
        return (bool) head($parameters);
    }

    /**
     * Returns a failure message that may be displayed when the check fails.
     *
     * @return string
     */
    public function message()
    {
        return 'Parameter does not evaluate as boolean true';
    }
}
