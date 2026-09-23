<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectAppToCanonicalDomain
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        if ($host !== 'app.ruanglari.com' && !str_starts_with($host, 'app.ruanglari.')) {
            return $next($request);
        }

        $canonicalHost = 'ruanglari.com';
        if (str_ends_with($host, '.id')) {
            $canonicalHost = 'ruanglari.id';
        }

        $isSecure = $request->isSecure()
            || $request->headers->get('X-Forwarded-Proto') === 'https'
            || $request->headers->get('X-Forwarded-Ssl') === 'on';
        $scheme = $isSecure ? 'https' : 'http';

        $path = rawurldecode($request->getPathInfo());
        if ($path === '') {
            $path = '/';
        }

        $qs = $request->getQueryString();
        $target = $scheme . '://' . $canonicalHost . $path;
        if ($qs !== null && $qs !== '') {
            $target .= '?' . $qs;
        }

        return redirect()->away($target, 301);
    }
}
