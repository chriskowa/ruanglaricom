<?php

namespace App\Http\Middleware;

use App\Models\PersonalAccessToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Token API tidak ditemukan. Harap sertakan Bearer Token pada Header Authorization.',
            ], 401);
        }

        $tokenRecord = PersonalAccessToken::findToken($token);

        if (! $tokenRecord || ! $tokenRecord->tokenable) {
            return response()->json([
                'success' => false,
                'message' => 'Token API tidak valid atau telah kadaluarsa.',
            ], 401);
        }

        if ($tokenRecord->expires_at && $tokenRecord->expires_at->isPast()) {
            $tokenRecord->delete();

            return response()->json([
                'success' => false,
                'message' => 'Token API telah kadaluarsa. Silakan login kembali.',
            ], 401);
        }

        // Touch last used timestamp
        $tokenRecord->forceFill(['last_used_at' => now()])->save();

        $user = $tokenRecord->tokenable;

        auth()->setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization');
        if ($header && str_starts_with($header, 'Bearer ')) {
            return trim(substr($header, 7));
        }

        return $request->query('api_token') ?: $request->query('token');
    }
}
