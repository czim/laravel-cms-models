<?php
namespace Czim\CmsModels\Http\Requests;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Support\Data\ModelInformation;
use RuntimeException;

abstract class AbstractModelRequest extends Request
{

    /**
     * The key for the currently active model module.
     *
     * @var string
     */
    protected $moduleKey;

    /**
     * @var ModelInformationInterface|ModelInformation
     */
    protected $modelInformation;
    

    /**
     * Validate the class instance.
     *
     * @return void
     */
    public function validate()
    {
        $this->initializeForModelRoute();

        parent::validate();
    }

    /**
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Initializes form request and checks context expecting a model route.
     *
     * @return $this
     */
    protected function initializeForModelRoute()
    {
        $routeHelper = $this->getRouteHelper();

        $this->moduleKey = $routeHelper->getModuleKeyForCurrentRoute();

        if ( ! $this->moduleKey) {
            throw new RuntimeException("Could not determine module key for route in request");
        }

        $this->modelInformation = $this->getModelInformationRepository()->getByKey($this->moduleKey);

        if ( ! $this->modelInformation) {
            throw new RuntimeException(
                "Could not load information for module key '{$this->moduleKey}' in request"
            );
        }

        return $this;
    }


    /**
     * @return RouteHelperInterface
     */
    protected function getRouteHelper()
    {
        return app(RouteHelperInterface::class);
    }

    /**
     * @return ModelInformationRepositoryInterface
     */
    protected function getModelInformationRepository()
    {
        return app(ModelInformationRepositoryInterface::class);
    }

}
