<?php
namespace Czim\CmsModels\Test\Repositories;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;
use Czim\CmsModels\Contracts\Strategies\FilterStrategyInterface;
use Czim\CmsModels\Contracts\Support\Factories\FilterStrategyFactoryInterface;
use Czim\CmsModels\Contracts\Support\Factories\ModelRepositoryFactoryInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Repositories\ModelReferenceRepository;
use Czim\CmsModels\Repositories\ModelRepository;
use Czim\CmsModels\Strategies\Filter\BasicSplitString;
use Czim\CmsModels\Strategies\Reference\DefaultReference;
use Czim\CmsModels\Strategies\Reference\IdAndAttribute;
use Czim\CmsModels\Support\Data\Strategies\ModelMetaReference;
use Czim\CmsModels\Test\AbstractPostCommentSeededTestCase;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\Helpers\Strategies\Context\TestSpecificIdOnly;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mockery;

/**
 * Class ModelReferenceRepositoryTest
 *
 * @group repository
 */
class ModelReferenceRepositoryTest extends AbstractPostCommentSeededTestCase
{

    public function setUp()
    {
        parent::setUp();

        /** @var ModelRepositoryFactoryInterface|Mockery\Mock $mockFactory */
        $mockFactory = Mockery::mock(ModelRepositoryFactoryInterface::class);
        $mockFactory->shouldReceive('make')->andReturnUsing(function ($model) {
            return new ModelRepository($model);
        });

        $this->app->instance(ModelRepositoryFactoryInterface::class, $mockFactory);
    }

    protected function seedDatabase()
    {
        $this
            ->seedAuthors()
            ->seedPosts();
    }
    
    
    // ------------------------------------------------------------------------------
    //      Direct references
    // ------------------------------------------------------------------------------
    
    /**
     * @test
     */
    function it_makes_a_reference_for_a_model_instance()
    {
        $model = TestPost::first();
        $info = new ModelInformation([
            'reference' => [
                'strategy' => IdAndAttribute::class,
                'source'   => 'title',
            ],
        ]);

        $infoRepositoryMock = $this->getMockInformationRepository();

        $infoRepositoryMock->shouldReceive('getByModel')->with($model)->andReturn($info);

        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);

        $repository = new ModelReferenceRepository($infoRepositoryMock);

