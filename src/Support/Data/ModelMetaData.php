<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;

/**
 * Class ModelMetaData
 *
 * Meta-information about a model in the context of the CMS.
 *
 * @property string $controller
 * @property string $default_controller_method
 * @property string[] $form_requests
 * @property string[] $views
 * @property string $transformer
 */
class ModelMetaData extends AbstractDataObject
{

    protected $attributes = [

        // FQN for the controller class to handle the model's web & API presence
        'controller' => null,
        // Default controller action to link to for the basic model's menu presence ('index', 'create', for instance)
        'default_controller_method' => 'index',

        // List of FQNs for form requests, keyed by the relevant POST controller method name ('update', 'create')
        'form_requests' => [],

        // List of (default) views to use, keyed by the controller action method name.
        'views' => [],

        // API serialization transformer class to use. Any class that implements the models module transformer interface.
        'transformer' => null,

    ];

}
