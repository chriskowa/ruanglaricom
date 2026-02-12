<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\StravaConfig;
use Illuminate\Http\Request;

class StravaConfigController extends Controller
{
    public function index()
    {
        $config = StravaConfig::firstOrNew();

        return view('admin.strava.config', compact('config'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'club_id' => 'required|string',
            'refresh_token' => 'required|string',
        ]);

        $config = StravaConfig::firstOrNew();
        $config->fill($data);

        // If refresh token changed or access token is missing, we might want to clear access token to force refresh
        // But for manual input, we just save what we have.
        // We'll rely on the service to refresh the token using these credentials.

        $config->save();

        return redirect()->back()->with('success', 'Konfigurasi Strava berhasil disimpan.');
    }
}
