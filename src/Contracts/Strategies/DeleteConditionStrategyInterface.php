<?php
namespace Czim\CmsModels\Contracts\Strategies;

use Illuminate\Database\Eloquent\Model;

interface DeleteConditionStrategyInterface
{

    /**
     * Returns whether deletion is allowed.
     *
     * @param Model $model
     * @param array $parameters     strategy-dependent parameters
     * @return bool
     */
    public function check(Model $model, array $parameters = []);

    /**
     * Returns a failure message that may be displayed when the check fails.
     *
     * @return string
     */
    public function message();

}
