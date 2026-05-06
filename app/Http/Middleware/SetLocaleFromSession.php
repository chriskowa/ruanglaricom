<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromSession
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) $request->session()->get('locale', '');
        $locale = strtolower(trim($locale));

        if (in_array($locale, ['id', 'en'], true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}

