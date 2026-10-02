<?php

namespace App\Http\Middleware;

use App\Services\KalenderPelari\TrialHandoffService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AfterLoginTrialRestore
{
    public function __construct(
        private readonly TrialHandoffService $trials,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! auth()->check()) {
            return $response;
        }

        if (! $this->trials->hasPendingTrialInSession()) {
            return $response;
        }

        try {
            $user = auth()->user();
            $restored = $this->trials->restoreFromSession($user);
            if ($restored && method_exists($this->trials, 'persistRestoredToUser')) {
                $this->trials->persistRestoredToUser($user, $restored);
            }
        } catch (\Throwable) {
            $this->trials->clearSession();
        }

        return $response;
    }
}
