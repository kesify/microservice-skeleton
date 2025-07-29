<?php

namespace Kesify\MicroserviceSkeleton\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

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

        $availableLocales = ['en', 'de', 'tr'];
        if (! in_array($locale, $availableLocales)) {
            $locale = Config::get('app.fallback_locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
