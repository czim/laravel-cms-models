<?php
namespace Czim\CmsModels\Test\Console;

use Czim\CmsModels\Console\Commands\ClearModelInformationCache;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Mockery;

class ClearModelInformationCacheTest extends ConsoleTestCase
{

    /**
     * @test
     */
    function it_clears_the_model_information_cache()
    {
        $this->getConsoleKernel()->registerCommand(new ClearModelInformationCache);

        $mock = $this->getMockRepository();
        $mock->shouldReceive('clearCache')->once();

        $this->app->instance(ModelInformationRepositoryInterface::class, $mock);

        $this->artisan('cms:models:clear');
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
