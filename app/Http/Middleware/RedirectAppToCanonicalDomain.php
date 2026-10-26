<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectAppToCanonicalDomain
{
    /**
     * Subdomain resmi yang diizinkan aktif dan TIDAK dialihkan ke domain utama.
     * Contoh: ['event', 'eo', 'admin']
     */
    protected array $allowedSubdomains = [
        // Tambahkan subdomain yang diizinkan di sini, contoh: 'event', 'admin'
    ];

    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $isRuangLari = str_ends_with($host, 'ruanglari.com') || str_ends_with($host, 'ruanglari.id');
        if (! $isRuangLari) {
            return $next($request);
        }

        $canonicalHost = str_ends_with($host, '.id') ? 'ruanglari.id' : 'ruanglari.com';
        if ($host === $canonicalHost) {
            return $next($request);
        }

        // Cek jika subdomain terdaftar dalam whitelist (misal via config atau property)
        $subdomain = str_replace(['.ruanglari.com', '.ruanglari.id'], '', $host);
        $allowed = array_merge(
            $this->allowedSubdomains,
            (array) config('app.allowed_subdomains', [])
        );

        if (in_array($subdomain, $allowed, true)) {
            return $next($request);
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
