<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\Support\Factories\FormFieldStrategyFactoryInterface;
use Czim\CmsModels\Exceptions\StrategyRenderException;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

trait HandlesFormFieldStrategies
{

    /**
     * Renders Vies or HTML for form field strategies.
     *
     * @param Model $model
     * @param array $fields
     * @param array $values
     * @param array $errors
     * @return View[]|\string[]
     * @throws StrategyRenderException
     */
    protected function renderedFormFieldStrategies(Model $model, array $fields, array $values, array $errors = [])
    {
        $views = [];

        foreach ($fields as $key => $field) {

            try {
                $instance = $this->getFormFieldStrategyFactory()->make($field->display_strategy);

                // @codeCoverageIgnoreStart
            } catch (Exception $e) {
                $message = "Failed to make form field strategy for '{$key}': \n{$e->getMessage()}";

                throw new StrategyRenderException($message, $e->getCode(), $e);
                // @codeCoverageIgnoreEnd
            }

            try {
                $views[ $key ] = $instance->render(
                    $model,
                    $fields[ $key ],
                    old() ? old($key) : array_get($values, $key),
                    array_get($values, $key),
                    array_get($errors, $key, [])
                );

                // @codeCoverageIgnoreStart
            } catch (Exception $e) {
                $message = "Failed to render form field '{$key}' for strategy " . get_class($instance)
                         . ": \n{$e->getMessage()}";

                throw new StrategyRenderException($message, $e->getCode(), $e);
                // @codeCoverageIgnoreEnd
            }
        }

        return $views;
    }

    /**
     * @return FormFieldStrategyFactoryInterface
     */
    protected function getFormFieldStrategyFactory()
    {
        return app(FormFieldStrategyFactoryInterface::class);
    }

}
