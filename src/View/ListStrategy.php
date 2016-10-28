<?php
namespace Czim\CmsModels\View;

use Czim\CmsModels\Contracts\Data\ModelAttributeDataInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\View\ListDisplayInterface;
use Czim\CmsModels\Contracts\View\ListStrategyInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\View\Traits\ResolvesSourceStrategies;
use Illuminate\Database\Eloquent\Model;

class ListStrategy implements ListStrategyInterface
{
    use ResolvesSourceStrategies;

    /**
     * True if strategy resolution fell back to the default strategy.
     *
     * @var bool
     */
    protected $fellBackToDefault = false;


    /**
     * Applies a strategy to render a list value from its source.
     *
     * @param Model  $model
     * @param string $strategy
     * @param string $source
     * @return string
     */
    public function render(Model $model, $strategy, $source)
    {
        $strategyInstance = $this->displayStrategy($strategy);

        return $strategyInstance->render($model, $source);
    }

    /**
     * Returns an optional style string for the list display value container.
     *
     * @param Model  $model
     * @param string $strategy
     * @param string $source    source column, method name or value (unresolved)
     * @return null|string  html element class string
     */
    public function style(Model $model, $strategy, $source)
    {
        return $this->displayStrategy($strategy, $source, $model)->style($model, $source);
    }

    /**
     * Returns an optional set of attribute values to merge into the list display value container.
     *
     * @param Model  $model
     * @param string $strategy
     * @param string $source source column, method name or value
     * @return array associative, key value pairs with html tag attributes
     */
    public function attributes(Model $model, $strategy, $source)
    {
        return $this->displayStrategy($strategy, $source, $model)->attributes($model, $source);
    }

    /**
     * Returns a display strategy instance for a given strategy string.
     *
     * @param string      $strategy
     * @param null|string $source   the source (attribute key), optional
     * @param null|Model  $model
     * @return ListDisplayInterface
     */
    public function displayStrategy($strategy, $source = null, Model $model = null)
    {
        $instance = $this->makeListDisplayStrategyInstance($strategy);

        // Feed any extra information we can gather to the instance
        if ($source && $model) {
            $attributeData = $this->getAttributeData($model, $source);

            if ($attributeData) {
                $instance->setAttributeInformation($attributeData);
            }
        }

        return $instance;
    }

    /**
     * Makes a list display strategy instance for a given strategy string.
     *
     * @param string $strategy
     * @return ListDisplayInterface
     */
    protected function makeListDisplayStrategyInstance($strategy)
    {
        // If the strategy indicates the FQN of display strategy,
        // or a classname that can be found in the default strategy name path, use it.
        if ($strategyClass = $this->resolveStrategyClass($strategy)) {

            $this->fellBackToDefault = false;

            return app($strategyClass);
        }

        $this->fellBackToDefault = true;

        return $this->getDefaultStrategy();
    }

    /**
     * Returns model attribute data, if possible.
     *
     * @param Model  $model
     * @param string $source
     * @return bool|ModelAttributeDataInterface|ModelAttributeData
     */
    protected function getAttributeData(Model $model, $source)
    {
        $information = $this->getInformationRepository()->getByModel($model);

        if ( ! $information) return false;

        if (isset($information->attributes[ $source ])) {
            return $information->attributes[ $source ];
        }

        return false;
    }

    /**
     * Resolves strategy assuming it is the class name or FQN of a list display interface
     * implementation or an alias for one.
     *
     * @param $strategy
     * @return string|false     returns full class namespace if it was resolved succesfully
     */
    protected function resolveStrategyClass($strategy)
    {
        if ( ! str_contains($strategy, '.')) {
            $strategy = config('cms-models.strategies.list.aliases.' . $strategy, $strategy);
        }

        if (class_exists($strategy) && is_a($strategy, ListDisplayInterface::class, true)) {
            return $strategy;
        }

        $strategy = $this->prefixStrategyNamespace($strategy);

        if (class_exists($strategy) && is_a($strategy, ListDisplayInterface::class, true)) {
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
        return rtrim(config('cms-models.strategies.list.default-namespace'), '\\') . '\\' . $class;
    }

    /**
     * @return ListDisplayInterface
     */
    protected function getDefaultStrategy()
    {
        return app(config('cms-models.strategies.list.default-strategy'));
    }

    /**
     * @return ModelInformationRepositoryInterface
     */
    protected function getInformationRepository()
    {
        return app(ModelInformationRepositoryInterface::class);
    }

}
