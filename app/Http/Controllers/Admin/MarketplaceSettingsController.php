<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSettings;

class MarketplaceSettingsController extends Controller
{
    public function index()
    {
        $commission = AppSettings::get('marketplace_commission_percentage', 1);
        return view('admin.marketplace.settings', compact('commission'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'commission' => 'required|numeric|min:0|max:100',
        ]);

        AppSettings::set('marketplace_commission_percentage', $request->commission);

        return back()->with('success', 'Commission percentage updated successfully.');
    }
}
