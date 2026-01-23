<?php

namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Models\MembershipTransaction;
use App\Models\Package;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MembershipController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function index()
    {
        $packages = Package::where('is_active', true)->get();
        return view('eo.membership.packages', compact('packages'));
    }

    public function payment(MembershipTransaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        if ($transaction->status === 'paid') {
            return redirect()->route('eo.dashboard')->with('success', 'Membership sudah aktif.');
        }

        // Generate Snap Token if not exists
        if (!$transaction->snap_token) {
            $result = $this->midtransService->createMembershipTransaction($transaction);
            
            if (!$result['success']) {
                return back()->with('error', 'Gagal memproses pembayaran: ' . ($result['message'] ?? 'Unknown error'));
            }
            
            // Refresh transaction to get the saved token
            $transaction->refresh();
        }

        return view('eo.membership.payment', compact('transaction'));
    }

    public function selectPackage(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id'
        ]);

        $package = Package::findOrFail($request->package_id);
        
        // Create Transaction
        $transaction = MembershipTransaction::create([
            'user_id' => Auth::id(),
            'package_id' => $package->id,
            'amount' => $package->price,
            'total_amount' => $package->price,
            'status' => 'pending'
        ]);

        return redirect()->route('eo.membership.payment', $transaction->id);
    }
}
