<?php
namespace Czim\CmsModels\Test;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Providers\CmsCoreServiceProvider;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\Analyzer\Processor\Steps as AnalyzerSteps;
use Czim\CmsModels\Providers\CmsModelsServiceProvider;
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

        $app['config']->set('cms-models.analyzer.steps', [
            AnalyzerSteps\SetBasicInformation::class,
            AnalyzerSteps\CheckGlobalScopes::class,
            AnalyzerSteps\AnalyzeAttributes::class,
            AnalyzerSteps\AnalyzeRelations::class,
            AnalyzerSteps\AnalyzeScopes::class,
            AnalyzerSteps\DetectActivatable::class,
            AnalyzerSteps\DetectOrderable::class,
            AnalyzerSteps\DetectStaplerAttributes::class,
            AnalyzerSteps\AnalyzeTranslation::class,
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

            $mock->method('getRouteLoginAction')->willReturn('MockController@index');
            $mock->method('getRouteLoginPostAction')->willReturn('MockController@index');
            $mock->method('getRouteLogoutAction')->willReturn('MockController@index');

            $mock->method('getRoutePasswordEmailGetAction')->willReturn('MockController@index');
            $mock->method('getRoutePasswordEmailPostAction')->willReturn('MockController@index');
            $mock->method('getRoutePasswordResetGetAction')->willReturn('MockController@index');
            $mock->method('getRoutePasswordResetPostAction')->willReturn('MockController@index');

            return $mock;
        });

        return $this;
    }

}
