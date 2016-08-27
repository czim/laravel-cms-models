<?php
namespace Czim\CmsModels\Http\Controllers;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use RuntimeException;

abstract class BaseModelController extends Controller
{

    /**
     * @var ModelRepositoryInterface
     */
    protected $modelRepository;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        CoreInterface $core,
        AuthenticatorInterface $auth,
        RouteHelperInterface $routeHelper,
        ModelInformationRepositoryInterface $infoRepository
    ) {
        parent::__construct($core, $auth, $routeHelper, $infoRepository);


        // artisan commands, like route:list, may instantiate this without requiring
        // any actions to be called; no exceptions should be thrown in that case.
        if (app()->runningInConsole()) return;


        $this->initializeForModelRoute()
             ->initializeModelRepository();
    }

    /**
     * Initializes controller and checks context expecting a model route.
     *
     * @return $this
     */
    protected function initializeForModelRoute()
    {
        $this->moduleKey        = $this->routeHelper->getModuleKeyForCurrentRoute();
        $this->permissionPrefix = $this->routeHelper->getPermissionPrefixForModuleKey($this->moduleKey);

        if ( ! $this->moduleKey) {
            throw new RuntimeException("Could not determine module key for route");
        }

        $this->modelInformation = $this->infoRepository->getByKey($this->moduleKey);

        if ( ! $this->modelInformation) {
            throw new RuntimeException("Could not load information for module key '{$this->moduleKey}'");
        }

        $this->routePrefix = $this->routeHelper->getRouteNameForModelClass(
            $this->modelInformation->modelClass(),
            true
        );

        return $this;
    }

    /**
     * Sets up the model repository for the relevant model.
     *
     * @return $this
     */
    protected function initializeModelRepository()
    {
        $this->modelRepository = app(ModelRepositoryInterface::class, [
            $this->modelInformation->modelClass()
        ]);

        return $this;
    }

}
