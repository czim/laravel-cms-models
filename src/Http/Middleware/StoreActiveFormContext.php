<?php
namespace Czim\CmsModels\Http\Middleware;

use Closure;
use Czim\CmsModels\Contracts\Support\Translation\TranslationLocaleHelperInterface;
use Czim\CmsModels\Http\Controllers\DefaultModelController;

class StoreActiveFormContext
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure                  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->storeActiveTranslationLocale();

        return $next($request);
    }

    /**
     * Stores active translation locale in session.
     */
    protected function storeActiveTranslationLocale()
    {
        $locale = request()->input(DefaultModelController::ACTIVE_TRANSLATION_LOCALE_KEY);

        if ( ! $locale) return;

        /** @var TranslationLocaleHelperInterface $helper */
        $helper = app(TranslationLocaleHelperInterface::class);

        $helper->setActiveLocale($locale);
    }

}
