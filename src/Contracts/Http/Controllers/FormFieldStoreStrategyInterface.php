<?php
namespace Czim\CmsModels\Contracts\Http\Controllers;

use Illuminate\Database\Eloquent\Model;

interface FormFieldStoreStrategyInterface
{

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

}
