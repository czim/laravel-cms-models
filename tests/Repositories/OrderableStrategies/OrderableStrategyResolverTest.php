<?php
namespace Czim\CmsModels\Test\Repositories\OrderableStrategies;

use Czim\CmsModels\Contracts\Repositories\OrderableStrategyInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Repositories\OrderableStrategies\ListifyStrategy;
use Czim\CmsModels\Repositories\OrderableStrategies\OrderableStrategyResolver;
use Czim\CmsModels\Test\TestCase;

/**
 * Class OrderableStrategyResolverTest
 *
 * @group repository
 * @group repository-strategy
 */
class OrderableStrategyResolverTest extends TestCase
{

    /**
     * @test
     */
    function it_resolves_to_orderable_strategy()
    {
        $info = new ModelInformation;

        $resolver = new OrderableStrategyResolver;

        $strategy = $resolver->resolve($info);

        static::assertInstanceOf(OrderableStrategyInterface::class, $strategy);
        static::assertInstanceOf(ListifyStrategy::class, $strategy);
    }

}
