<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Strategies\DeleteStrategyInterface;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

trait DeletesModel
{

    /**
     * Deletes model.
     *
     * Note that this does not check authorization, conditions, etc.
     *
     * @param Model $model
     * @return bool
     */
    protected function deleteModel(Model $model)
    {
        $strategy = $this->getModelInformation()->deleteStrategy();

        if ( ! $strategy) {
            return $model->delete();
        }

        $strategy = $this->resolveDeleteStrategyClass($strategy);

        /** @var DeleteStrategyInterface $instance */
        $instance = app($strategy);

        return $instance->delete($model);
    }

    /**
     * Resolves strategy assuming it is the class name or FQN of a delete interface
     * implementation, or a configured alias.
     *
     * @param string $strategy
     * @return string|false     returns full class namespace if it was resolved succesfully
     */
    protected function resolveDeleteStrategyClass($strategy)
    {
        $originalStrategy = $strategy;

        if ( ! str_contains($strategy, '.')) {
            $strategy = config('cms-models.strategies.delete.aliases.' . $strategy, $strategy);
        }

        if (class_exists($strategy) && is_a($strategy, DeleteStrategyInterface::class, true)) {
            return $strategy;
        }

        $strategy = $this->prefixDeleteStrategyNamespace($strategy);

        if (class_exists($strategy) && is_a($strategy, DeleteStrategyInterface::class, true)) {
            return $strategy;
        }

        throw new UnexpectedValueException(
            "Could not resolve strategy '{$originalStrategy}' as a DeleteStrategy"
        );
    }

    /**
     * @param string $class
     * @return string
     */
    protected function prefixDeleteStrategyNamespace($class)
    {
        return rtrim(config('cms-models.strategies.delete.default-namespace'), '\\') . '\\' . $class;
    }


    /**
     * @return ModelInformationInterface
     */
    abstract protected function getModelInformation();

}
