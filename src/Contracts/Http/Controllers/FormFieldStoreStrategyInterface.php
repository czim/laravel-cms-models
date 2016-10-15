<?php
namespace Czim\CmsModels\Contracts\Http\Controllers;

use Illuminate\Database\Eloquent\Model;

interface FormFieldStoreStrategyInterface
{

    /**
     * Sets parameters to use for retrieving & storing.
     *
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters);

    /**
     * Retrieves current values from a model
     *
     * @param Model $model
     * @param mixed $source
     * @return mixed
     */
    public function retrieve(Model $model, $source);

    /**
     * Stores a submitted value on a model
     *
     * @param Model $model
     * @param mixed $source
     * @param mixed $value
     */
    public function store(Model $model, $source, $value);

    /**
     * Stores a submitted value on a model, after it has been created (or saved).
     *
     * @param Model $model
     * @param mixed $source
     * @param mixed $value
     */
    public function storeAfter(Model $model, $source, $value);

}
