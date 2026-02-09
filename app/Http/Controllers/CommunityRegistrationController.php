<?php

namespace App\Http\Controllers;

use App\Models\CommunityInvoice;
use App\Models\CommunityParticipant;
use App\Models\CommunityRegistration;
use App\Models\Event;
use App\Models\RaceCategory;
use App\Models\Transaction;
use App\Services\CommunityPricingService;
use App\Services\MootaService;
use App\Services\QrisDynamicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\Community;

class CommunityRegistrationController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::query()
            ->where('event_kind', 'managed')
            ->where('status', 'published')
            ->where('is_active', true)
            ->orderByRaw('COALESCE(start_at, created_at) ASC')
            ->get(['id', 'name', 'slug', 'start_at', 'location_name']);

        $communities = Community::query()
            ->orderBy('name')
            ->get(['id', 'name', 'pic_name', 'pic_email', 'pic_phone']);

        $selectedEventId = $request->query('eventId');
        if (!$selectedEventId && $request->query('slug')) {
            $eventBySlug = $events->firstWhere('slug', $request->query('slug'));
            if ($eventBySlug) {
                $selectedEventId = $eventBySlug->id;
            }
        }

        $eventItems = $events->map(function ($e) {
            $label = $e->name;
            if ($e->start_at) {
                $label .= ' • ' . $e->start_at->format('d M Y');
            }
            if ($e->location_name) {
                $label .= ' • ' . $e->location_name;
            }
            return [
                'id' => $e->id,
                'label' => $label,
            ];
        })->values();

        $initialEventId = request()->old('event_id', $selectedEventId ?? '');

        return view('community.index', [
            'events' => $events,
            'eventItems' => $eventItems,
            'communities' => $communities,
            'selectedEventId' => $selectedEventId,
            'initialEventId' => $initialEventId,
        ]);
    }

    public function start(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'community_id' => 'nullable|exists:communities,id',
            'community_name' => 'nullable|required_without:community_id|string|max:255',
            'pic_name' => 'nullable|required_without:community_id|string|max:255',
            'pic_email' => 'nullable|required_without:community_id|email|max:255',
            'pic_phone' => 'nullable|required_without:community_id|string|min:8|max:20',
        ]);

        $event = Event::query()
            ->where('event_kind', 'managed')
            ->where('status', 'published')
            ->where('is_active', true)
            ->whereKey($validated['event_id'])
            ->firstOrFail();

        $community = null;
        $snapshot = [];
        if (!empty($validated['community_id'])) {
            $community = Community::query()->whereKey($validated['community_id'])->firstOrFail();
            $snapshot = [
                'community_name' => $community->name,
                'pic_name' => (string) $community->pic_name,
                'pic_email' => (string) $community->pic_email,
                'pic_phone' => (string) $community->pic_phone,
            ];
        } else {
            $communityName = trim((string) $validated['community_name']);
            $picName = trim((string) $validated['pic_name']);
            $picEmail = strtolower(trim((string) $validated['pic_email']));
            $picPhone = trim((string) $validated['pic_phone']);

            $community = Community::query()
                ->whereRaw('LOWER(name) = ?', [strtolower($communityName)])
                ->orWhere('pic_email', $picEmail)
                ->first();

            if (!$community) {
                $community = Community::create([
                    'name' => $communityName,
                    'slug' => $this->generateUniqueCommunitySlug($communityName),
                    'pic_name' => $picName,
                    'pic_email' => $picEmail,
                    'pic_phone' => $picPhone,
                ]);
            } else {
                $community->update([
                    'name' => $communityName,
                    'pic_name' => $picName,
                    'pic_email' => $picEmail,
                    'pic_phone' => $picPhone,
                ]);
            }

            $snapshot = [
                'community_name' => $communityName,
                'pic_name' => $picName,
                'pic_email' => $picEmail,
                'pic_phone' => $picPhone,
            ];
        }

        $registration = CommunityRegistration::query()->firstOrCreate([
            'event_id' => $event->id,
            'community_id' => $community->id,
        ], array_merge($snapshot, [
            'status' => 'draft',
        ]));

        if (!$registration->wasRecentlyCreated && $registration->status === 'draft') {
            $registration->update($snapshot);
        }

        return redirect()->route('community.register.show', [
            'event' => $event->slug,
            'community' => $community->slug,
        ]);
    }

    public function show(Event $event, Community $community)
    {
        $registration = $this->getRegistration($event, $community);

        $categories = RaceCategory::query()
            ->where('event_id', $event->id)
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name', 'price_regular', 'price_early', 'price_late', 'early_bird_end_at', 'early_bird_quota']);

        $participants = $registration->participants()
            ->with(['category:id,name'])
            ->orderBy('id')
            ->get();

        return view('community.show', [
            'event' => $event,
            'registration' => $registration,
            'categories' => $categories,
            'participants' => $participants,
            'latestInvoice' => $registration->invoices()->latest()->first(),
            'community' => $community,
        ]);
    }

    public function legacyShow(Event $event, CommunityRegistration $registration)
    {
        if ((int) $registration->event_id !== (int) $event->id) {
            abort(404);
        }

        $community = $registration->community;
        if (!$community) {
            $communityName = trim((string) $registration->community_name);
            $picName = trim((string) $registration->pic_name);
            $picEmail = strtolower(trim((string) $registration->pic_email));
            $picPhone = trim((string) $registration->pic_phone);

            $community = Community::query()
                ->whereRaw('LOWER(name) = ?', [strtolower($communityName)])
                ->orWhere('pic_email', $picEmail)
                ->first();

            if (!$community) {
                $community = Community::create([
                    'name' => $communityName,
                    'slug' => $this->generateUniqueCommunitySlug($communityName),
                    'pic_name' => $picName,
                    'pic_email' => $picEmail,
                    'pic_phone' => $picPhone,
                ]);
            }

            $registration->update([
                'community_id' => $community->id,
            ]);
        }

        return redirect()->route('community.register.show', [
            'event' => $event->slug,
            'community' => $community->slug,
        ]);
    }

    public function updatePic(Request $request, Event $event, Community $community)
    {
        $registration = $this->getRegistration($event, $community);

        if ($registration->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Registrasi sudah dikunci (invoice sudah dibuat).',
            ], 409);
        }

        $validated = $request->validate([
            'community_name' => 'required|string|max:255',
            'pic_name' => 'required|string|max:255',
            'pic_email' => 'required|email|max:255',
            'pic_phone' => 'required|string|min:8|max:20',
        ]);

        $registration->update([
            'community_name' => trim((string) $validated['community_name']),
            'pic_name' => trim((string) $validated['pic_name']),
            'pic_email' => strtolower(trim((string) $validated['pic_email'])),
            'pic_phone' => trim((string) $validated['pic_phone']),
        ]);

        $community->update([
            'name' => trim((string) $validated['community_name']),
            'pic_name' => trim((string) $validated['pic_name']),
            'pic_email' => strtolower(trim((string) $validated['pic_email'])),
            'pic_phone' => trim((string) $validated['pic_phone']),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function listParticipants(Event $event, Community $community)
    {
        $registration = $this->getRegistration($event, $community);

        $items = $registration->participants()
            ->with(['category:id,name'])
            ->orderBy('id')
            ->get()
            ->map(function (CommunityParticipant $p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'email' => $p->email,
                    'phone' => $p->phone,
                    'id_card' => $p->id_card,
                    'address' => $p->address,
                    'gender' => $p->gender,
                    'category_id' => $p->race_category_id,
                    'category_name' => $p->category?->name,
                    'jersey_size' => $p->jersey_size,
                    'is_free' => (bool) $p->is_free,
                ];
            });

        return response()->json([
            'success' => true,
            'participants' => $items,
            'locked' => $registration->status !== 'draft',
        ]);
    }

    public function storeParticipant(Request $request, Event $event, Community $community)
    {
        $registration = $this->getRegistration($event, $community);

        if ($registration->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Registrasi sudah dikunci (invoice sudah dibuat).',
            ], 409);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'nullable|in:male,female',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|min:8|max:20',
            'id_card' => 'required|string|max:50',
            'address' => 'required|string|max:500',
            'race_category_id' => 'required|exists:race_categories,id',
            'date_of_birth' => 'nullable|date|before:today',
            'jersey_size' => 'nullable|string|max:10',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|min:8|max:20',
        ]);

        $category = RaceCategory::query()->whereKey((int) $validated['race_category_id'])->firstOrFail();
        if ((int) $category->event_id !== (int) $registration->event_id) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak valid untuk event ini.',
            ], 422);
        }

        $duplicateIdCard = $registration->participants()->where('id_card', $validated['id_card'])->exists();
        if ($duplicateIdCard) {
            return response()->json([
                'success' => false,
                'message' => 'ID Card sudah dipakai oleh peserta lain di komunitas ini.',
            ], 422);
        }

        $participant = CommunityParticipant::create([
            'community_registration_id' => $registration->id,
            'event_id' => $registration->event_id,
            'race_category_id' => (int) $validated['race_category_id'],
            'name' => trim((string) $validated['name']),
            'gender' => $validated['gender'] ?? null,
            'email' => strtolower(trim((string) $validated['email'])),
            'phone' => trim((string) $validated['phone']),
            'id_card' => trim((string) $validated['id_card']),
            'address' => trim((string) $validated['address']),
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'jersey_size' => $validated['jersey_size'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_number' => $validated['emergency_contact_number'] ?? null,
            'base_price' => 0,
            'is_free' => false,
            'final_price' => 0,
        ]);

        return response()->json([
            'success' => true,
            'participant_id' => $participant->id,
        ]);
    }

    public function updateParticipant(Request $request, Event $event, Community $community, CommunityParticipant $participant)
    {
        $registration = $this->getRegistration($event, $community);

        if ($registration->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Registrasi sudah dikunci (invoice sudah dibuat).',
            ], 409);
        }

        if ((int) $participant->community_registration_id !== (int) $registration->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'nullable|in:male,female',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|min:8|max:20',
            'id_card' => 'required|string|max:50',
            'address' => 'required|string|max:500',
            'race_category_id' => 'required|exists:race_categories,id',
            'date_of_birth' => 'nullable|date|before:today',
            'jersey_size' => 'nullable|string|max:10',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|min:8|max:20',
        ]);

        $category = RaceCategory::query()->whereKey((int) $validated['race_category_id'])->firstOrFail();
        if ((int) $category->event_id !== (int) $registration->event_id) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak valid untuk event ini.',
            ], 422);
        }

        $duplicateIdCard = $registration->participants()
            ->where('id_card', $validated['id_card'])
            ->where('id', '!=', $participant->id)
            ->exists();
        if ($duplicateIdCard) {
            return response()->json([
                'success' => false,
                'message' => 'ID Card sudah dipakai oleh peserta lain di komunitas ini.',
            ], 422);
        }

        $participant->update([
            'race_category_id' => (int) $validated['race_category_id'],
            'name' => trim((string) $validated['name']),
            'gender' => $validated['gender'] ?? null,
            'email' => strtolower(trim((string) $validated['email'])),
            'phone' => trim((string) $validated['phone']),
            'id_card' => trim((string) $validated['id_card']),
            'address' => trim((string) $validated['address']),
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'jersey_size' => $validated['jersey_size'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_number' => $validated['emergency_contact_number'] ?? null,
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function deleteParticipant(Event $event, Community $community, CommunityParticipant $participant)
    {
        $registration = $this->getRegistration($event, $community);

        if ($registration->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Registrasi sudah dikunci (invoice sudah dibuat).',
            ], 409);
        }

        if ((int) $participant->community_registration_id !== (int) $registration->id) {
            abort(404);
        }

        $participant->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function cancelInvoice(Event $event, Community $community)
    {
        $registration = $this->getRegistration($event, $community);

        if ($registration->status !== 'invoiced') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice tidak bisa dibatalkan pada status registrasi saat ini.',
            ], 409);
        }

        $invoice = $registration->invoices()
            ->with(['transaction'])
            ->latest()
            ->first();

        if (! $invoice || ! $invoice->transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice tidak ditemukan.',
            ], 404);
        }

        if ($invoice->status !== 'pending' || $invoice->transaction->payment_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice tidak bisa dibatalkan karena status pembayaran sudah berubah.',
            ], 409);
        }

        DB::transaction(function () use ($registration, $invoice) {
            $invoice->update([
                'status' => 'cancelled',
            ]);

            $invoice->transaction->update([
                'payment_status' => 'failed',
            ]);

            $registration->update([
                'status' => 'draft',
                'invoiced_at' => null,
            ]);
        }, 3);

        return response()->json([
            'success' => true,
        ]);
    }

    public function generateInvoice(
        Request $request,
        Event $event,
        Community $community,
        CommunityPricingService $pricingService,
        MootaService $mootaService,
        QrisDynamicService $qrisService
    ) {
        $registration = $this->getRegistration($event, $community);

        if ($registration->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice sudah dibuat untuk registrasi ini.',
            ], 409);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:moota,qris',
        ]);

        $participants = $registration->participants()->get();
        if ($participants->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tambahkan minimal 1 peserta.',
            ], 422);
        }

        $categoryIds = $participants->pluck('race_category_id')->filter()->unique()->values()->all();
        $categories = RaceCategory::query()
            ->whereIn('id', $categoryIds)
            ->get()
            ->keyBy('id');

        $priceRows = [];
        foreach ($participants as $p) {
            $cat = $p->race_category_id ? ($categories[$p->race_category_id] ?? null) : null;
            if (! $cat || (int) $cat->event_id !== (int) $event->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ada peserta dengan kategori tidak valid.',
                ], 422);
            }

            $info = $pricingService->getCategoryPrice($cat);
            $priceRows[] = [
                'id' => $p->id,
                'base' => (int) ($info['price'] ?? 0),
            ];
        }

        usort($priceRows, function ($a, $b) {
            return $a['base'] <=> $b['base'];
        });

        // Promo: Buy 10 Get 1 Free (Global for community registration)
        $freeCount = intdiv(count($priceRows), 11);
        
        $freeIds = array_slice(array_column($priceRows, 'id'), 0, $freeCount);

        $totalOriginal = 0;
        $discount = 0;

        DB::transaction(function () use ($participants, $priceRows, $freeIds, &$totalOriginal, &$discount) {
            $baseById = [];
            foreach ($priceRows as $row) {
                $baseById[(int) $row['id']] = (int) $row['base'];
            }

            foreach ($participants as $p) {
                $base = (int) ($baseById[(int) $p->id] ?? 0);
                $isFree = in_array((int) $p->id, $freeIds, true);
                $final = $isFree ? 0 : $base;

                $p->update([
                    'base_price' => $base,
                    'is_free' => $isFree,
                    'final_price' => $final,
                ]);

                $totalOriginal += $base;
                if ($isFree) {
                    $discount += $base;
                }
            }
        }, 3);

        $adminFee = 0;
        $subtotal = max(0, (int) round($totalOriginal - $discount));
        $uniqueCode = $mootaService->generateUniqueCode($subtotal);
        $finalAmount = $subtotal + $uniqueCode;

        $transaction = Transaction::create([
            'event_id' => $event->id,
            'user_id' => null,
            'pic_data' => [
                'name' => $registration->pic_name,
                'email' => $registration->pic_email,
                'phone' => $registration->pic_phone,
                'community_registration_id' => $registration->id,
                'community_name' => $registration->community_name,
            ],
            'total_original' => $totalOriginal,
            'coupon_id' => null,
            'discount_amount' => $discount,
            'admin_fee' => $adminFee,
            'final_amount' => $finalAmount,
            'payment_status' => 'pending',
            'payment_gateway' => 'moota',
            'payment_channel' => $validated['payment_method'] === 'qris' ? 'qris' : 'bank_transfer',
            'unique_code' => $uniqueCode,
        ]);

        $qrisPayload = null;
        if ($validated['payment_method'] === 'qris') {
            try {
                $static = (string) (config('qris.static') ?? '');
                $nmid = (string) (config('qris.nmid') ?? '');
                $qrisPayload = $qrisService->generate($static, (int) $finalAmount, $nmid !== '' ? $nmid : null);
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
        }

        $invoice = CommunityInvoice::create([
            'community_registration_id' => $registration->id,
            'transaction_id' => $transaction->id,
            'payment_method' => $validated['payment_method'],
            'status' => 'pending',
            'total_original' => $totalOriginal,
            'discount_amount' => $discount,
            'admin_fee' => $adminFee,
            'unique_code' => $uniqueCode,
            'final_amount' => $finalAmount,
            'qris_payload' => $qrisPayload,
        ]);

        $registration->update([
            'status' => 'invoiced',
            'invoiced_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'transaction_id' => $transaction->id,
            'registration_id' => $transaction->public_ref,
            'payment_gateway' => 'moota',
            'payment_channel' => $transaction->payment_channel,
            'payment_status' => $transaction->payment_status,
            'final_amount' => (float) $transaction->final_amount,
            'unique_code' => (int) $transaction->unique_code,
            'qris_payload' => $invoice->qris_payload,
        ]);
    }

    private function getRegistration(Event $event, Community $community): CommunityRegistration
    {
        return CommunityRegistration::query()
            ->where('event_id', $event->id)
            ->where('community_id', $community->id)
            ->firstOrFail();
    }

    private function generateUniqueCommunitySlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug !== '' ? $baseSlug : Str::random(10);
        $base = $baseSlug !== '' ? $baseSlug : $slug;
        $counter = 2;
        while (Community::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }
        return $slug;
    }
}
