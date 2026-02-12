<?php

namespace App\Services;

use App\Models\Admin\StravaConfig;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class StravaApiService
{
    public function getValidAccessToken(User $user): ?string
    {
        if (! $user->strava_access_token || ! $user->strava_refresh_token) {
            return null;
        }

        $config = StravaConfig::first();
        $clientId = $config->client_id ?? env('STRAVA_CLIENT_ID');
        $clientSecret = $config->client_secret ?? env('STRAVA_CLIENT_SECRET');
        if (! $clientId || ! $clientSecret) {
            return null;
        }

        $accessToken = $user->strava_access_token;
        if ($user->strava_expires_at && $user->strava_expires_at->lte(now()->addMinute())) {
            $refresh = Http::withoutVerifying()->post('https://www.strava.com/oauth/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $user->strava_refresh_token,
            ]);

            if (! $refresh->successful()) {
                return null;
            }

            $tokenData = $refresh->json();
            $accessToken = data_get($tokenData, 'access_token');
            if (! $accessToken) {
                return null;
            }

            $user->update([
                'strava_access_token' => $accessToken,
                'strava_refresh_token' => data_get($tokenData, 'refresh_token', $user->strava_refresh_token),
                'strava_expires_at' => now()->addSeconds((int) data_get($tokenData, 'expires_in', 0)),
            ]);
        }

        return $accessToken;
    }

    public function fetchActivityDetails(User $user, int|string $activityId): ?array
    {
        $accessToken = $this->getValidAccessToken($user);
        if (! $accessToken) {
            return null;
        }

        $res = Http::withoutVerifying()
            ->withToken($accessToken)
            ->get('https://www.strava.com/api/v3/activities/'.rawurlencode((string) $activityId), [
                'include_all_efforts' => true,
            ]);

        if (! $res->successful()) {
            return null;
        }

        $json = $res->json();

        return is_array($json) ? $json : null;
    }

    public function fetchActivityStreams(User $user, int|string $activityId, array $keys = ['time', 'heartrate', 'cadence', 'velocity_smooth', 'watts']): ?array
    {
        $accessToken = $this->getValidAccessToken($user);
        if (! $accessToken) {
            return null;
        }

        $res = Http::withoutVerifying()
            ->withToken($accessToken)
            ->get('https://www.strava.com/api/v3/activities/'.rawurlencode((string) $activityId).'/streams', [
                'keys' => implode(',', $keys),
                'key_by_type' => 'true',
            ]);

        if (! $res->successful()) {
            return null;
        }

        $json = $res->json();

        return is_array($json) ? $json : null;
    }

    public function formatPaceFromSpeed($mps): ?string
    {
        $speed = is_numeric($mps) ? (float) $mps : 0.0;
        if ($speed <= 0) {
            return null;
        }

        $secPerKm = 1000 / $speed;
        $mins = (int) floor($secPerKm / 60);
        $secs = (int) round($secPerKm - ($mins * 60));
        if ($secs === 60) {
            $mins += 1;
            $secs = 0;
        }

        return $mins.':'.str_pad((string) $secs, 2, '0', STR_PAD_LEFT);
    }
}
