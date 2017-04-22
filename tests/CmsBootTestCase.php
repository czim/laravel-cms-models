<?php
namespace Czim\CmsModels\Test;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Providers\CmsCoreServiceProvider;
use Czim\CmsCore\Support\Enums\CmsMiddleware;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Support\Enums\NamedRoute;
use Czim\CmsModels\Providers\CmsModelsServiceProvider;
use Czim\CmsModels\Test\Helpers\Http\Controllers\MockAuthController;
use Illuminate\Contracts\Foundation\Application;

abstract class CmsBootTestCase extends DatabaseTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // Load the CMS even when unit testing
        $app['config']->set('cms-core.testing', true);

        // Set up service providers for tests, excluding what is not part of this package
        $app['config']->set('cms-core.providers', [
            \Czim\CmsCore\Providers\ModuleManagerServiceProvider::class,
            \Czim\CmsCore\Providers\LogServiceProvider::class,
            \Czim\CmsCore\Providers\MiddlewareServiceProvider::class,
            \Czim\CmsCore\Providers\MigrationServiceProvider::class,
            \Czim\CmsCore\Providers\ViewServiceProvider::class,

            //\Czim\CmsAuth\Providers\CmsAuthServiceProvider::class,
            //\Czim\CmsTheme\Providers\CmsThemeServiceProvider::class,
            //\Czim\CmsAuth\Providers\Api\OAuthSetupServiceProvider::class,

            CmsModelsServiceProvider::class,

            \Czim\CmsCore\Providers\Api\CmsCoreApiServiceProvider::class,
            \Czim\CmsCore\Providers\RouteServiceProvider::class,
            \Czim\CmsCore\Providers\Api\ApiRouteServiceProvider::class,
        ]);

        $app['config']->set('cms-api.providers', []);

        // Mock component bindings in the config
        $app['config']->set(
            'cms-core.bindings', [
            Component::BOOTCHECKER => $this->getTestBootCheckerBinding(),
            Component::CACHE       => \Czim\CmsCore\Core\Cache::class,
            Component::CORE        => \Czim\CmsCore\Core\Core::class,
            Component::MODULES     => \Czim\CmsCore\Modules\ModuleManager::class,
            Component::API         => \Czim\CmsCore\Api\ApiCore::class,
            Component::ACL         => \Czim\CmsCore\Auth\AclRepository::class,
            Component::MENU        => \Czim\CmsCore\Menu\MenuRepository::class,
            Component::AUTH        => 'mock-cms-auth',
        ]);

        $this->mockBoundCoreExternalComponents($app);

        $app->register(CmsCoreServiceProvider::class);
    }

    /**
     * @return string
     */
    protected function getTestBootCheckerBinding()
    {
        return \Czim\CmsCore\Core\BootChecker::class;
    }

    /**
     * Mocks components that should not be part of any core test.
     *
     * @param Application $app
     * @return $this
     */
    protected function mockBoundCoreExternalComponents($app)
    {
        $app->bind('mock-cms-auth', function () {

            $mock = $this->getMockBuilder(AuthenticatorInterface::class)->getMock();

            $mock->method('getRouteLoginAction')
                ->willReturn([
                    'middleware' => [ CmsMiddleware::GUEST ],
                    'as'         => NamedRoute::AUTH_LOGIN,
                    'uses'       => MockAuthController::class . '@showLoginForm',
                ]);

            $mock->method('getRouteLoginPostAction')
                ->willReturn([
                    'middleware' => [ CmsMiddleware::GUEST ],
                    'uses'       => MockAuthController::class . '@login',
                ]);

            $mock->method('getRouteLogoutAction')
                ->willReturn([
                    'as'   => NamedRoute::AUTH_LOGOUT,
                    'uses' => MockAuthController::class . '@logout',
                ]);

            $mock->method('getRoutePasswordEmailGetAction')
                ->willReturn([
                    'as'   => NamedRoute::AUTH_PASSWORD_EMAIL,
                    'uses' => MockAuthController::class . '@showLinkRequestForm',
                ]);

            $mock->method('getRoutePasswordEmailPostAction')
                ->willReturn([
                    'uses' => MockAuthController::class . '@sendResetLinkEmail'
                ]);

            $mock->method('getRoutePasswordResetGetAction')
                ->willReturn([
                    'as'   => NamedRoute::AUTH_PASSWORD_RESET,
                    'uses' => MockAuthController::class . '@showResetForm',
                ]);

            $mock->method('getRoutePasswordResetPostAction')
                ->willReturn([
                    'uses' => MockAuthController::class . '@reset'
                ]);

            return $mock;
        });

        return $this;
    }

}
