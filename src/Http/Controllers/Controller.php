<?php
namespace Czim\CmsModels\Http\Controllers;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
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
     * @var ModelInformationRepositoryInterface
     */
    protected $repository;

    /**
     * @param CoreInterface                       $core
     * @param AuthenticatorInterface              $auth
     * @param ModelInformationRepositoryInterface $repository
     */
    public function __construct(
        CoreInterface $core,
        AuthenticatorInterface $auth,
        ModelInformationRepositoryInterface $repository
    ) {
        $this->core       = $core;
        $this->auth       = $auth;
        $this->repository = $repository;
    }

}
