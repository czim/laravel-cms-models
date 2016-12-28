<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ListParentData
 *
 * Container for data about parents in the list parent hierarchy.
 *
 * @property Model                                      $model
 * @property ModelInformationInterface|ModelInformation $information
 */
class ListParentData extends AbstractDataObject
{

    protected $attributes = [
        'model'       => null,
        'information' => null,
    ];

}
