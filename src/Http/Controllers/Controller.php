<?php
namespace Czim\CmsModels\Http\Controllers;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Support\Data\ModelInformation;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use RuntimeException;

abstract class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @var AuthenticatorInterface
     */
    protected $auth;

    /**
     * @var RouteHelperInterface
     */
    protected $routeHelper;

    /**
     * @var ModelInformationRepositoryInterface
     */
    protected $repository;

    /**
     * @var string
     */
    protected $moduleKey;

    /**
     * @var string
     */
    protected $routePrefix;

    /**
     * Prefix for auth module permissions, including the final period.
     *
     * @var string
     */
    protected $permissionPrefix;

    /**
     * @var null|ModelInformationInterface|ModelInformation
     */
    protected $modelInformation;

    /**
     * @param CoreInterface                       $core
     * @param AuthenticatorInterface              $auth
     * @param RouteHelperInterface                $routeHelper
     * @param ModelInformationRepositoryInterface $repository
     */
    public function __construct(
        CoreInterface $core,
        AuthenticatorInterface $auth,
        RouteHelperInterface $routeHelper,
        ModelInformationRepositoryInterface $repository
    ) {
        $this->core        = $core;
        $this->auth        = $auth;
        $this->routeHelper = $routeHelper;
        $this->repository  = $repository;

        $this->moduleKey        = $this->routeHelper->getModuleKeyForCurrentRoute();
        $this->permissionPrefix = $this->routeHelper->getPermissionPrefixForModuleKey($this->moduleKey);

        if ( ! $this->moduleKey) {
            throw new RuntimeException("Could not determine module key for route");
        }

        $this->modelInformation = $this->repository->getByKey($this->moduleKey);

        if ( ! $this->modelInformation) {
            throw new RuntimeException("Could not load information for module key '{$this->moduleKey}'");
        }

        $this->routePrefix = $this->routeHelper->getRouteNameForModelClass(
            $this->modelInformation->modelClass(),
            true
        );
    }

}
