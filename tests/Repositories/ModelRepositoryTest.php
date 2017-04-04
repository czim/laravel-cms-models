<?php
namespace Czim\CmsModels\Test\Repositories;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Repositories\ModelRepository;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Mockery;

/**
 * Class ModelRepositoryTest
 *
 * @group repository
 */
class ModelRepositoryTest extends TestCase
{

    /**
     * @test
     */
    function it_constructs_for_the_indicated_model_class()
    {
        $repository = new ModelRepository(TestPost::class);

        static::assertEquals(TestPost::class, $repository->model());

        $query = $repository->query();

        static::assertInstanceOf(Builder::class, $query);
        static::assertInstanceOf(TestPost::class, $query->getModel());
    }

    /**
     * @return ModelInformationRepositoryInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockModelInfoRepository()
    {
        return Mockery::mock(ModelInformationRepositoryInterface::class);
    }

    /**
     * @return RouteHelperInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockRouteHelper()
    {
        return Mockery::mock(RouteHelperInterface::class);
    }
}
