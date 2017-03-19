<?php
namespace Czim\CmsModels\Strategies\Form\Display;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Support\Factories\ListDisplayStrategyFactoryInterface;
use Czim\CmsModels\Http\Controllers\Traits\HandlesListColumnStrategies;
use Czim\CmsModels\Support\Data\ModelInformation;

class StaticStrategy extends AbstractDefaultStrategy
{
    use HandlesListColumnStrategies;

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.static';
    }

    /**
     * Enriches field data before passing it on to the view.
     *
     * @param array $data
     * @return array
     */
    protected function decorateFieldData(array $data)
    {
        $strategy = array_get($this->field->options, 'strategy');

        $data['displayStrategy'] = null;
        $data['displaySource']  = $this->field->source() ?: $this->field->key();

        if ($strategy) {

            $instance = $this->getListDisplayFactory()->make($strategy);

            // todo: resolve attribute information if we can?
            //$instance->setAttributeInformation();
            $instance->setOptions(array_get($this->field->options, 'strategy_options', []));

            $data['displayStrategy'] = $instance;
            $data['displaySource'] = array_get($this->field->options, 'strategy_source', $data['displaySource']);
        }

        return $data;
    }

    /**
     * @return ModelInformation|ModelInformationInterface
     * @see BaseModelController::getModelInformation()
     */
    protected function getModelInformation()
    {

    }

    /**
     * @return ListDisplayStrategyFactoryInterface
     */
    protected function getListDisplayFactory()
    {
        return app(ListDisplayStrategyFactoryInterface::class);
    }

}