        static::assertEquals('#1: Some Basic Title', $repository->getReferenceForModel($model));
    }

    /**
     * @test
     * @depends it_makes_a_reference_for_a_model_instance
     */
    function it_makes_a_reference_for_a_model_using_specified_strategy_and_source()
    {
        $model = TestPost::first();
        $info = new ModelInformation([
            'reference' => [
                'strategy' => IdAndAttribute::class,
                'source'   => 'title',
            ],
        ]);

        $infoRepositoryMock = $this->getMockInformationRepository();

        $infoRepositoryMock->shouldReceive('getByModel')->with($model)->andReturn($info);

        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);

        $repository = new ModelReferenceRepository($infoRepositoryMock);

        static::assertEquals('1', $repository->getReferenceForModel($model, DefaultReference::class, 'id'));
    }

    /**
     * @test
     */
    function it_makes_references_for_multiple_model_instances()
    {
        /** @var Collection|TestPost[] $models */
        $models = TestPost::take(3)->get();

        $info = new ModelInformation([
            'reference' => [
                'strategy' => IdAndAttribute::class,
                'source'   => 'title',
            ],
        ]);

        $infoRepositoryMock = $this->getMockInformationRepository();

        $infoRepositoryMock->shouldReceive('getByModel')->with($models->first())->andReturn($info);

        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);

        $repository = new ModelReferenceRepository($infoRepositoryMock);

        $references = $repository->getReferencesForModels($models);

        static::assertInstanceOf(Collection::class, $references);
        static::assertCount(3, $references);
        static::assertEquals('#1: Some Basic Title', $references->get(1));
        static::assertEquals('#2: Elaborate Alternative Title', $references->get(2));
        static::assertEquals('#3: Surprising Testing Title', $references->get(3));

        // Models may also be given as an array, with same results
        $references = $repository->getReferencesForModels($models->all());

        static::assertInstanceOf(Collection::class, $references);
        static::assertCount(3, $references);
    }

    /**
     * @test
     */
    function it_returns_an_empty_collection_if_none_given_for_multiple_model_instances()
    {
        $infoRepositoryMock = $this->getMockInformationRepository();

        $repository = new ModelReferenceRepository($infoRepositoryMock);

        $references = $repository->getReferencesForModels([]);

        static::assertInstanceOf(Collection::class, $references);
        static::assertTrue($references->isEmpty());
    }

    /**
     * @test
     * @depends it_makes_references_for_multiple_model_instances
     */
    function it_makes_references_for_multiple_model_instances_using_specified_strategy_and_source()
    {
        /** @var Collection|TestPost[] $models */
        $models = TestPost::take(3)->get();

        $info = new ModelInformation([
            'reference' => [
                'strategy' => IdAndAttribute::class,
                'source'   => 'title',
            ],
        ]);

        $infoRepositoryMock = $this->getMockInformationRepository();

        $infoRepositoryMock->shouldReceive('getByModel')->with($models->first())->andReturn($info);

        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);

        $repository = new ModelReferenceRepository($infoRepositoryMock);

        $references = $repository->getReferencesForModels($models, DefaultReference::class, 'id');

        static::assertInstanceOf(Collection::class, $references);
        static::assertCount(3, $references);
        static::assertEquals('1', $references->get(1));
        static::assertEquals('2', $references->get(2));
        static::assertEquals('3', $references->get(3));
    }

    /**
     * @test
     * @depends it_makes_a_reference_for_a_model_instance
     * @depends it_makes_references_for_multiple_model_instances
     */
    function it_silently_falls_back_to_a_fallback_strategy_if_no_strategy_could_be_determined()
    {
        $this->app['config']->set('cms-models.strategies.reference.default-strategy', null);

        $model = TestPost::first();
        $info = new ModelInformation([
            'reference' => [],
        ]);

        $infoRepositoryMock = $this->getMockInformationRepository();

        $infoRepositoryMock->shouldReceive('getByModel')->with(Mockery::type(TestPost::class))->andReturn($info);

        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);

        $repository = new ModelReferenceRepository($infoRepositoryMock);

        static::assertEquals('1', $repository->getReferenceForModel($model));

        // Multiple models

        /** @var Collection|TestPost[] $models */
        $models = TestPost::take(3)->get();

        $references = $repository->getReferencesForModels($models);

        static::assertInstanceOf(Collection::class, $references);
        static::assertCount(3, $references);
        static::assertEquals('1', $references->get(1));
        static::assertEquals('2', $references->get(2));
        static::assertEquals('3', $references->get(3));
    }
    
    
    // ------------------------------------------------------------------------------
    //      Meta references
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_returns_reference_for_model_meta_reference_by_model_instance()
    {
        $model = TestPost::first();
        $info = new ModelInformation([
            'reference' => [],
        ]);

        $refData = new ModelMetaReference([
            'model'  => TestPost::class,
            'source' => 'title',
        ]);

        $infoRepositoryMock = $this->getMockInformationRepository();
        $infoRepositoryMock->shouldReceive('getByModel')->with(Mockery::type(TestPost::class))->andReturn($info);
        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);

        $repository = new ModelReferenceRepository($infoRepositoryMock);

        static::assertEquals('#1: Some Basic Title', $repository->getReferenceForModelMetaReferenceByModel($refData, $model));
    }

    /**
     * @test
     */
    function it_returns_reference_for_model_meta_reference_by_key()
    {
        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
            'reference'      => [],
        ]);

        $refData = new ModelMetaReference([
            'model'  => TestPost::class,
            'source' => 'title',
        ]);

        $infoRepositoryMock = $this->getMockInformationRepository();
        $infoRepositoryMock->shouldReceive('getByModelClass')->with(TestPost::class)->andReturn($info);
        $infoRepositoryMock->shouldReceive('getByModel')->with(Mockery::type(TestPost::class))->andReturn($info);
        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);

        $modelRepositoryMock = $this->getMockModelRepository();
        $this->app->instance(ModelRepositoryInterface::class, $modelRepositoryMock);
        $modelRepositoryMock->shouldReceive('query')->andReturn(TestPost::query());

        $repository = new ModelReferenceRepository($infoRepositoryMock);

        static::assertEquals('#1: Some Basic Title', $repository->getReferenceForModelMetaReferenceByKey($refData, 1));
    }
    
    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_if_the_model_class_referenced_in_reference_data_is_not_a_model()
    {
        $refData = new ModelMetaReference([
            'model' => static::class,
        ]);

        $infoRepositoryMock = $this->getMockInformationRepository();
        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);

        $repository = new ModelReferenceRepository($infoRepositoryMock);

        $repository->getReferenceForModelMetaReferenceByKey($refData, 1);
    }

    /**
     * @test
     */
    function it_can_handle_models_not_defined_by_the_cms_for_references_by_reference_data()
    {
        $refData = new ModelMetaReference([
            'model' => TestPost::class,
        ]);

        $infoRepositoryMock = $this->getMockInformationRepository();
        $infoRepositoryMock->shouldReceive('getByModelClass')->with(TestPost::class)->andReturn(null);
        $infoRepositoryMock->shouldReceive('getByModel')->with(Mockery::type(TestPost::class))->andReturn(null);
        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);

        $repository = new ModelReferenceRepository($infoRepositoryMock);

        static::assertEquals('#1: 1', $repository->getReferenceForModelMetaReferenceByKey($refData, 1));
    }

    /**
     * @test
     */
    function it_returns_reference_for_model_meta_references_by_reference_data()
    {
        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
        ]);

        $refData = new ModelMetaReference([
            'model'  => TestPost::class,
            'source' => 'title',
        ]);

        $infoRepositoryMock = $this->getMockInformationRepository();
        $infoRepositoryMock->shouldReceive('getByModelClass')->with(TestPost::class)->andReturn($info);
        $infoRepositoryMock->shouldReceive('getByModel')->with(Mockery::type(TestPost::class))->andReturn($info);
        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);

        $modelRepositoryMock = $this->getMockModelRepository();
        $this->app->instance(ModelRepositoryInterface::class, $modelRepositoryMock);
        $modelRepositoryMock->shouldReceive('query')->andReturn(TestPost::query());

        $repository = new ModelReferenceRepository($infoRepositoryMock);

        $references = $repository->getReferencesForModelMetaReference($refData);

        static::assertInstanceOf(Collection::class, $references);
        static::assertCount(3, $references);
        static::assertEquals('#1: Some Basic Title', $references->get(1));
        static::assertEquals('#2: Elaborate Alternative Title', $references->get(2));
        static::assertEquals('#3: Surprising Testing Title', $references->get(3));
    }

    /**
     * @test
     */
    function it_returns_reference_for_model_meta_references_by_reference_data_filtered_by_search_string()
    {
        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
        ]);

        $refData = new ModelMetaReference([
            'model'  => TestPost::class,
            'source' => 'title',
        ]);

        $infoRepositoryMock = $this->getMockInformationRepository();
        $infoRepositoryMock->shouldReceive('getByModelClass')->with(TestPost::class)->andReturn($info);
        $infoRepositoryMock->shouldReceive('getByModel')->with(Mockery::type(TestPost::class))->andReturn($info);
        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);

        $modelRepositoryMock = $this->getMockModelRepository();
        $this->app->instance(ModelRepositoryInterface::class, $modelRepositoryMock);
        $modelRepositoryMock->shouldReceive('query')->andReturn(TestPost::query());

        $filterMock = $this->getMockFilterStrategy();
        $filterMock->shouldReceive('apply')->with(Mockery::type(Builder::class), 'title', 'elaborate')
            ->andReturnUsing(function ($query) {
                /** @var Builder $query */
                // Use a mock query modification to yield expected result
                return $query->where('id', 2);
            });

        $filterFactoryMock = $this->getMockFilterStrategyFactory();
        $filterFactoryMock->shouldReceive('make')->with(BasicSplitString::class)->andReturn($filterMock);
        $this->app->instance(FilterStrategyFactoryInterface::class, $filterFactoryMock);

        $repository = new ModelReferenceRepository($infoRepositoryMock);

        $references = $repository->getReferencesForModelMetaReference($refData, 'elaborate');

        static::assertInstanceOf(Collection::class, $references);
        static::assertCount(1, $references);
        static::assertEquals('#2: Elaborate Alternative Title', $references->get(2));
    }

    /**
     * @test
     */
    function it_returns_reference_for_model_meta_references_by_reference_data_applying_a_specified_context_strategy()
    {
        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
        ]);

        $refData = new ModelMetaReference([
            'model'            => TestPost::class,
            'source'           => 'title',
            'context_strategy' => TestSpecificIdOnly::class,
        ]);

        $infoRepositoryMock = $this->getMockInformationRepository();
        $infoRepositoryMock->shouldReceive('getByModelClass')->with(TestPost::class)->andReturn($info);
        $infoRepositoryMock->shouldReceive('getByModel')->with(Mockery::type(TestPost::class))->andReturn($info);
        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);

        $modelRepositoryMock = $this->getMockModelRepository();
        $this->app->instance(ModelRepositoryInterface::class, $modelRepositoryMock);
        $modelRepositoryMock->shouldReceive('query')->andReturn(TestPost::query());

        $repository = new ModelReferenceRepository($infoRepositoryMock);

        $references = $repository->getReferencesForModelMetaReference($refData);

        static::assertInstanceOf(Collection::class, $references);
        static::assertCount(1, $references);
        static::assertEquals('#2: Elaborate Alternative Title', $references->get(2));
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessageRegExp #not a sort strategy#
     */
    function it_throws_an_exception_if_the_resolved_sorting_strategy_is_invalid()
    {
        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
        ]);

        $refData = new ModelMetaReference([
            'model'         => TestPost::class,
            'source'        => 'title',
            'sort_strategy' => IdAndAttribute::class, // not a sorting strategy
        ]);

        $infoRepositoryMock = $this->getMockInformationRepository();
        $infoRepositoryMock->shouldReceive('getByModelClass')->with(TestPost::class)->andReturn($info);
        $infoRepositoryMock->shouldReceive('getByModel')->with(Mockery::type(TestPost::class))->andReturn($info);
        $this->app->instance(ModelInformationRepositoryInterface::class, $infoRepositoryMock);

        $modelRepositoryMock = $this->getMockModelRepository();
        $this->app->instance(ModelRepositoryInterface::class, $modelRepositoryMock);
        $modelRepositoryMock->shouldReceive('query')->andReturn(TestPost::query());

        $repository = new ModelReferenceRepository($infoRepositoryMock);

        $repository->getReferencesForModelMetaReference($refData);
    }
    

    /**
     * @return CoreInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockCore()
    {
        return Mockery::mock(CoreInterface::class);
    }

    /**
     * @return ModelInformationRepositoryInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockInformationRepository()
    {
        return Mockery::mock(ModelInformationRepositoryInterface::class);
    }

    /**
     * @return ModelRepositoryInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockModelRepository()
    {
        return Mockery::mock(ModelRepositoryInterface::class);
    }

    /**
     * @return FilterStrategyFactoryInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockFilterStrategyFactory()
    {
        return Mockery::mock(FilterStrategyFactoryInterface::class);
    }

    /**
     * @return FilterStrategyInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockFilterStrategy()
    {
        return Mockery::mock(FilterStrategyInterface::class);
    }

}
