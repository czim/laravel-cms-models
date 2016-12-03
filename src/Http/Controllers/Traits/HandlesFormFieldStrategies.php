<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\Data\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\Support\Factories\FormFieldStrategyFactoryInterface;
use Czim\CmsModels\Contracts\View\FormFieldDisplayInterface;
use Czim\CmsModels\Support\Data\ModelFormFieldData;

trait HandlesFormFieldStrategies
{

    /**
     * Returns instances of form field strategies.
     *
     * @param ModelFormFieldData[]|ModelFormFieldDataInterface[] $fields    the form field keys to get
     * @return FormFieldDisplayInterface[]
     */
    protected function getFormFieldStrategyInstances(array $fields)
    {
        $instances = [];

        foreach ($fields as $key => $data) {

            $instances[ $key ] = $this->getFormFieldStrategyFactory()->make($data->display_strategy);
        }

        return $instances;
    }

    /**
     * @return FormFieldStrategyFactoryInterface
     */
    protected function getFormFieldStrategyFactory()
    {
        return app(FormFieldStrategyFactoryInterface::class);
    }

}
