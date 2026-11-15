<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EventBibCheckController extends Controller
{
    private const VALID_PAYMENT_STATUSES = ['paid', 'settlement', 'capture', 'cod'];

    /**
     * Render the public/kiosk TV BIB Check landing page for an event.
     */
    public function show(Request $request, string $slug, ?string $bib = null): View
    {
        $event = $this->findEventBySlug($slug);
        [$takeLast, $prefix] = $this->getBibCheckConfig($event);

        $initialQuery = $bib ?? $request->query('bib');
        $initialResult = null;
        $initialNotFound = false;

        if ($initialQuery !== null && trim($initialQuery) !== '') {
            $normalized = $this->normalizeBibInput($initialQuery);
            $participant = $this->findParticipantByBib($event, $normalized, $takeLast, $prefix);

            if ($participant) {
                $initialResult = $this->formatParticipantPayload($participant, $takeLast, $prefix);
            } else {
                $initialNotFound = true;
            }
        }

        $config = $event->premium_amenities['bib_check'] ?? [];
        $bibDesign = null;
        if (! empty($config['bg_image']) && ! empty($config['canvas_width']) && ! empty($config['canvas_height'])) {
            $bibDesign = [
                'bg_url' => asset('storage/' . $config['bg_image']) . '?v=' . ($event->updated_at?->timestamp ?? time()),
                'canvas_width' => (float) $config['canvas_width'],
                'canvas_height' => (float) $config['canvas_height'],
                'texts' => is_array($config['texts'] ?? null) ? $config['texts'] : [],
            ];
        }

        return view('events.bib-check', [
            'event' => $event,
            'initialQuery' => $initialQuery ? $this->normalizeBibInput($initialQuery) : '',
            'initialResult' => $initialResult,
            'initialNotFound' => $initialNotFound,
            'takeLast' => $takeLast,
            'prefix' => $prefix,
            'bibDesign' => $bibDesign,
            'canManage' => $this->canManageEvent($event),
        ]);
    }

    /**
     * Fast JSON lookup endpoint for hardware QR scanner / manual BIB check.
     */
    public function lookup(Request $request, string $slug): JsonResponse
    {
        $validated = $request->validate([
            'bib' => ['required', 'string', 'max:120'],
        ]);

        $event = $this->findEventBySlug($slug);
        [$takeLast, $prefix] = $this->getBibCheckConfig($event);
        $normalized = $this->normalizeBibInput($validated['bib']);

        if ($normalized === '') {
            return response()->json([
                'found' => false,
                'message' => 'Nomor BIB tidak valid.',
            ], 422);
        }

        $participant = $this->findParticipantByBib($event, $normalized, $takeLast, $prefix);

        if (! $participant) {
            return response()->json([
                'found' => false,
                'query' => $normalized,
                'message' => 'Nomor BIB tidak ditemukan pada event ini.',
            ]);
        }

        return response()->json([
            'found' => true,
            'query' => $normalized,
            'participant' => $this->formatParticipantPayload($participant, $takeLast, $prefix),
        ]);
    }

    /**
     * Format raw participant BIB using the event's take_last & prefix rules.
     */
    public static function formatDisplayBib(?string $rawBib, string $takeLast = '', string $prefix = '', int $categoryQuota = 0): string
    {
        $dynamicBib = trim((string) ($rawBib ?? ''));
        if ($dynamicBib === '') {
            return '-';
        }

        $takeLastTrimmed = trim($takeLast);

        if ($takeLastTrimmed !== '' && ! is_numeric($takeLastTrimmed)) {
            $dynamicBib = str_replace($takeLastTrimmed, '', $dynamicBib);
        } elseif (strpos($dynamicBib, '-') !== false) {
            $parts = explode('-', $dynamicBib);
            $dynamicBib = end($parts);
        }

        if ($categoryQuota > 0 && is_numeric($dynamicBib)) {
            $padLength = strlen((string) $categoryQuota);
            $dynamicBib = str_pad($dynamicBib, $padLength, '0', STR_PAD_LEFT);
        }

        if ($takeLastTrimmed !== '' && is_numeric($takeLastTrimmed)) {
            $numChars = (int) $takeLastTrimmed;
            if ($numChars > 0) {
                $dynamicBib = substr($dynamicBib, -$numChars);
            }
        }

        if ($prefix !== '') {
            $dynamicBib = $prefix . $dynamicBib;
        }

        return trim($dynamicBib) === '' ? '-' : $dynamicBib;
    }

    private function findEventBySlug(string $slug): Event
    {
        return Event::query()
            ->select([
                'id',
                'user_id',
                'name',
                'slug',
                'start_at',
                'location_name',
                'logo_image',
                'hero_image',
                'hero_image_url',
                'favicon_image',
                'organizer_name',
                'premium_amenities',
                'updated_at',
            ])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function getBibCheckConfig(Event $event): array
    {
        $config = $event->premium_amenities['bib_check'] ?? [];
        $takeLast = isset($config['take_last']) ? (string) $config['take_last'] : '';
        $prefix = isset($config['prefix']) ? (string) $config['prefix'] : '';

        return [$takeLast, $prefix];
    }

    private function canManageEvent(Event $event): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return (int) $event->user_id === (int) $user->id
            || in_array($user->role ?? '', ['admin', 'super_admin'], true);
    }

    /**
     * Normalize raw scanner or manual input (supports plain BIB or scanned URL).
     */
    private function normalizeBibInput(string $raw): string
    {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            return '';
        }

        if (filter_var($trimmed, FILTER_VALIDATE_URL)) {
            $parsed = parse_url($trimmed);
            if (! empty($parsed['query'])) {
                parse_str($parsed['query'], $queryParams);
                if (! empty($queryParams['bib']) && is_string($queryParams['bib'])) {
                    return trim($queryParams['bib']);
                }
            }
            if (! empty($parsed['path'])) {
                $segments = array_values(array_filter(explode('/', $parsed['path'])));
                $last = end($segments);
                if (is_string($last) && $last !== '' && $last !== 'bib-check') {
                    return urldecode(trim($last));
                }
            }
        }

        return $trimmed;
    }

    private function findParticipantByBib(Event $event, string $bib, string $takeLast, string $prefix): ?Participant
    {
        $baseQuery = Participant::query()
            ->select([
                'id',
                'transaction_id',
                'race_category_id',
                'event_package_id',
                'name',
                'bib_number',
                'gender',
            ])
            ->with([
                'category:id,name,quota',
                'package:id,name',
            ])
            ->whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id)
                    ->whereIn('payment_status', self::VALID_PAYMENT_STATUSES);
            });

        if ($event->requires_approval) {
            $baseQuery->where('isApproved', true);
        }

        // 1. Exact match (fast index lookup for QR scans)
        $exact = (clone $baseQuery)->where('bib_number', $bib)->first();
        if ($exact) {
            return $exact;
        }

        // 2. Case-insensitive fallback
        $ciMatch = (clone $baseQuery)
            ->whereRaw('LOWER(TRIM(bib_number)) = ?', [mb_strtolower($bib)])
            ->first();
        if ($ciMatch) {
            return $ciMatch;
        }

        // 3. Formatted/printed BIB lookup (e.g. "25338" matching "POLKESMASI-2026-0338")
        if ($prefix !== '' || $takeLast !== '') {
            $suffixQuery = $bib;
            if ($prefix !== '' && str_starts_with(mb_strtolower($suffixQuery), mb_strtolower($prefix))) {
                $suffixQuery = substr($suffixQuery, strlen($prefix));
            }

            if ($suffixQuery !== '') {
                $escaped = addcslashes($suffixQuery, '%_\\');
                $candidates = (clone $baseQuery)
                    ->where('bib_number', 'like', '%' . $escaped)
                    ->limit(25)
                    ->get();

                foreach ($candidates as $candidate) {
                    $formatted = self::formatDisplayBib(
                        $candidate->bib_number,
                        $takeLast,
                        $prefix,
                        (int) ($candidate->category?->quota ?? 0)
                    );
                    if (mb_strtolower($formatted) === mb_strtolower($bib)) {
                        return $candidate;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Strictly return only public-safe BIB check fields.
     *
     * @return array{name: string, bib_number: string, raw_bib: string, gender: string, category: string}
     */
    private function formatParticipantPayload(Participant $participant, string $takeLast, string $prefix): array
    {
        $displayBib = self::formatDisplayBib(
            $participant->bib_number,
            $takeLast,
            $prefix,
            (int) ($participant->category?->quota ?? 0)
        );

        return [
            'name' => trim((string) ($participant->name ?? '')) !== '' ? strtoupper(trim($participant->name)) : '-',
            'bib_number' => $displayBib,
            'raw_bib' => trim((string) ($participant->bib_number ?? '')),
            'gender' => $this->normalizeGender($participant->gender),
            'category' => $participant->category?->name ?? ($participant->package?->name ?? '-'),
        ];
    }

    private function normalizeGender(?string $gender): string
    {
        $g = mb_strtolower(trim((string) $gender));

        if (in_array($g, ['male', 'm', 'laki-laki', 'laki - laki', 'l', 'men', 'man', 'pria'], true)) {
            return 'Male';
        }

        if (in_array($g, ['female', 'f', 'perempuan', 'p', 'women', 'woman', 'wanita'], true)) {
            return 'Female';
        }

        return '-';
    }
}
