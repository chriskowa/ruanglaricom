<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marketplace\MarketplaceConsignmentIntake;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketplaceConsignmentController extends Controller
{
    public function index()
    {
        $intakes = MarketplaceConsignmentIntake::with(['product.primaryImage', 'seller'])
            ->latest()
            ->paginate(20);

        return view('admin.marketplace.consignments.index', compact('intakes'));
    }

    public function markReceived(MarketplaceConsignmentIntake $intake)
    {
        DB::transaction(function () use ($intake) {
            $intake->status = 'received';
            $intake->received_at = now();
            $intake->processed_by = Auth::id();
            $intake->save();

            $product = $intake->product()->lockForUpdate()->first();
            if ($product) {
                $product->consignment_status = 'received';
                $product->is_active = false;
                $product->save();
            }
        }, 3);

        return back()->with('success', 'Consignment marked as received.');
    }

    public function markListed(MarketplaceConsignmentIntake $intake)
    {
        DB::transaction(function () use ($intake) {
            $intake->status = 'listed';
            $intake->listed_at = now();
            $intake->processed_by = Auth::id();
            $intake->save();

            $product = $intake->product()->lockForUpdate()->first();
            if ($product) {
                $product->consignment_status = 'listed';
                $product->is_active = true;
                $product->save();
            }
        }, 3);

        return back()->with('success', 'Consignment listed and published.');
    }
}

