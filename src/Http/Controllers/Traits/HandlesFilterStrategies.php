<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Support\Factories\FilterStrategyFactoryInterface;
use Czim\CmsModels\Exceptions\StrategyRenderException;
use Czim\CmsModels\Http\Controllers\BaseModelController;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Illuminate\Contracts\View\View;

trait HandlesFilterStrategies
{

    /**
     * Renders Vies or HTML for list filter strategies.
     *
     * @param array $values     active filter values
     * @return View[]|\string[]
     * @throws StrategyRenderException
     */
    protected function renderedListFilterStrategies(array $values)
    {
        if ($this->getModelInformation()->list->disable_filters) {
            return [];
        }

        $views = [];

        foreach ($this->getModelInformation()->list->filters as $key => $data) {

            try {
                $instance = $this->getFilterFactory()->make($data->strategy, $key, $data);

            } catch (\Exception $e) {

                $message = "Failed to make list filter strategy for '{$key}': \n{$e->getMessage()}";

                throw new StrategyRenderException($message, $e->getCode(), $e);
            }

            try {
                $views[ $key ] = $instance->render($key, array_get($values, $key));

            } catch (\Exception $e) {

                $message = "Failed to render list filter '{$key}' for strategy " . get_class($instance)
                         . ": \n{$e->getMessage()}";

                throw new StrategyRenderException($message, $e->getCode(), $e);
            }
        }

        return $views;
    }


    /**
     * @return ModelInformation|ModelInformationInterface
     * @see BaseModelController::getModelInformation()
     */
    abstract protected function getModelInformation();

    /**
     * @return FilterStrategyFactoryInterface
     */
    protected function getFilterFactory()
    {
        return app(FilterStrategyFactoryInterface::class);
    }

}
