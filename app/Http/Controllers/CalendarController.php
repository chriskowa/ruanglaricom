<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{
    public function index()
    {
        return view('calendar.index');
    }

    public function getAiAnalysis(Request $request)
    {
        $data = $request->input('data');

        $prompt = 'You are a professional running coach. Analyze the following weekly running data: '.json_encode($data).". Provide a concise summary of performance and 1 specific actionable tip for next week. Keep it under 100 words. Speak in Bahasa Indonesia style 'Coach Gaul'.";

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=AIzaSyBkGYYIr1MPrbqQsBijXb9s_w8gQ--Lx_w', [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('AI Analysis Failed', ['status' => $response->status(), 'body' => $response->body()]);

            return response()->json([
                'error' => 'AI Service Unavailable',
                'details' => $response->json() ?? $response->body(),
                'upstream_status' => $response->status(),
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('AI Analysis Exception', ['message' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getEvents()
    {
        try {
            // Fetch events from RuangLari
            $response = Http::withoutVerifying()->withHeaders([
                'ruangLariKey' => 'Thinkpadx390',
            ])->get('https://ruanglari.com/wp-json/ruanglari/v1/events');

            if ($response->successful()) {
                return $response->json();
            }

            return response()->json(['error' => 'Failed to fetch events from source'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function stravaConnect()
    {
        $clientId = \App\Models\Admin\StravaConfig::first()->client_id ?? env('STRAVA_CLIENT_ID');
        
        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => route('calendar.strava.callback'),
            'response_type' => 'code',
            'scope' => 'activity:read_all,profile:read_all',
            'approval_prompt' => 'auto',
        ]);

        return redirect('https://www.strava.com/oauth/authorize?'.$query);
    }

    public function stravaCallback(Request $request)
    {
        if (! $request->has('code')) {
            return redirect()->route('calendar.public')->with('error', 'Authorization failed');
        }

        try {
            $config = \App\Models\Admin\StravaConfig::first();
            $clientId = $config->client_id ?? env('STRAVA_CLIENT_ID');
            $clientSecret = $config->client_secret ?? env('STRAVA_CLIENT_SECRET');

            $response = Http::withoutVerifying()->post('https://www.strava.com/oauth/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $request->code,
                'grant_type' => 'authorization_code',
            ]);

            if ($response->successful()) {
                $tokenData = $response->json();

                // Save to Authenticated User (if logged in)
                if (auth()->check()) {
                    $user = auth()->user();
                    $user->update([
                        'strava_id' => $tokenData['athlete']['id'] ?? null,
                        'strava_access_token' => $tokenData['access_token'],
                        'strava_refresh_token' => $tokenData['refresh_token'],
                        'strava_expires_at' => now()->addSeconds($tokenData['expires_in']),
                    ]);
                }

                // Return a view that saves to localStorage and closes/redirects
                return view('calendar.strava-callback', ['tokenData' => $tokenData]);
            }

            $errorMessage = 'Token exchange failed';
            $body = $response->json();
            if (isset($body['message'])) {
                $errorMessage .= ': '.$body['message'];
            }
            if (isset($body['errors'])) {
                $errorMessage .= ' ('.json_encode($body['errors']).')';
            }

            return redirect()->route('calendar.public')->with('error', $errorMessage);

        } catch (\Exception $e) {
            return redirect()->route('calendar.public')->with('error', 'Connection error: '.$e->getMessage());
        }
    }
}
