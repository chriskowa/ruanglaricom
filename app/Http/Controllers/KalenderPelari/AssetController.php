<?php

namespace App\Http\Controllers\KalenderPelari;

use App\Http\Controllers\Controller;
use App\Services\KalenderPelari\AssetLifecycleService;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function __construct(
        private readonly AssetLifecycleService $lifecycle,
    ) {}

    public function markLocalOnly(Request $request)
    {
        $valid = $request->validate([
            'client_refs' => ['required', 'array', 'max:64'],
            'client_refs.*' => ['string', 'max:200'],
        ]);

        $statuses = [];
        foreach ($valid['client_refs'] as $ref) {
            $statuses[$ref] = [
                'status' => 'LOCAL_ONLY',
                'expires_at' => now()->addMinutes(config('kalender-pelari.trial_expire_minutes', 60 * 24 * 7))->toIso8601String(),
            ];
        }

        return response()->json([
            'success' => true,
            'refs' => $statuses,
        ]);
    }
}
