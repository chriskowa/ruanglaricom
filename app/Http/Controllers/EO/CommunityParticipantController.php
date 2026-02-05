<?php

namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPaidEventTransaction;
use App\Models\CommunityRegistration;
use App\Models\Event;
use App\Models\Participant;
use App\Models\RaceCategory;
use App\Models\Transaction;
use App\Services\CommunityPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommunityParticipantController extends Controller
{
    public function index()
    {
        $events = Event::query()
            ->where('user_id', auth()->id())
            ->orderByDesc('id')
            ->get(['id', 'name', 'slug', 'start_at', 'status']);

        $counts = CommunityRegistration::query()
            ->whereIn('event_id', $events->pluck('id')->all())
            ->selectRaw('event_id, COUNT(*) as total, SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid')
            ->groupBy('event_id')
            ->get()
            ->keyBy('event_id');

        return view('eo.community.index', [
            'events' => $events,
            'counts' => $counts,
        ]);
    }

    public function show(Event $event)
    {
        $this->authorizeEvent($event);

        $registrations = CommunityRegistration::query()
            ->where('event_id', $event->id)
            ->withCount('participants')
            ->with(['invoices.transaction'])
            ->orderByDesc('id')
            ->paginate(20);

        return view('eo.community.show', [
            'event' => $event,
            'registrations' => $registrations,
        ]);
    }

    public function import(Request $request, Event $event, CommunityRegistration $registration, CommunityPricingService $pricingService)
    {
        $this->authorizeEvent($event);
        if ((int) $registration->event_id !== (int) $event->id) {
            abort(404);
        }

        if ($registration->imported_at) {
            return back()->with('error', 'Registrasi komunitas ini sudah pernah diimport.');
        }

        $invoice = $registration->invoices()
            ->whereNotNull('transaction_id')
            ->latest()
            ->with('transaction')
            ->first();

        if (! $invoice || ! $invoice->transaction) {
            return back()->with('error', 'Invoice tidak ditemukan.');
        }

        if (($invoice->transaction->payment_status ?? null) !== 'paid') {
            return back()->with('error', 'Pembayaran belum PAID.');
        }

        $participants = $registration->participants()->get();
        if ($participants->isEmpty()) {
            return back()->with('error', 'Tidak ada peserta untuk diimport.');
        }

        $categoryIds = $participants->pluck('race_category_id')->filter()->unique()->values()->all();
        $categories = RaceCategory::query()->whereIn('id', $categoryIds)->get()->keyBy('id');

        $participantEmails = $participants->pluck('email')->filter()->map(fn ($e) => strtolower(trim((string) $e)))->all();
        $participantIdCards = $participants->pluck('id_card')->filter()->map(fn ($v) => trim((string) $v))->all();

        $duplicateEmails = Participant::query()
            ->whereIn('email', $participantEmails)
            ->whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            })
            ->limit(10)
            ->pluck('email')
            ->all();

        if (! empty($duplicateEmails)) {
            return back()->with('error', 'Ada email peserta yang sudah terdaftar di event ini.');
        }

        $duplicateIdCards = Participant::query()
            ->whereIn('id_card', $participantIdCards)
            ->whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            })
            ->limit(10)
            ->pluck('id_card')
            ->all();

        if (! empty($duplicateIdCards)) {
            return back()->with('error', 'Ada ID Card peserta yang sudah terdaftar di event ini.');
        }

        $byCategory = [];
        foreach ($participants as $p) {
            $cid = (int) ($p->race_category_id ?? 0);
            if ($cid <= 0) {
                return back()->with('error', 'Ada peserta tanpa kategori.');
            }
            $byCategory[$cid] = ($byCategory[$cid] ?? 0) + 1;
        }

        try {
            $transaction = DB::transaction(function () use ($event, $registration, $invoice, $participants, $byCategory, $categories, $pricingService) {
                foreach ($byCategory as $categoryId => $qty) {
                    $category = RaceCategory::lockForUpdate()->findOrFail($categoryId);
                    if ((int) $category->event_id !== (int) $event->id) {
                        throw new \RuntimeException('Kategori tidak valid untuk event ini.');
                    }

                    $registeredCount = Participant::where('race_category_id', $categoryId)
                        ->whereHas('transaction', function ($query) {
                            $query->whereIn('payment_status', ['paid', 'cod']);
                        })
                        ->count();

                    $remainingQuota = $category->quota ? ($category->quota - $registeredCount) : 999999;
                    if ($category->quota && $remainingQuota < $qty) {
                        throw new \RuntimeException("Kuota kategori '{$category->name}' tidak mencukupi. Sisa: {$remainingQuota}");
                    }
                }

                $tx = Transaction::create([
                    'event_id' => $event->id,
                    'user_id' => auth()->id(),
                    'pic_data' => [
                        'name' => $registration->pic_name,
                        'email' => $registration->pic_email,
                        'phone' => $registration->pic_phone,
                        'community_registration_id' => $registration->id,
                        'community_name' => $registration->community_name,
                        'manual_entry' => true,
                    ],
                    'total_original' => (float) ($invoice->total_original ?? 0),
                    'coupon_id' => null,
                    'discount_amount' => (float) ($invoice->discount_amount ?? 0),
                    'admin_fee' => 0,
                    'final_amount' => (float) ($invoice->final_amount ?? 0),
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'payment_gateway' => 'community_import',
                    'unique_code' => 0,
                    'payment_channel' => 'community',
                ]);

                foreach ($participants as $p) {
                    $email = strtolower(trim((string) $p->email));
                    $category = $categories[$p->race_category_id] ?? null;
                    $priceType = 'regular';
                    if ($category) {
                        $info = $pricingService->getCategoryPrice($category);
                        $priceType = (string) ($info['type'] ?? 'regular');
                    }

                    \App\Models\Participant::create([
                        'transaction_id' => $tx->id,
                        'event_package_id' => null,
                        'race_category_id' => $p->race_category_id,
                        'name' => $p->name,
                        'gender' => $p->gender,
                        'phone' => $p->phone,
                        'email' => $email,
                        'id_card' => $p->id_card,
                        'address' => $p->address,
                        'emergency_contact_name' => $p->emergency_contact_name,
                        'emergency_contact_number' => $p->emergency_contact_number,
                        'date_of_birth' => $p->date_of_birth,
                        'target_time' => null,
                        'jersey_size' => $p->jersey_size,
                        'addons' => [],
                        'status' => 'pending',
                        'is_picked_up' => false,
                        'price_type' => $priceType,
                    ]);
                }

                $registration->update([
                    'imported_at' => now(),
                ]);

                return $tx;
            }, 3);

            ProcessPaidEventTransaction::dispatch($transaction);

            return back()->with('success', 'Peserta komunitas berhasil diimport dan email diproses via queue.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    protected function authorizeEvent(Event $event): void
    {
        if ((int) $event->user_id !== (int) auth()->id()) {
            abort(403);
        }
    }
}
