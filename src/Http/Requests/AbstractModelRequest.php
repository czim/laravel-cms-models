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
     * @param \Illuminate\Contracts\Validation\Factory $factory
     * @return mixed
     */
    public function validator($factory)
    {
        $this->initializeForModelRoute();

        return $factory->make(
            $this->validationData(),
            $this->container->call([$this, 'processedRules']),
            $this->messages(),
            $this->attributes()
        );
    }

    /**
     * Returns post-processed validation rules.
     *
     * @return array
     */
    public function processedRules()
    {
        return $this->container->call([$this, 'rules']);
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
     * @return ModelInformationInterface|ModelInformation
     */
    protected function getModelInformation()
    {
        return $this->modelInformation;
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
