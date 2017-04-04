<?php
namespace Czim\CmsModels\Test\Repositories;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\ModelInformation\ModelInformationCollectorInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Repositories\ModelInformationRepository;
use Czim\CmsModels\Test\Helpers\Models\TestComment;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Illuminate\Support\Collection;
use Mockery;

/**
 * Class ModelInformationRepositoryTest
 *
 * @group repository
 */
class ModelInformationRepositoryTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->deleteModelsCacheFile();
    }

    /**
     * @test
     */
    function it_initializes()
    {
        $coreMock      = $this->getMockCore();
        $collectorMock = $this->getMockCollector();

        $collectorMock->shouldReceive('collect')
            ->andReturn(new Collection([
                'test-a' => new ModelInformation([
                    'model'          => TestPost::class,
                    'original_model' => TestPost::class,
                ]),
                'test-b' => new ModelInformation([
                    'model'          => TestComment::class,
                    'original_model' => TestComment::class,
                ]),
            ]));

        $repository = new ModelInformationRepository($coreMock, $collectorMock);

        $repository->initialize();

        static::assertFalse($repository->getAll()->isEmpty());
    }
    
    /**
     * @test
     */
    function it_should_automatically_initialize_on_retrieval_methods()
    {
        $coreMock      = $this->getMockCore();
        $collectorMock = $this->getMockCollector();
        $collectorMock->shouldReceive('collect')
            ->andReturn(new Collection([
                'test-a' => new ModelInformation([
                    'model'          => TestPost::class,
                    'original_model' => TestPost::class,
                ]),
            ]));

        $repository = new ModelInformationRepository($coreMock, $collectorMock);
        static::assertCount(1, $repository->getAll());

        $repository = new ModelInformationRepository($coreMock, $collectorMock);
        static::assertNotEmpty($repository->getByKey('test-a'));

        $repository = new ModelInformationRepository($coreMock, $collectorMock);
        static::assertNotEmpty($repository->getByModel(new TestPost));

        $repository = new ModelInformationRepository($coreMock, $collectorMock);
        static::assertNotEmpty($repository->getByModelClass(TestPost::class));
    }

    /**
     * @test
     */
    function it_returns_information_by_key()
    {
        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
        ]);

        $coreMock      = $this->getMockCore();
        $collectorMock = $this->getMockCollector();
        $collectorMock->shouldReceive('collect')
            ->andReturn(new Collection([
                'test-a' => $info,
            ]));

        $repository = new ModelInformationRepository($coreMock, $collectorMock);

        static::assertSame($info, $repository->getByKey('test-a'));
        static::assertFalse($repository->getByKey('does-not-exist'), 'Should default to null if key does not exist');
    }

    /**
     * @test
     */
    function it_returns_information_by_model_instance()
    {
        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
        ]);

        $coreMock      = $this->getMockCore();
        $collectorMock = $this->getMockCollector();
        $collectorMock->shouldReceive('collect')
            ->andReturn(new Collection([
                'test-a' => $info,
            ]));

        $repository = new ModelInformationRepository($coreMock, $collectorMock);

        static::assertSame($info, $repository->getByModel(new TestPost));
        static::assertFalse($repository->getByModel(new TestComment), 'Should default to null if model has no info');
    }

    /**
     * @test
     */
    function it_returns_information_by_model_class()
    {
        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
        ]);

        $coreMock      = $this->getMockCore();
        $collectorMock = $this->getMockCollector();
        $collectorMock->shouldReceive('collect')
            ->andReturn(new Collection([
                'test-a' => $info,
            ]));

        $repository = new ModelInformationRepository($coreMock, $collectorMock);

        static::assertSame($info, $repository->getByModelClass(TestPost::class));
        static::assertFalse($repository->getByModelClass(TestComment::class), 'Should default to null if model class has no info');
    }
    
    
    // ------------------------------------------------------------------------------
    //      Cache
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_writes_to_cache()
    {
        static::assertFileNotExists($this->getModelsCachePath(), 'Cache should not exist beforehand');

        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
        ]);

        $coreMock      = $this->getMockCore();
        $collectorMock = $this->getMockCollector();
        $collectorMock->shouldReceive('collect')
            ->andReturn(new Collection([
                'test-a' => $info,
            ]));

        $repository = new ModelInformationRepository($coreMock, $collectorMock);

        $repository->initialize();

        static::assertSame($repository, $repository->writeCache());

        static::assertFileExists($this->getModelsCachePath(), 'Cache was not created');
    }

    /**
     * @test
     */
    function it_clears_the_cache()
    {
        // Make a fake cache file
        file_put_contents($this->getModelsCachePath(), '<?php return []');

        static::assertFileExists($this->getModelsCachePath(), 'Failed to set up fake cache');

        $coreMock      = $this->getMockCore();
        $collectorMock = $this->getMockCollector();

        $repository = new ModelInformationRepository($coreMock, $collectorMock);

        static::assertSame($repository, $repository->clearCache());

        static::assertFileNotExists($this->getModelsCachePath(), 'Cache was not deleted');
    }

    /**
     * @test
     * @depends it_writes_to_cache
     */
    function it_uses_cached_information_if_set()
    {
        $coreMock = $this->getMockCore();

        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
        ]);

        $collectorMock = $this->getMockCollector();
        $collectorMock->shouldReceive('collect')
            ->andReturn(new Collection([
                'test-a' => $info,
            ]));

        $repository = new ModelInformationRepository($coreMock, $collectorMock);
        $repository->initialize()->writeCache();

        // Cache file set up, test whether it is actually used

        $info = new ModelInformation([
            'model'          => TestComment::class,
            'original_model' => TestComment::class,
        ]);

        $collectorMock = $this->getMockCollector();
        $collectorMock->shouldReceive('collect')
            ->andReturn(new Collection([
                'test-a' => $info,
            ]));

        $repository = new ModelInformationRepository($coreMock, $collectorMock);

        static::assertEquals(TestPost::class, $repository->getByKey('test-a')->modelClass());
    }
    

    /**
     * @return CoreInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockCore()
    {
        return Mockery::mock(CoreInterface::class);
    }

    /**
     * @return ModelInformationCollectorInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockCollector()
    {
        return Mockery::mock(ModelInformationCollectorInterface::class);
    }

}
