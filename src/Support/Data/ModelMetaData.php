<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsModels\Contracts\Data\ModelMetaDataInterface;

/**
 * Class ModelMetaData
 *
 * Meta-information about a model in the context of the CMS.
 *
 * @property string      $controller
 * @property string      $default_controller_method
 * @property string      $controller_api
 * @property string      $repository_strategy
 * @property array       $repository_strategy_parameters
 * @property string|bool $disable_global_scopes
 * @property string[]    $form_requests
 * @property string[]    $views
 * @property string      $transformer
 */
class ModelMetaData extends AbstractModelInformationDataObject implements ModelMetaDataInterface
{

    protected $attributes = [

        // FQN for the controller class to handle the model's web & API presence
        'controller' => null,
        // Default controller action to link to for the basic model's menu presence ('index', 'create', for instance)
        'default_controller_method' => 'index',

        // FQN for the controller class to handle the model's API presence
        'controller_api' => null,

        // The strategy to apply to the base repository query for listings & accessibility of models.
        'repository_strategy' => null,

        // Optional parameters to pass along to the repository strategy instance.
        'repository_strategy_parameters' => [],

        // Whether to disable all global scopes (boolean true) or a string with comma-separated global scopes to disable
        'disable_global_scopes' => null,

        // List of FQNs for form requests, keyed by the relevant POST controller method name ('update', 'create')
        'form_requests' => [],

        // List of (default) views to use, keyed by the controller action method name.
        'views' => [],

        // API serialization transformer class to use. Any class that implements the models module transformer interface.
        'transformer' => null,
    ];


    /**
     * @param ModelMetaDataInterface|ModelMetaData $with
     */
    public function merge(ModelMetaDataInterface $with)
    {
        $this->mergeAttribute('controller', $with->controller);
        $this->mergeAttribute('default_controller_method', $with->default_controller_method);
        $this->mergeAttribute('transformer', $with->transformer);
        $this->mergeAttribute('repository_strategy', $with->repository_strategy);
        $this->mergeAttribute('disable_global_scopes', $with->disable_global_scopes);

        if ( ! empty($with->repository_strategy_parameters)) {
            $this->repository_strategy_parameters = $with->repository_strategy_parameters;
        }

        if ( ! empty($with->form_requests)) {
            $this->form_requests = array_merge($this->form_requests, $with->form_requests);
        }

        if ( ! empty($with->views)) {
            $this->views = array_merge($this->views, $with->views);
        }
    }

}
