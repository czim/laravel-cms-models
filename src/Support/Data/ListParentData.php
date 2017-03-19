<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ListParentData
 *
 * Container for data about parents in the list parent hierarchy.
 *
 * @property string                                     $relation
 * @property mixed                                      $key
 * @property Model                                      $model
 * @property ModelInformationInterface|ModelInformation $information
 * @property string                                     $module_key
 * @property string                                     $route_prefix
 * @property string                                     $permission_prefix
 * @property string                                     $query                  the query string to add to the route/url
 */
class ListParentData extends AbstractDataObject
{

    protected $attributes = [
        'relation'          => null,
        'key'               => null,
        'model'             => null,
        'information'       => null,
        'module_key'        => null,
        'route_prefix'      => null,
        'permission_prefix' => null,
        'query'             => null,
    ];

}
