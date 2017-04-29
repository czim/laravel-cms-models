<?php
namespace Czim\CmsModels\Test\Helpers\Strategies\DeleteCondition;

use Czim\CmsModels\Contracts\Strategies\DeleteConditionStrategyInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OnlyIfIdIsTwo
 *
 * Test condition that only allows deletion for record #2.
 */
class OnlyIfIdIsTwo implements DeleteConditionStrategyInterface
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
        return $model->getKey() === 2;
    }

    /**
     * Returns a failure message that may be displayed when the check fails.
     *
     * @return string
     */
    public function message()
    {
        return 'ID is not #2';
    }
}
