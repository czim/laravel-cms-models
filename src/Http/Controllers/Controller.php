<?php
namespace Czim\CmsModels\Http\Controllers;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

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
     * @var ModuleHelperInterface
     */
    protected $moduleHelper;

    /**
     * @var ModelInformationRepositoryInterface
     */
    protected $infoRepository;

    /**
     * The full model module key (models.app-models-post, f.i.).
     *
     * @var string
     */
    protected $moduleKey;

    /**
     * The model slug part of the module key (app-models-post, f.i.).
     *
     * @var string
     */
    protected $modelSlug;

    /**
     * @var null|ModelInformationInterface|ModelInformation
     */
    protected $modelInformation;

    /**
     * @param CoreInterface                       $core
     * @param AuthenticatorInterface              $auth
     * @param RouteHelperInterface                $routeHelper
     * @param ModuleHelperInterface               $moduleHelper
     * @param ModelInformationRepositoryInterface $infoRepository
     */
    public function __construct(
        CoreInterface $core,
        AuthenticatorInterface $auth,
        RouteHelperInterface $routeHelper,
        ModuleHelperInterface $moduleHelper,
        ModelInformationRepositoryInterface $infoRepository
    ) {
        $this->core           = $core;
        $this->auth           = $auth;
        $this->routeHelper    = $routeHelper;
        $this->moduleHelper   = $moduleHelper;
        $this->infoRepository = $infoRepository;
    }


    /**
     * @return CoreInterface
     */
    protected function getCore()
    {
        return $this->core;
    }

    /**
     * @return string
     */
    protected function getModuleKey()
    {
        return $this->moduleKey;
    }

    /**
     * @return string
     */
    protected function getModelSlug()
    {
        return $this->modelSlug;
    }

    /**
     * @return ModelInformationInterface|ModelInformation|null
     */
    protected function getModelInformation()
    {
        return $this->modelInformation;
    }

    /**
     * @return ModuleHelperInterface
     */
    protected function getModuleHelper()
    {
        return $this->moduleHelper;
    }

}
