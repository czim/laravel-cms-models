<?php
namespace Czim\CmsModels\Test\Support\Form;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Support\Factories\FormStoreStrategyFactoryInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Support\Form\FormDataStorer;
use Czim\CmsModels\Test\DatabaseTestCase;
use Czim\CmsModels\Test\Helpers\Models\TestGenre;
use Czim\CmsModels\Test\Helpers\Strategies\Form\Store\TestSimpleStoreAfter;
use Czim\CmsModels\Test\Helpers\Strategies\Form\Store\TestSimpleThrowsException;
use Mockery;

class FormDataStorerTest extends DatabaseTestCase
{

    protected function migrateDatabase()
    {
        $this->schema()->create('test_genres', function($table) {
            /** @var \Illuminate\Database\Schema\Blueprint $table */
            $table->increments('id');
            $table->string('name', 50);
            $table->nullableTimestamps();
        });
    }


    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelInformation
     */
    function it_saves_the_model_when_store_after_makes_it_dirty_again()
    {
        $coreMock    = $this->getMockCore();
        $factoryMock = $this->getMockFactory();

        $factoryMock->shouldReceive('make')->with('store-after')->andReturn(new TestSimpleStoreAfter);
        $this->app->instance(FormStoreStrategyFactoryInterface::class, $factoryMock);

        $storer = new FormDataStorer($coreMock);

        $info = new ModelInformation([
            'form' => [
                'fields' => [
                    'name' => [
                        'source'         => 'name',
                        'store_strategy' => 'store-after',
                        'options'        => [],
                    ],
                ],
            ],
        ]);

        $storer->setModelInformation($info);

        $model = new TestGenre(['name' => 'old']);

        $storer->store($model, ['name' => 'store me']);

        static::assertEquals('store me', $model->fresh()->getAttribute('name'));
    }
    
    /**
     * @test
     */
    function it_does_not_store_fields_when_user_has_no_permission()
    {
        $coreMock = $this->getMockCore();
        $authMock = $this->getMockAuth();

        $coreMock->shouldReceive('auth')->andReturn($authMock);
        $authMock->shouldReceive('admin')->andReturn(false);
        $authMock->shouldReceive('can')->with(['test-permission'])->andReturn(false);

        $storer = new FormDataStorer($coreMock);

        // For Admin-only field
        $info = new ModelInformation([
            'form' => [
                'fields' => [
                    'name' => [
                        'source'     => 'name',
                        'admin_only' => true,
                    ],
                ],
            ],
        ]);

        $storer->setModelInformation($info);

        $model = new TestGenre(['name' => 'old']);

        $storer->store($model, ['name' => 'store me']);

        static::assertEquals('old', $model->fresh()->getAttribute('name'));

        // For field with permissions
        $info = new ModelInformation([
            'form' => [
                'fields' => [
                    'name' => [
                        'source'      => 'name',
                        'permissions' => 'test-permission',
                    ],
                ],
            ],
        ]);

        $storer->setModelInformation($info);

        $model = new TestGenre(['name' => 'old']);

        $storer->store($model, ['name' => 'store me']);

        static::assertEquals('old', $model->fresh()->getAttribute('name'));
    }

    /**
     * @test
     */
    function it_stores_values_without_permission_for_admins()
    {
        $coreMock    = $this->getMockCore();
        $authMock    = $this->getMockAuth();
        $factoryMock = $this->getMockFactory();

        $factoryMock->shouldReceive('make')->with('store-after')->andReturn(new TestSimpleStoreAfter);
        $this->app->instance(FormStoreStrategyFactoryInterface::class, $factoryMock);

        $coreMock->shouldReceive('auth')->andReturn($authMock);
        $authMock->shouldReceive('admin')->andReturn(true);
        $authMock->shouldReceive('can')->with(['test-permission'])->andReturn(false);

        $storer = new FormDataStorer($coreMock);

        // For Admin-only field
        $info = new ModelInformation([
            'form' => [
                'fields' => [
                    'name' => [
                        'source'         => 'name',
                        'store_strategy' => 'store-after',
                        'permissions'    => 'test-permission',
                    ],
                ],
            ],
        ]);

        $storer->setModelInformation($info);

        $model = new TestGenre(['name' => 'old']);

        $storer->store($model, ['name' => 'store me']);

        static::assertEquals('store me', $model->fresh()->getAttribute('name'));
    }

    /**
     * @test
     * @expectedException \Czim\CmsModels\Exceptions\StrategyApplicationException
     * @expectedExceptionMessageRegExp #form field 'name'.*failed to store on purpose#is
     */
    function it_throws_a_strategy_application_exception_if_a_store_strategy_fails_to_store()
    {
        $coreMock    = $this->getMockCore();
        $factoryMock = $this->getMockFactory();

        $factoryMock->shouldReceive('make')->with('store')->andReturn(new TestSimpleThrowsException);
        $this->app->instance(FormStoreStrategyFactoryInterface::class, $factoryMock);

        $storer = new FormDataStorer($coreMock);

        $info = new ModelInformation([
            'form' => [
                'fields' => [
                    'name' => [
                        'source'         => 'name',
                        'store_strategy' => 'store',
                        'options'        => [],
                    ],
                ],
            ],
        ]);

        $storer->setModelInformation($info);

        $model = new TestGenre(['name' => 'old']);

        $storer->store($model, ['name' => 'irrelevant']);
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
     * @return FormStoreStrategyFactoryInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockFactory()
    {
        return Mockery::mock(FormStoreStrategyFactoryInterface::class);
    }

}
