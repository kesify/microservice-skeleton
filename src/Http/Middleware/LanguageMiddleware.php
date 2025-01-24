<?php

namespace Kesify\MicroserviceSkeleton\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use function App\Http\Middleware\config;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $localeHeader = $request->header('Accept-Language', 'en');

        $locale = substr($localeHeader, 0, 2);

        $availableLocales = ['en', 'de'];
        if (! in_array($locale, $availableLocales)) {
            $locale = config('app.fallback_locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
