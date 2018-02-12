<?php
namespace Czim\CmsModels\Test\Listeners;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Auth\UserInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Events\ModelActivatedInCms;
use Czim\CmsModels\Events\ModelCreatedInCms;
use Czim\CmsModels\Events\ModelDeactivatedInCms;
use Czim\CmsModels\Events\ModelDeletedInCms;
use Czim\CmsModels\Events\ModelListExportedInCms;
use Czim\CmsModels\Events\ModelPositionUpdatedInCms;
use Czim\CmsModels\Events\ModelUpdatedInCms;
use Czim\CmsModels\Listeners\ModelLogListener;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Mockery;

class ModelLogListenerTest extends TestCase
{

    /**
     * @test
     */
    function it_logs_when_a_model_is_created()
    {
        $coreMock  = $this->getMockCore();
        $authMock  = $this->getMockAuth();
        $userMock  = $this->getMockUser();
        $modelMock = $this->getMockModel();

        $coreMock->shouldReceive('auth')->andReturn($authMock);
        $authMock->shouldReceive('user')->andReturn($userMock);
        $userMock->shouldReceive('getUsername')->andReturn('test@user.com');
        $modelMock->shouldReceive('getKey')->andReturn(1);

        $coreMock->shouldReceive('log')->once()
            ->with(
                'info',
                Mockery::pattern('/^model was created: [a-z0-9\\\\_]+ #1 by user: ["\']?test@user\.com["\']? \(IP: ["\']?(\d+\.){3}\d+["\']?\)/i')
            );

        $listener = new ModelLogListener($coreMock);

        $listener->modelCreated(new ModelCreatedInCms($modelMock));
    }

    /**
     * @test
     */
    function it_logs_when_a_model_is_updated()
    {
        $coreMock  = $this->getMockCore();
        $authMock  = $this->getMockAuth();
        $userMock  = $this->getMockUser();
        $modelMock = $this->getMockModel();

        $coreMock->shouldReceive('auth')->andReturn($authMock);
        $authMock->shouldReceive('user')->andReturn($userMock);
        $userMock->shouldReceive('getUsername')->andReturn('test@user.com');
        $modelMock->shouldReceive('getKey')->andReturn(1);

        $coreMock->shouldReceive('log')->once()
            ->with(
                'info',
                Mockery::pattern('/^model was updated: [a-z0-9\\\\_]+ #1 by user: ["\']?test@user\.com["\']? \(IP: ["\']?(\d+\.){3}\d+["\']?\)/i')
            );

        $listener = new ModelLogListener($coreMock);

        $listener->modelUpdated(new ModelUpdatedInCms($modelMock));
    }

    /**
     * @test
     */
    function it_logs_when_a_model_is_deleted()
    {
        $coreMock  = $this->getMockCore();
        $authMock  = $this->getMockAuth();
        $userMock  = $this->getMockUser();
        $modelMock = $this->getMockModel();

        $coreMock->shouldReceive('auth')->andReturn($authMock);
        $authMock->shouldReceive('user')->andReturn($userMock);
        $userMock->shouldReceive('getUsername')->andReturn('test@user.com');
        $modelMock->shouldReceive('getKey')->andReturn(1);

        $coreMock->shouldReceive('log')->once()
            ->with(
                'info',
                Mockery::pattern('/^model was deleted: [a-z0-9\\\\_]+ #1 by user: ["\']?test@user\.com["\']? \(IP: ["\']?(\d+\.){3}\d+["\']?\)/i')
            );

        $listener = new ModelLogListener($coreMock);

        $listener->modelDeleted(new ModelDeletedInCms(TestPost::class, 1));
    }

