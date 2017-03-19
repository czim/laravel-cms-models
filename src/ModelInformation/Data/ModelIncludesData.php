<?php
namespace Czim\CmsModels\ModelInformation\Data;

/**
 * Class ModelIncludesData
 *
 * Information about include rules for the model.
 *
 * @property string $controller
 * @property string $default_controller_method
 * @property string[] $form_requests
 * @property string[] $views
 * @property string $transformer
 */
class ModelIncludesData extends AbstractModelInformationDataObject
{

    protected $attributes = [

        // List of default includes to use for loading a model
        'default' => [],

        // List of available includes to allow (either relation name string, or relation name key string => callable strategy)
        'available' => [],

    ];

    protected $known = [
        'default',
        'available',
    ];

}
