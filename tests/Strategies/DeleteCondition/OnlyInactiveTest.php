<?php
namespace Czim\CmsModels\Test\Strategies\DeleteCondition;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Strategies\DeleteCondition\OnlyInactive;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class OnlyInactiveTest
 *
 * @group strategies
 * @group strategies-delete-condition
 */
class OnlyInactiveTest extends TestCase
{

    /**
     * @test
     */
    function it_only_reports_inactive_records_deletable()
    {
        $model = new TestPost;
        $model->checked = true;

        $info = new ModelInformation([
            'list' => [
                'activatable'   => true,
                'active_column' => 'checked',
            ],
        ]);

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $repositoryMock */
        $repositoryMock = Mockery::mock(ModelInformationRepositoryInterface::class);
        $repositoryMock->shouldReceive('getByModel')->with($model)->andReturn($info);
        $this->app->instance(ModelInformationRepositoryInterface::class, $repositoryMock);

        $condition = new OnlyInactive;

        static::assertFalse($condition->check($model, []), 'Should report false if active column is true');

        $model->checked = false;

        static::assertTrue($condition->check($model, []), 'Should report true if active column is false');
    }

    /**
     * @test
     */
    function it_defaults_to_using_the_active_column()
    {
        $model = new TestPost;
        $model->active = true;

        $info = new ModelInformation;

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $repositoryMock */
        $repositoryMock = Mockery::mock(ModelInformationRepositoryInterface::class);
        $repositoryMock->shouldReceive('getByModel')->with($model)->andReturn($info);
        $this->app->instance(ModelInformationRepositoryInterface::class, $repositoryMock);

        $condition = new OnlyInactive;

        static::assertFalse($condition->check($model, []));
    }

    /**
     * @test
     */
    function it_returns_a_message()
    {
        /** @var CoreInterface|Mockery\Mock $coreMock */
        $coreMock = Mockery::mock(CoreInterface::class);
        $coreMock->shouldReceive('config')->with('translation.prefix', 'cms::')->andReturn('cms::');
        $this->app->instance(Component::CORE, $coreMock);

        $condition = new OnlyInactive;

        $this->app['translator']->addLines(['models.delete.failure.is-active' => 'Test Message'], 'en', '*');

        static::assertEquals('Test Message', $condition->message());
    }

}
