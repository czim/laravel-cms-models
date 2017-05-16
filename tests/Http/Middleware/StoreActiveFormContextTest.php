<?php
namespace Czim\CmsModels\Test\Http\Middleware;

use Czim\CmsModels\Contracts\Support\Translation\TranslationLocaleHelperInterface;
use Czim\CmsModels\Http\Controllers\DefaultModelController;
use Czim\CmsModels\Http\Middleware\StoreActiveFormContext;
use Czim\CmsModels\Test\TestCase;
use Illuminate\Http\Request;
use Mockery;

class StoreActiveFormContextTest extends TestCase
{

    /**
     * @test
     */
    function it_stores_the_active_translation_locale()
    {
        /** @var Request|Mockery\Mock $requestMock */
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('input')
            ->with(DefaultModelController::ACTIVE_TRANSLATION_LOCALE_KEY)
            ->atLeast()->once()
            ->andReturn('en');

        /** @var TranslationLocaleHelperInterface|Mockery\Mock $helperMock */
        $helperMock = Mockery::mock(TranslationLocaleHelperInterface::class);
        $helperMock->shouldReceive('setActiveLocale')->with('en')->once();
        
        $this->app->instance(TranslationLocaleHelperInterface::class, $helperMock);

        $middleware = new StoreActiveFormContext;

        $next = function ($request) { return $request; };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));
    }
}