    /**
     * @test
     */
    function it_logs_when_a_model_is_activated()
    {
        $coreMock  = $this->getMockCore();
        $authMock  = $this->getMockAuth();
        $userMock  = $this->getMockUser();
        $modelMock = $this->getMockModel();

        $coreMock->shouldReceive('auth')->andReturn($authMock);
        $authMock->shouldReceive('user')->andReturn($userMock);
        $userMock->shouldReceive('getUsername')->andReturn('test@user.com');
        $modelMock->shouldReceive('getKey')->andReturn(1);

        $coreMock->shouldReceive('log')->once()
            ->with(
                'info',
                Mockery::pattern('/^model was activated: [a-z0-9\\\\_]+ #1 by user: ["\']?test@user\.com["\']? \(IP: ["\']?(\d+\.){3}\d+["\']?\)/i')
            );

        $listener = new ModelLogListener($coreMock);

        $listener->modelActivated(new ModelActivatedInCms($modelMock));
    }

    /**
     * @test
     */
    function it_logs_when_a_model_is_deactivated()
    {
        $coreMock  = $this->getMockCore();
        $authMock  = $this->getMockAuth();
        $userMock  = $this->getMockUser();
        $modelMock = $this->getMockModel();

        $coreMock->shouldReceive('auth')->andReturn($authMock);
        $authMock->shouldReceive('user')->andReturn($userMock);
        $userMock->shouldReceive('getUsername')->andReturn('test@user.com');
        $modelMock->shouldReceive('getKey')->andReturn(1);

        $coreMock->shouldReceive('log')->once()
            ->with(
                'info',
                Mockery::pattern('/^model was deactivated: [a-z0-9\\\\_]+ #1 by user: ["\']?test@user\.com["\']? \(IP: ["\']?(\d+\.){3}\d+["\']?\)/i')
            );

        $listener = new ModelLogListener($coreMock);

        $listener->modelDeactivated(new ModelDeactivatedInCms($modelMock));
    }

    /**
     * @test
     */
    function it_logs_when_a_model_is_repositioned()
    {
        $coreMock  = $this->getMockCore();
        $authMock  = $this->getMockAuth();
        $userMock  = $this->getMockUser();
        $modelMock = $this->getMockModel();

        $coreMock->shouldReceive('auth')->andReturn($authMock);
        $authMock->shouldReceive('user')->andReturn($userMock);
        $userMock->shouldReceive('getUsername')->andReturn('test@user.com');
        $modelMock->shouldReceive('getKey')->andReturn(1);

        $coreMock->shouldReceive('log')->once()
            ->with(
                'info',
                Mockery::pattern('/^model was repositioned: [a-z0-9\\\\_]+ #1 by user: ["\']?test@user\.com["\']? \(IP: ["\']?(\d+\.){3}\d+["\']?\)/i')
            );

        $listener = new ModelLogListener($coreMock);

        $listener->modelPositionUpdated(new ModelPositionUpdatedInCms($modelMock));
    }

    /**
     * @test
     */
    function it_logs_when_a_model_listing_was_exported()
    {
        $coreMock  = $this->getMockCore();
        $authMock  = $this->getMockAuth();
        $userMock  = $this->getMockUser();
        $modelMock = $this->getMockModel();

        $coreMock->shouldReceive('auth')->andReturn($authMock);
        $authMock->shouldReceive('user')->andReturn($userMock);
        $userMock->shouldReceive('getUsername')->andReturn('test@user.com');
        $modelMock->shouldReceive('getKey')->andReturn(1);

        $coreMock->shouldReceive('log')->once()
            ->with(
                'info',
                Mockery::pattern('/^model list was exported \(["\']?csv["\']?\): [a-z0-9\\\\_]+ by user: ["\']?test@user\.com["\']? \(IP: ["\']?(\d+\.){3}\d+["\']?\)/i')
            );

        $listener = new ModelLogListener($coreMock);

        $listener->modelListExported(new ModelListExportedInCms(TestPost::class, 'csv'));
    }

    /**
     * @return CoreInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockCore()
    {
        return Mockery::mock(CoreInterface::class);
    }

    /**
     * @return AuthenticatorInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockAuth()
    {
        return Mockery::mock(AuthenticatorInterface::class);
    }

    /**
     * @return UserInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockUser()
    {
        return Mockery::mock(UserInterface::class);
    }

    /**
     * @return TestPost|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockModel()
    {
        return Mockery::mock(TestPost::class);
    }
}
