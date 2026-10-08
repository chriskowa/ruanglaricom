<?php

namespace App\Http\Controllers;

use App\Actions\Events\StoreRegistrationAction;
use App\Models\AppSettings;
use App\Models\Coupon;
use App\Models\Event;
use App\Services\EventCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventRegistrationController extends Controller
{
    protected $cacheService;

    protected $storeAction;

    public function __construct(EventCacheService $cacheService, StoreRegistrationAction $storeAction)
    {
        $this->cacheService = $cacheService;
        $this->storeAction = $storeAction;
    }

    /**
     * Show registration form - redirect to event show page
     * Form is now inline in show.blade.php
     */
    public function show($slug)
    {
        return redirect()->route('events.show', $slug)->with('show_form', true);
    }

    /**
     * Apply coupon code
     */
    public function applyCoupon(Request $request)
    {
        try {
            \Illuminate\Support\Facades\Log::info('Apply Coupon Request', [
                'event_id' => $request->event_id,
                'code' => $request->coupon_code,
                'total_amount' => $request->total_amount,
                'user_id' => auth()->id(),
            ]);

            $validated = $request->validate([
                'event_id' => 'required|exists:events,id',
                'coupon_code' => 'required|string',
                'total_amount' => 'required|numeric|min:0',
            ]);

            $coupon = Coupon::where('code', $validated['coupon_code'])
                ->where(function ($query) use ($validated) {
                    $query->where('event_id', $validated['event_id'])
                        ->orWhereNull('event_id');
                })
                ->first();

            if (! $coupon) {
                \Illuminate\Support\Facades\Log::warning('Coupon not found', ['code' => $validated['coupon_code']]);

                return response()->json([
                    'success' => false,
                    'message' => 'Kode kupon tidak ditemukan',
                ], 404);
            }

            if (! $coupon->canBeUsed($validated['event_id'], $validated['total_amount'], auth()->id())) {
                \Illuminate\Support\Facades\Log::warning('Coupon invalid condition', [
                    'code' => $coupon->code,
                    'reason' => 'Failed canBeUsed check',
                    'min_trx' => $coupon->min_transaction_amount ?? 0,
                    'request_amount' => $validated['total_amount'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Kupon tidak valid atau syarat minimum transaksi tidak terpenuhi',
                ], 400);
            }

            $discountAmount = $coupon->applyDiscount($validated['total_amount']);
            $finalAmount = $validated['total_amount'] - $discountAmount;

            \Illuminate\Support\Facades\Log::info('Coupon Applied Successfully', [
                'code' => $coupon->code,
                'discount' => $discountAmount,
                'final' => $finalAmount,
            ]);

            return response()->json([
                'success' => true,
                'original_price' => $validated['total_amount'],
                'discount_amount' => $discountAmount,
                'final_price' => $finalAmount,
                'final_amount' => $finalAmount, // Keep backward compatibility
                'coupon' => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                ],
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Apply Coupon Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check quota for categories
     */
    public function checkQuota(Request $request, $slug)
    {
        $validated = $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:race_categories,id',
        ]);

        $quotas = [];
        foreach ($validated['category_ids'] as $categoryId) {
            $category = \App\Models\RaceCategory::find($categoryId);
            if ($category) {
                // Count registered participants
                $registeredCount = \App\Models\Participant::where('race_category_id', $categoryId)
                    ->whereHas('transaction', function ($query) {
                        $query->whereIn('payment_status', ['paid', 'cod']);
                    })
                    ->count();

                $remainingQuota = $category->quota ? ($category->quota - $registeredCount) : 999999;

                $quotas[$categoryId] = [
                    'remaining_quota' => max(0, $remainingQuota),
                    'is_sold_out' => $category->quota && $remainingQuota <= 0,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'quotas' => $quotas,
        ]);
    }

    /**
     * Show payment instruction page
     */
    public function payment($slug, \App\Models\Transaction $transaction)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        // Security check: ensure transaction belongs to this event
        if ($transaction->event_id !== $event->id) {
            abort(404);
        }

        // Ensure transaction is moota or manual_transfer and pending
        if (! in_array($transaction->payment_gateway, ['moota', 'manual_transfer'], true) || $transaction->payment_status !== 'pending') {
            return redirect()->route('events.show', $slug)->with('info', 'Transaksi tidak valid atau sudah dibayar.');
        }

        $isManualTransfer = $transaction->payment_gateway === 'manual_transfer';
        $bankAccounts = [];
        $instructions = '';

        if ($isManualTransfer) {
            $manualBank = $event->payment_config['manual_bank'] ?? [];
            if (! empty($manualBank['bank_name']) && ! empty($manualBank['account_number'])) {
                $bankAccounts = [[
                    'bank_type' => $manualBank['bank_name'],
                    'account_number' => $manualBank['account_number'],
                    'name' => $manualBank['account_name'] ?? 'Panitia Event',
                ]];
            } else {
                $eoUser = $event->user;
                $bankAccounts = [[
                    'bank_type' => $eoUser?->bank_account['bank_name'] ?? 'BCA',
                    'account_number' => $eoUser?->bank_account_number ?? ($eoUser?->bank_account['account_number'] ?? '-'),
                    'name' => $eoUser?->bank_account_name ?? ($eoUser?->bank_account['account_name'] ?? ($eoUser?->name ?? 'Panitia Event')),
                ]];
            }
            $instructions = $manualBank['instructions'] ?? 'Silakan transfer tepat sesuai nominal yang tertera (termasuk 3 digit kode unik). Setelah transfer berhasil, silakan unggah foto bukti transfer di bawah ini agar panitia dapat memverifikasi pendaftaran Anda.';
        } else {
            $bankAccounts = config('moota.bank_accounts');
            $instructions = AppSettings::get('moota_instructions');
        }

        return view('events.payment', [
            'event' => $event,
            'transaction' => $transaction,
            'bankAccounts' => $bankAccounts,
            'instructions' => $instructions,
            'isManualTransfer' => $isManualTransfer,
        ]);
    }

    /**
     * Store registration
     */
    public function store(Request $request, $slug)
    {
        \Illuminate\Support\Facades\Log::info('Registration Request Received', [
            'slug' => $slug,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_ajax' => $request->ajax(),
            'content_type' => $request->header('Content-Type'),
        ]);

        $event = Event::where('slug', $slug)->firstOrFail();

        $wantsJson = $request->expectsJson()
            || $request->ajax()
            || $request->wantsJson()
            || $request->header('Accept') === 'application/json'
            || \Illuminate\Support\Str::contains((string) $request->header('Accept'), 'json')
            || $request->header('X-Requested-With') === 'XMLHttpRequest';

        try {
            $transaction = $this->storeAction->execute($request, $event);

            // Handle Free Event / Zero Amount Registration
            if ($transaction->payment_gateway === 'free' || (float) ($transaction->final_amount ?? 0) <= 0) {
                // Case 1: Free Registration that requires approval (Challenge / Curated Selection)
                if ($transaction->payment_status === 'pending') {
                    if ($wantsJson) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Pendaftaran berhasil dikirim! Menunggu seleksi dan persetujuan (approval) panitia.',
                            'payment_gateway' => 'free',
                            'payment_status' => 'pending',
                            'final_amount' => 0,
                            'transaction_id' => $transaction->id,
                            'registration_id' => $transaction->public_ref,
                            'redirect_url' => route('events.show', $slug).'?payment=approval_pending&tx='.$transaction->id.'&ref='.$transaction->public_ref,
                        ]);
                    }

                    return redirect()->route('events.show', [
                        'slug' => $slug,
                        'payment' => 'approval_pending',
                        'tx' => $transaction->id,
                        'ref' => $transaction->public_ref,
                    ])->with('success', 'Pendaftaran berhasil dikirim! Menunggu seleksi dan persetujuan panitia.');
                }

                // Case 2: Free Registration that is automatically confirmed (No approval required)
                if ($wantsJson) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Pendaftaran berhasil dikonfirmasi!',
                        'payment_gateway' => 'free',
                        'payment_status' => 'paid',
                        'final_amount' => 0,
                        'transaction_id' => $transaction->id,
                        'registration_id' => $transaction->public_ref,
                        'redirect_url' => route('events.show', $slug).'?payment=success&tx='.$transaction->id.'&ref='.$transaction->public_ref,
                    ]);
                }

                return redirect()->route('events.show', [
                    'slug' => $slug,
                    'payment' => 'success',
                    'tx' => $transaction->id,
                    'ref' => $transaction->public_ref,
                ])->with('success', 'Pendaftaran berhasil dikonfirmasi!');
            }

            // Handle Moota & Manual Transfer Redirect
            if (in_array($transaction->payment_gateway, ['moota', 'manual_transfer'], true) && $transaction->payment_status === 'pending') {
                if ($wantsJson) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Registrasi berhasil! Silakan lakukan transfer pembayaran.',
                        'payment_gateway' => $transaction->payment_gateway,
                        'payment_status' => $transaction->payment_status,
                        'transaction_id' => $transaction->id,
                        'registration_id' => $transaction->public_ref,
                        'final_amount' => (float) ($transaction->final_amount ?? 0),
                        'unique_code' => (int) ($transaction->unique_code ?? 0),
                        'redirect_url' => route('events.payment', ['slug' => $slug, 'transaction' => $transaction->id]),
                    ]);
                }

                return redirect()->route('events.payment', ['slug' => $slug, 'transaction' => $transaction->id]);
            }

            // Handle COD success (no payment gateway redirect)
            if ($transaction->payment_gateway === 'cod') {
                if ($wantsJson) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Registrasi COD berhasil dikirim! Menunggu verifikasi dan persetujuan (approval) panitia.',
                        'payment_gateway' => 'cod',
                        'payment_status' => $transaction->payment_status,
                        'transaction_id' => $transaction->id,
                        'registration_id' => $transaction->public_ref,
                        'redirect_url' => route('events.show', $slug).'?payment=cod_pending',
                    ]);
                }

                return redirect()->route('events.show', $slug)->with('success', 'Registrasi COD berhasil dikirim! Menunggu persetujuan panitia.')->with('payment', 'cod_pending');
            }

            // If AJAX request, return JSON
            if ($wantsJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registrasi berhasil!',
                    'payment_gateway' => $transaction->payment_gateway,
                    'snap_token' => $transaction->snap_token,
                    'payment_url' => $transaction->midtrans_redirect_url ?? ($transaction->snap_token ? ('https://app.midtrans.com/snap/v2/vtweb/'.$transaction->snap_token) : null),
                    'redirect_url' => $transaction->midtrans_redirect_url ?? (route('events.show', $slug).'?payment=pending&tx='.$transaction->id.'&ref='.$transaction->public_ref),
                    'transaction_id' => $transaction->id,
                    'registration_id' => $transaction->public_ref,
                    'testing_mode' => config('midtrans.testing_mode', false),
                ]);
            }

            // Redirect back with success message and snap token
            return redirect()->route('events.show', $slug)
                ->with('success', 'Registrasi berhasil!')
                ->with('snap_token', $transaction->snap_token);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Data pendaftaran belum lengkap.';
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => $firstError,
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()->route('events.show', [
                'slug' => $slug,
                'payment' => 'failed',
                'error_message' => $firstError,
            ])
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            // If AJAX request, return JSON error
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'message' => $e->getMessage(),
                ], 400);
            }

            return redirect()->route('events.show', [
                'slug' => $slug,
                'payment' => 'failed',
                'error_message' => $e->getMessage(),
            ])
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Upload payment proof for manual transfer
     */
    public function uploadProof(Request $request, $slug, \App\Models\Transaction $transaction)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        if ((int) $transaction->event_id !== (int) $event->id) {
            abort(404);
        }

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $path = null;

        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $ext = strtolower($file->getClientOriginalExtension());
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'], true)) {
                $ext = 'jpg';
            }
            $filename = 'proof_' . $transaction->id . '_' . time() . '_' . Str::random(8) . '.' . $ext;
            $path = $file->storeAs('payment_proofs/' . $event->id, $filename, 'public');
        } elseif ($request->filled('payment_proof_base64')) {
            // Base64 compressed image from HTML5 canvas
            $image = $request->input('payment_proof_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
                $image = substr($image, strpos($image, ',') + 1);
                $type = strtolower($type[1]);
                if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    $image = str_replace(' ', '+', $image);
                    $filename = 'proof_' . $transaction->id . '_' . time() . '_' . Str::random(8) . '.' . $type;
                    Storage::disk('public')->put('payment_proofs/' . $event->id . '/' . $filename, base64_decode($image));
                    $path = 'payment_proofs/' . $event->id . '/' . $filename;
                }
            }
        }

        if (! $path) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan pilih berkas bukti transfer (foto resi atau screenshot m-banking).',
            ], 422);
        }

        // Delete previous proof if re-uploading
        if ($transaction->payment_proof && Storage::disk('public')->exists($transaction->payment_proof)) {
            try {
                Storage::disk('public')->delete($transaction->payment_proof);
            } catch (\Throwable $e) {}
        }

        $transaction->update([
            'payment_proof' => $path,
            'payment_proof_uploaded_at' => now(),
            'proof_notes' => $request->input('notes'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bukti pembayaran berhasil diunggah! Panitia sedang memverifikasi pembayaran Anda.',
            'proof_url' => asset('storage/' . $path),
            'uploaded_at' => $transaction->payment_proof_uploaded_at ? $transaction->payment_proof_uploaded_at->format('d M Y H:i') : now()->format('d M Y H:i'),
        ]);
    }
}
