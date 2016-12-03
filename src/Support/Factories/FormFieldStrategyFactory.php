<?php
namespace Czim\CmsModels\Support\Factories;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Support\Factories\FormFieldStrategyFactoryInterface;
use Czim\CmsModels\Contracts\View\FormFieldDisplayInterface;

class FormFieldStrategyFactory implements FormFieldStrategyFactoryInterface
{

    /**
     * Makes a form field display strategy instance.
     *
     * @param string $strategy
     * @return FormFieldDisplayInterface
     */
    public function make($strategy)
    {
        // If the strategy indicates the FQN of display strategy,
        // or a classname that can be found in the default strategy name path, use it.
        if ($strategyClass = $this->resolveStrategyClass($strategy)) {

            return app($strategyClass);
        }

        return $this->getDefaultStrategy();
    }

    /**
     * Resolves strategy assuming it is the class name or FQN of a form field display interface
     * implementation or an alias for one.
     *
     * @param $strategy
     * @return string|false     returns full class namespace if it was resolved succesfully
     */
    protected function resolveStrategyClass($strategy)
    {
        if ( ! str_contains($strategy, '.')) {
            $strategy = config('cms-models.strategies.form.aliases.' . $strategy, $strategy);
        }

        if (class_exists($strategy) && is_a($strategy, FormFieldDisplayInterface::class, true)) {
            return $strategy;
        }

        $strategy = $this->prefixStrategyNamespace($strategy);

        if (class_exists($strategy) && is_a($strategy, FormFieldDisplayInterface::class, true)) {
            return $strategy;
        }

        return false;
    }

    /**
     * @param string $class
     * @return string
     */
    protected function prefixStrategyNamespace($class)
    {
        return rtrim(config('cms-models.strategies.form.default-namespace'), '\\') . '\\' . $class;
    }

    /**
     * @return FormFieldDisplayInterface
     */
    protected function getDefaultStrategy()
    {
        return app(config('cms-models.strategies.form.default-strategy'));
    }

    /**
     * @return ModelInformationRepositoryInterface
     */
    protected function getInformationRepository()
    {
        return app(ModelInformationRepositoryInterface::class);
    }

}
