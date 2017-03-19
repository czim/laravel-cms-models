<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data\Form;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelFormValidationDataInterface extends ArrayAccess, Arrayable
{

    /**
     * Returns default or create specific rules.
     *
     * @return array
     */
    public function create();

    /**
     * Returns update specific rules.
     *
     * @return array
     */
    public function update();

    /**
     * @param ModelFormValidationDataInterface $with
     */
    public function merge(ModelFormValidationDataInterface $with);

}
