<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSettings;
use Illuminate\Http\Request;

class MarketplaceSettingsController extends Controller
{
    public function index()
    {
        $commission = AppSettings::get('marketplace_commission_percentage', 1);
        $consignmentFee = AppSettings::get('marketplace_consignment_fee_percentage', 0);

        return view('admin.marketplace.settings', compact('commission', 'consignmentFee'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'commission' => 'required|numeric|min:0|max:100',
            'consignment_fee' => 'nullable|numeric|min:0|max:100',
        ]);

        AppSettings::set('marketplace_commission_percentage', $request->commission);
        AppSettings::set('marketplace_consignment_fee_percentage', $request->consignment_fee ?? 0);

        return back()->with('success', 'Commission percentage updated successfully.');
    }
}
