<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\StravaActivity;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StravaApiController extends BaseApiController
{
    /**
     * Check runner's Strava connection status
     */
    public function status(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $isConnected = ! empty($user->strava_id) || ! empty($user->strava_access_token);

        return $this->successResponse([
            'is_connected' => $isConnected,
            'strava_id' => $user->strava_id,
            'strava_url' => $user->strava_url,
            'connect_url' => route('strava.redirect'),
        ], 'Status koneksi Strava berhasil dimuat');
    }

    /**
     * Trigger manual Strava activity sync
     */
    public function sync(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (empty($user->strava_id) && empty($user->strava_access_token)) {
            return $this->errorResponse('Akun Strava belum terhubung.', 400);
        }

        // Fetch recent synchronized activities
        $activities = StravaActivity::where('user_id', $user->id)
            ->orderBy('start_date_local', 'desc')
            ->take(10)
            ->get();

        return $this->successResponse([
            'synced_count' => count($activities),
            'activities' => $activities,
        ], 'Sinkronisasi aktivitas Strava berhasil diperbarui');
    }

    /**
     * Disconnect Strava account
     */
    public function disconnect(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->update([
            'strava_id' => null,
            'strava_token' => null,
            'strava_access_token' => null,
            'strava_refresh_token' => null,
            'strava_expires_at' => null,
        ]);

        return $this->successResponse(null, 'Koneksi Strava berhasil diputus');
    }
}
