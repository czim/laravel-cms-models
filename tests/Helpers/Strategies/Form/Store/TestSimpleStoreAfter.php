<?php
namespace Czim\CmsModels\Test\Helpers\Strategies\Form\Store;

use Czim\CmsModels\Contracts\Strategies\FormFieldStoreStrategyInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Illuminate\Database\Eloquent\Model;

class TestSimpleStoreAfter implements FormFieldStoreStrategyInterface
{


    /**
     * @param string $key
     * @return mixed|null
     */
    public function valueForListParentKey($key)
    {
        return null;
    }

    /**
     * Sets the relevant form field data to provide a context.
     *
     * @param ModelFormFieldDataInterface $data
     * @return $this
     */
    public function setFormFieldData(ModelFormFieldDataInterface $data)
    {
        return $this;
    }

    /**
     * Sets parameters to use for retrieving & storing.
     *
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        return $this;
    }

    /**
     * Retrieves current values from a model
     *
     * @param Model $model
     * @param mixed $source
     * @return mixed
     */
    public function retrieve(Model $model, $source)
    {
        return $model->{$source};
    }

    /**
     * Stores a submitted value on a model
     *
     * @param Model $model
     * @param mixed $source
     * @param mixed $value
     */
    public function store(Model $model, $source, $value)
    {
    }

    /**
     * Stores a submitted value on a model, after it has been created (or saved).
     *
     * @param Model $model
     * @param mixed $source
     * @param mixed $value
     */
    public function storeAfter(Model $model, $source, $value)
    {
        $model->{$source} = $value;
    }

    /**
     * Performs finalizing/cleanup handling.
     */
    public function finish()
    {
    }

    /**
     * @param ModelInformationInterface|null $modelInformation
     * @param bool                           $create whether the rules are for creating a new record
     * @return array|false false if no validation should be performed.
     */
    public function validationRules(ModelInformationInterface $modelInformation = null, $create)
    {
        return false;
    }
}
