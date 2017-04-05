<?php
namespace Czim\CmsModels\Http\Requests;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use RuntimeException;

abstract class AbstractModelRequest extends Request
{

    /**
     * The key/slug for the currently active model.
     *
     * @var string
     */
    protected $modelSlug;

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

        $this->modelSlug = $routeHelper->getModelSlugForCurrentRoute();

        if ( ! $this->modelSlug) {
            throw new RuntimeException("Could not determine model slug for route in request");
        }

        $this->modelInformation = $this->getModelInformationRepository()->getByKey($this->modelSlug);

        if ( ! $this->modelInformation) {
            throw new RuntimeException(
                "Could not load information for model slug '{$this->modelSlug}' in request"
            );
        }

        return $this;
    }

    /**
     * Overridden to make sure AJAX calls don't redirect based on session where it can be prevented.
     *
     * For POST to <model-slug>/            we should be redirected back to /create
     * For POST to <model-slug>/update      we should be redirected back to /edit
     *
     * {@inheritdoc}
     */
    protected function getRedirectUrl()
    {
        if ($this->ajax()) {

            $lastSegment = last($this->segments());

            $routePrefix = $this->getRouteHelper()->getRouteNameForModelClass(
                $this->modelInformation->modelClass(),
                true
            );

            // If the model slug is the last segment, and this is a POST request, this is the STORE action.
            if ($lastSegment == $this->getRouteHelper()->getModelSlugForCurrentRoute() && $this->method() == 'POST') {

                $this->redirect = cms_route("{$routePrefix}.create");

            } elseif ($lastSegment == 'update') {

                $this->redirect = cms_route("{$routePrefix}.edit");
            }
        }

        return parent::getRedirectUrl();
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
