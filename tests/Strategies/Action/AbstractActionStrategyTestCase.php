<?php
namespace Czim\CmsModels\Test\Strategies\Action;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Mockery;

abstract class AbstractActionStrategyTestCase extends TestCase
{

    /**
     * @return RouteHelperInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockRouteHelper()
    {
        return Mockery::mock(RouteHelperInterface::class);
    }

    /**
     * @return AuthenticatorInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockAuthenticator()
    {
        return Mockery::mock(AuthenticatorInterface::class);
    }

    /**
     * @return TestPost|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockModel()
    {
        return Mockery::mock(TestPost::class);
    }

}
