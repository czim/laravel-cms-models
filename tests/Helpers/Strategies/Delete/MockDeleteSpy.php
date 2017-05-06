<?php
namespace Czim\CmsModels\Test\Helpers\Strategies\Delete;

use Czim\CmsModels\Contracts\Strategies\DeleteStrategyInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MockDeleteSpy
 *
 * Test delete strategy spy.
 */
class MockDeleteSpy implements DeleteStrategyInterface
{

    /**
     * Deletes a model.
     *
     * @param Model $model
     * @return bool
     */
    public function delete(Model $model)
    {
        app()->instance('mock-delete-spy-triggered', true);

        return true;
    }

}
