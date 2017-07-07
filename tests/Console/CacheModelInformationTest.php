<?php
namespace Czim\CmsModels\Test\Console;

use Czim\CmsModels\Console\Commands\CacheModelInformation;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Mockery;

/**
 * Class CacheModelInformationTest
 *
 * @group console
 */
class CacheModelInformationTest extends ConsoleTestCase
{

    /**
     * @test
     */
    function it_clears_the_model_information_cache()
    {
        $this->getConsoleKernel()->registerCommand(new CacheModelInformation());

        $mock = $this->getMockRepository();
        $mock->shouldReceive('clearCache')->once()->andReturnSelf();
        $mock->shouldReceive('writeCache')->once()->andReturnSelf();

        $this->app->instance(ModelInformationRepositoryInterface::class, $mock);

        $this->artisan('cms:models:cache');
    }


    // ------------------------------------------------------------------------------
    //      Helpers
    // ------------------------------------------------------------------------------

    /**
     * @return ModelInformationRepositoryInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockRepository()
    {
        return Mockery::mock(ModelInformationRepositoryInterface::class);
    }

}
