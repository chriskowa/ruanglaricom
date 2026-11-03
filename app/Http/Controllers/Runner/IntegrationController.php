<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\StravaActivity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    /**
     * Display the runner's connected accounts and integrations overview.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $lastStravaActivity = StravaActivity::query()
            ->where('user_id', $user->id)
            ->latest('start_date')
            ->first();

        $totalStravaSynced = StravaActivity::query()
            ->where('user_id', $user->id)
            ->count();

        $stravaData = $this->buildStravaData($user, $totalStravaSynced, $lastStravaActivity);
        $emailData = $this->buildEmailData($user);
        $waData = $this->buildWhatsAppData($user);
        $googleData = $this->buildGoogleData($user);

        return view('runner.integrations.index', compact(
            'user',
            'stravaData',
            'emailData',
            'waData',
            'googleData'
        ));
    }

    /**
     * Build Strava connection payload for view/API.
     */
    public function buildStravaData(User $user, int $syncedCount = 0, ?StravaActivity $lastActivity = null): array
    {
        $hasStravaToken = ! empty($user->strava_access_token) || ! empty($user->strava_id);
        $stravaExpiresAt = null;
        $stravaIsExpired = false;

        if ($user->strava_expires_at) {
            try {
                $stravaExpiresAt = Carbon::parse($user->strava_expires_at)->setTimezone(config('app.timezone'));
                $stravaIsExpired = $stravaExpiresAt->lte(now());
            } catch (\Throwable $e) {
                $stravaExpiresAt = null;
            }
        }

        return [
            'is_connected' => $hasStravaToken,
            'athlete_id' => $user->strava_id,
            'athlete_url' => $user->strava_url ?: ($user->strava_id ? 'https://www.strava.com/athletes/'.$user->strava_id : null),
            'expires_at' => $stravaExpiresAt,
            'is_expired' => $stravaIsExpired,
            'synced_count' => $syncedCount,
            'last_activity' => $lastActivity,
        ];
    }

    /**
     * Build Email connection payload for view/API.
     */
    public function buildEmailData(User $user): array
    {
        $emailVerifiedAt = null;
        if ($user->email_verified_at) {
            try {
                $emailVerifiedAt = Carbon::parse($user->email_verified_at)->setTimezone(config('app.timezone'));
            } catch (\Throwable $e) {
                $emailVerifiedAt = null;
            }
        }

        return [
            'address' => $user->email,
            'is_verified' => ! is_null($user->email_verified_at),
            'verified_at' => $emailVerifiedAt,
            'created_at' => $user->created_at ? Carbon::parse($user->created_at)->setTimezone(config('app.timezone')) : null,
        ];
    }

    /**
     * Build WhatsApp connection payload for view/API.
     */
    public function buildWhatsAppData(User $user): array
    {
        return [
            'phone' => $user->phone,
            'has_phone' => ! empty($user->phone),
            'is_receive_wa' => (bool) $user->is_receive_wa,
            'wa_subscription_expires_at' => $user->wa_subscription_expires_at,
        ];
    }

    /**
     * Build Google Calendar connection payload for view/API.
     */
    public function buildGoogleData(User $user): array
    {
        return [
            'has_calendar_token' => ! empty($user->google_calendar_token),
        ];
    }

    /**
     * Send email verification notification to runner.
     */
    public function sendVerificationEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('runner.integrations.index')->with('info', 'Alamat email Anda sudah terverifikasi.');
        }

        try {
            $user->sendEmailVerificationNotification();
            return redirect()->route('runner.integrations.index')->with('success', 'Tautan verifikasi email telah dikirim ke ' . $user->email . '. Silakan periksa kotak masuk atau spam.');
        } catch (\Throwable $e) {
            return redirect()->route('runner.integrations.index')->with('error', 'Gagal mengirim email verifikasi: ' . $e->getMessage());
        }
    }
}
