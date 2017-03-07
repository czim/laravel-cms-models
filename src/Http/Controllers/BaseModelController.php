<?php
namespace Czim\CmsModels\Http\Controllers;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Contracts\Support\Factories\ModelRepositoryFactoryInterface;
use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Czim\CmsModels\Http\Controllers\Traits\AppliesRepositoryContext;
use Czim\CmsModels\Support\ModuleHelper;
use Czim\Repository\Contracts\ExtendedRepositoryInterface;
use RuntimeException;

abstract class BaseModelController extends Controller
{
    use AppliesRepositoryContext;

    /**
     * @var ModelRepositoryInterface|ExtendedRepositoryInterface
     */
    protected $modelRepository;

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
     * {@inheritdoc}
     */
    public function __construct(
        CoreInterface $core,
        AuthenticatorInterface $auth,
        RouteHelperInterface $routeHelper,
        ModuleHelperInterface $moduleHelper,
        ModelInformationRepositoryInterface $infoRepository
    ) {
        parent::__construct($core, $auth, $routeHelper, $moduleHelper, $infoRepository);


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
        $this->modelSlug        = $this->routeHelper->getModelSlugForCurrentRoute();
        $this->permissionPrefix = $this->routeHelper->getPermissionPrefixForModelSlug($this->modelSlug);

        if ( ! $this->moduleKey) {
            throw new RuntimeException("Could not determine module key for route");
        }

        $this->modelInformation = $this->infoRepository->getByKey($this->modelSlug);

        if ( ! $this->modelInformation) {
            throw new RuntimeException("Could not load information for model key '{$this->modelSlug}'");
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
        /** @var ModelRepositoryFactoryInterface $factory */
        $factory = app(ModelRepositoryFactoryInterface::class);

        $this->modelRepository = $factory->make($this->modelInformation->modelClass());

        $this->applyRepositoryContext();

        return $this;
    }

    /**
     * @return ModelRepositoryInterface|ExtendedRepositoryInterface
     */
    protected function getModelRepository()
    {
        return $this->modelRepository;
    }

    /**
     * @return string
     */
    protected function getRoutePrefix()
    {
        return $this->routePrefix;
    }

    /**
     * @return string
     */
    protected function getPermissionPrefix()
    {
        return $this->permissionPrefix;
    }

}
