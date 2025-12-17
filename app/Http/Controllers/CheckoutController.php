<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    /**
     * Show checkout page
     */
    public function index()
    {
        $user = Auth::user();
        $cartItems = Cart::where('user_id', $user->id)
            ->with('program.coach')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong.');
        }

        $subtotal = $cartItems->sum('subtotal');
        $tax = 0;
        $total = $subtotal + $tax;

        // Get user wallet balance
        $walletBalance = $user->wallet ? $user->wallet->balance : 0;

        return view('checkout.index', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'walletBalance' => $walletBalance,
        ]);
    }

    /**
     * Process checkout
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:wallet,midtrans',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        
        \Log::info('Checkout started', [
            'user_id' => $user->id,
            'payment_method' => $validated['payment_method'],
        ]);

        // Ensure user has wallet
        if (!$user->wallet) {
            $wallet = \App\Models\Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'locked_balance' => 0,
            ]);
            $user->update(['wallet_id' => $wallet->id]);
            $user->refresh();
        }

        $cartItems = Cart::where('user_id', $user->id)
            ->with('program')
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Keranjang kosong.');
        }

        $subtotal = $cartItems->sum('subtotal');
        $tax = 0;
        $total = $subtotal + $tax;

        DB::beginTransaction();
        try {
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'program_id' => $cartItem->program_id,
                    'program_title' => $cartItem->program->title,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'subtotal' => $cartItem->subtotal,
                ]);
            }

            // Process payment
            if ($validated['payment_method'] === 'wallet') {
                // Refresh wallet to get latest balance
                $user->wallet->refresh();
                
                // Check wallet balance
                if ($user->wallet->balance < $total) {
                    DB::rollBack();
                    return back()->withErrors(['error' => 'Saldo wallet tidak cukup. Saldo Anda: Rp ' . number_format($user->wallet->balance, 0, ',', '.') . ', Diperlukan: Rp ' . number_format($total, 0, ',', '.')]);
                }

                // Deduct from wallet
                $balanceBefore = $user->wallet->balance;
                $user->wallet->decrement('balance', $total);
                $balanceAfter = $user->wallet->fresh()->balance;

                // Create wallet transaction
                WalletTransaction::create([
                    'wallet_id' => $user->wallet->id,
                    'type' => 'withdraw',
                    'amount' => $total,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'status' => 'completed',
                    'description' => 'Pembelian program: ' . $order->order_number,
                    'reference_id' => $order->id,
                    'reference_type' => Order::class,
                    'processed_at' => now(),
                ]);

                // Mark order as paid and completed
                $order->markAsPaid();
                $order->markAsCompleted();

                // Enroll user in programs (check if already enrolled)
                foreach ($cartItems as $cartItem) {
                    $program = $cartItem->program;
                    
                    // Check if user already enrolled in this program
                    $existingEnrollment = ProgramEnrollment::where('program_id', $program->id)
                        ->where('runner_id', $user->id)
                        ->where('status', '!=', 'cancelled')
                        ->first();

                    if (!$existingEnrollment) {
                        // Calculate end date
                        $endDate = Carbon::today()->addWeeks($program->duration_weeks ?? 12);

                        ProgramEnrollment::create([
                            'program_id' => $program->id,
                            'runner_id' => $user->id,
                            'start_date' => Carbon::today(),
                            'end_date' => $endDate,
                            'status' => 'active',
                            'payment_status' => 'paid',
                        ]);

                        // Update program enrolled count
                        $program->increment('enrolled_count');
                    }
                }

                // Clear cart
                Cart::where('user_id', $user->id)->delete();

                DB::commit();
                
                \Log::info('Checkout successful', [
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);
                
                // Redirect to invoice page
                return redirect()->route('marketplace.orders.show', $order->id)
                    ->with('success', 'Pembelian berhasil! Program telah ditambahkan ke kalender Anda.');

            } else {
                // Midtrans payment (TODO: implement)
                DB::rollBack();
                return back()->withErrors(['error' => 'Pembayaran Midtrans belum tersedia.']);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Checkout error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return back()->with('error', 'Gagal memproses checkout: ' . $e->getMessage())
                ->withErrors(['error' => 'Gagal memproses checkout: ' . $e->getMessage()]);
        }
    }
}
