<?php
namespace Czim\CmsModels\Test\Helpers\Http\Middleware;

use Closure;

/**
 * Class NullMiddleware
 *
 * A middleware class that does nothing.
 */
class NullMiddleware
{

    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure                  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

}
