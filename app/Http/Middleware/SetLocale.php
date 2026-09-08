<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $segment = $request->segment(1);

        if ($segment && LaravelLocalization::checkLocaleInSupportedLocales($segment)) {
            LaravelLocalization::setLocale($segment);
        } elseif (session()->has('locale') && LaravelLocalization::checkLocaleInSupportedLocales(session('locale'))) {
            LaravelLocalization::setLocale(session('locale'));
        }

        return $next($request);
    }
}
