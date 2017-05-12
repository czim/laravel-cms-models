<?php
namespace Czim\CmsModels\Contracts\Support\Form;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Illuminate\Database\Eloquent\Model;

interface FormDataStorerInterface
{

    /**
     * @param ModelInformationInterface $information
     * @return $this
     */
    public function setModelInformation(ModelInformationInterface $information);

    /**
     * Stores submitted form field data on a model.
     *
     * @param Model $model
     * @param array $data
     * @return bool
     */
    public function store(Model $model, array $data);

}
