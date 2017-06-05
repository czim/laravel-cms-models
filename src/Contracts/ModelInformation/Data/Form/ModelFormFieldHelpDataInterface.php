<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data\Form;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelFormFieldHelpDataInterface extends ArrayAccess, Arrayable
{

    /**
     * @param ModelFormFieldHelpDataInterface $with
     */
    public function merge(ModelFormFieldHelpDataInterface $with);

}
