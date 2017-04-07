<?php
namespace Czim\CmsModels\Test\Repositories\ActivateStrategies;

use Czim\CmsModels\Contracts\Repositories\ActivateStrategyInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Repositories\ActivateStrategies\ActivateStrategyResolver;
use Czim\CmsModels\Repositories\ActivateStrategies\ActiveColumn;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ActivateStrategyResolverTest
 *
 * @group repository
 * @group repository-strategy
 */
class ActivateStrategyResolverTest extends TestCase
{

    /**
     * @test
     */
    function it_resolves_to_activatable_strategy()
    {
        $info = new ModelInformation;

        $resolver = new ActivateStrategyResolver;

        $strategy = $resolver->resolve($info);

        static::assertInstanceOf(ActivateStrategyInterface::class, $strategy);
        static::assertInstanceOf(ActiveColumn::class, $strategy);
    }

}
