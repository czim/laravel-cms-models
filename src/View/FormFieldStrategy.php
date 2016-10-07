<?php
namespace Czim\CmsModels\View;

use Czim\CmsModels\Contracts\Data\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\View\FormFieldDisplayInterface;
use Czim\CmsModels\Contracts\View\FormFieldStrategyInterface;
use Czim\CmsModels\Support\Data\ModelFormFieldData;
use Illuminate\Database\Eloquent\Model;

class FormFieldStrategy implements FormFieldStrategyInterface
{

    /**
     * Applies a strategy to render a form field.
     *
     * @param Model                                          $model
     * @param ModelFormFieldDataInterface|ModelFormFieldData $field
     * @param mixed                                          $value
     * @param array                                          $errors
     * @return string
     */
    public function render(Model $model, ModelFormFieldDataInterface $field, $value, array $errors = [])
    {
        $instance = $this->makeFormFieldDisplayStrategyInstance($field->display_strategy);

        return $instance->render($model, $field, $value, $errors);
    }

    /**
     * Makes a form field display strategy instance for a given strategy string.
     *
     * @param string $strategy
     * @return FormFieldDisplayInterface
     */
    protected function makeFormFieldDisplayStrategyInstance($strategy)
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
