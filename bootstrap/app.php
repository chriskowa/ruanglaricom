<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'events/transactions/webhook',
            'midtrans/webhook',
            'marketplace/webhook',
            'wallet/topup/callback',
            'membership/webhook',
            'webhook/moota',
            'api/moota/webhook',
            'api/tools/race-master/public/*',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            $requestToken = $request->header('X-CSRF-TOKEN')
                ?: $request->input('_token')
                ?: $request->header('X-XSRF-TOKEN');

            $sessionToken = null;
            try {
                $sessionToken = $request->session()->token();
            } catch (\Throwable $t) {
                $sessionToken = null;
            }

            $hash = static function (?string $value): ?string {
                if ($value === null || $value === '') {
                    return null;
                }
                return substr(hash('sha256', $value), 0, 12);
            };

            $sessionCookieName = config('session.cookie');
            $sessionCookiePresent = $sessionCookieName ? $request->cookies->has($sessionCookieName) : false;

            \Illuminate\Support\Facades\Log::warning('CSRF TokenMismatch (419)', [
                'path' => $request->path(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->headers->get('referer'),
                'origin' => $request->headers->get('origin'),
                'expects_json' => $request->expectsJson(),
                'session_cookie_name' => $sessionCookieName,
                'session_cookie_present' => $sessionCookiePresent,
                'session_id_hash' => $hash($request->cookies->get($sessionCookieName)),
                'session_token_hash' => $hash($sessionToken),
                'request_token_hash' => $hash($requestToken),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code' => 'SESSION_EXPIRED',
                    'message' => 'Sesi Anda sudah berakhir. Silakan muat ulang halaman dan coba lagi.',
                ], 419);
            }

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Sesi Anda sudah berakhir. Silakan muat ulang halaman dan coba lagi.']);
        });
    })->create();
