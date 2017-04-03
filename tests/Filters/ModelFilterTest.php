<?php
namespace Czim\CmsModels\Test\Filters;

use Czim\CmsModels\Contracts\Strategies\FilterStrategyInterface;
use Czim\CmsModels\Contracts\Support\Factories\FilterStrategyFactoryInterface;
use Czim\CmsModels\Filters\ModelFilter;
use Czim\CmsModels\Filters\ModelFilterData;
use Czim\CmsModels\ModelInformation\Data\Listing\ModelListFilterData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Mockery;

class ModelFilterTest extends TestCase
{

    /**
     * @test
     */
    function it_sets_defaults_based_on_filters()
    {
        // Setup

        /** @var FilterStrategyInterface|Mockery\MockInterface|Mockery\Mock $mockStrategy */
        $mockStrategy = Mockery::mock(FilterStrategyInterface::class);
        $mockStrategy
            ->shouldReceive('apply')->with(Mockery::type(EloquentBuilder::class), 'title', 'testing')->once()
            ->andReturnUsing(function ($query) { return $query; });
        $mockStrategy
            ->shouldReceive('apply')->with(Mockery::type(EloquentBuilder::class), 'active', true)->once()
            ->andReturnUsing(function ($query) { return $query; });

        /** @var FilterStrategyFactoryInterface|Mockery\MockInterface|Mockery\Mock $mockFactory */
        $mockFactory = Mockery::mock(FilterStrategyFactoryInterface::class);
        $mockFactory
            ->shouldReceive('make')->with('test-a', 'title', Mockery::type(ModelListFilterData::class))->once()
            ->andReturn($mockStrategy);
        $mockFactory
            ->shouldReceive('make')->with('test-b', 'active', Mockery::type(ModelListFilterData::class))->once()
            ->andReturn($mockStrategy);

        $this->app->instance(FilterStrategyFactoryInterface::class, $mockFactory);

        // Parameters
        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
            'list' => [
                'filters' => [
                    'title' => [
                        'target'   => 'title',
                        'strategy' => 'test-a',
                    ],
                    'active' => [
                        'target'   => 'active',
                        'strategy' => 'test-b',
                    ],
                ],
            ],
        ]);

        $data = new ModelFilterData($info, [
            'title'  => 'testing',
            'active' => true,
        ]);

        $filter = new ModelFilter($info, $data);

        $query = TestPost::query();

        $filter->apply($query);
    }

    /**
     * @test
     * @expectedException \Czim\Filter\Exceptions\FilterParameterUnhandledException
     * @expectedExceptionMessageRegExp #['"]extra['"]#
     */
    function it_throws_the_standard_filter_exception_if_a_parameter_is_unhandled()
    {
        // Parameters
        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
            'list' => [
                'filters' => [
                    'title' => [
                        'target'   => 'title',
                        'strategy' => 'test-a',
                    ],
                    'active' => [
                        'target'   => 'active',
                        'strategy' => 'test-b',
                    ],
                ],
            ],
        ]);

        $moreInfo = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
            'list' => [
                'filters' => [
                    'title' => [
                        'target'   => 'title',
                        'strategy' => 'test-a',
                    ],
                    'active' => [
                        'target'   => 'active',
                        'strategy' => 'test-b',
                    ],
                    'extra' => [
                        'target'   => 'extra',
                        'strategy' => 'test-c',
                    ]
                ],
            ],
        ]);

        $data = new ModelFilterData($moreInfo, [
            'extra' => true,
        ]);

        $filter = new ModelFilter($info, $data);

        $query = TestPost::query();

        $filter->apply($query);
    }

}
