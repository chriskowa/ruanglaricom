<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Popup;
use App\Models\PopupVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PopupController extends Controller
{
    public function index(Request $request)
    {
        $query = Popup::query()->withSum('stats as views', 'views')->withSum('stats as clicks', 'clicks')->withSum('stats as conversions', 'conversions');
        if ($search = $request->input('search')) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('slug', 'like', '%' . $search . '%');
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($sort = $request->input('sort')) {
            [$field, $dir] = array_pad(explode(':', $sort), 2, 'desc');
            if (in_array($field, ['created_at', 'updated_at', 'starts_at', 'ends_at', 'name'], true)) {
                $query->orderBy($field, $dir === 'asc' ? 'asc' : 'desc');
            }
        } else {
            $query->latest();
        }
        $popups = $query->paginate(15)->withQueryString();
        return view('admin.popups.index', compact('popups'));
    }

    public function create()
    {
        $popup = new Popup([
            'status' => 'draft',
            'settings' => [
                'position' => 'center',
                'overlay' => 'rgba(15, 23, 42, 0.7)',
                'background' => '#0f172a',
                'text_color' => '#e2e8f0',
                'accent' => '#ccff00',
                'animation' => 'fade',
                'close_on_backdrop' => true,
                'close_on_esc' => true,
            ],
            'rules' => [
                'include_paths' => ['/*'],
                'exclude_paths' => [],
                'devices' => ['desktop', 'mobile', 'tablet'],
                'segments' => ['guest', 'new', 'returning', 'premium', 'member'],
                'frequency' => ['mode' => 'session', 'interval_hours' => 24],
                'city_ids' => [],
                'time_windows' => [],
            ],
            'content' => [
                'blocks' => [],
            ],
        ]);
        $templates = $this->getTemplates();
        return view('admin.popups.create', compact('popup', 'templates'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePopup($request, null);
        $popup = Popup::create($data + [
            'created_by' => $request->user()->id ?? null,
            'updated_by' => $request->user()->id ?? null,
        ]);
        $this->storeVersion($popup, $request->user()->id ?? null);
        return redirect()->route('admin.popups.edit', $popup)->with('success', 'Popup created.');
    }

    public function edit(Popup $popup)
    {
        $templates = $this->getTemplates();
        $versions = $popup->versions()->latest()->take(20)->get();
        return view('admin.popups.edit', compact('popup', 'templates', 'versions'));
    }

    public function update(Request $request, Popup $popup)
    {
        $data = $this->validatePopup($request, $popup->id);
        $popup->update($data + ['updated_by' => $request->user()->id ?? null]);
        $this->storeVersion($popup, $request->user()->id ?? null);
        return redirect()->route('admin.popups.edit', $popup)->with('success', 'Popup updated.');
    }

    public function destroy(Popup $popup)
    {
        $popup->delete();
        return redirect()->route('admin.popups.index')->with('success', 'Popup deleted.');
    }

    public function scheduled()
    {
        $popups = Popup::query()
            ->where('status', 'scheduled')
            ->orderBy('starts_at')
            ->paginate(15);
        return view('admin.popups.scheduled', compact('popups'));
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'action' => 'required|in:activate,deactivate',
        ]);
        $status = $data['action'] === 'activate' ? 'active' : 'draft';
        Popup::whereIn('id', $data['ids'])->update(['status' => $status]);
        return redirect()->back()->with('success', 'Bulk update applied.');
    }

    public function restoreVersion(Request $request, Popup $popup, PopupVersion $version)
    {
        if ($version->popup_id !== $popup->id) {
            abort(404);
        }
        $payload = $version->payload;
        $popup->update([
            'name' => $payload['name'] ?? $popup->name,
            'slug' => $payload['slug'] ?? $popup->slug,
            'status' => $payload['status'] ?? $popup->status,
            'starts_at' => $payload['starts_at'] ?? $popup->starts_at,
            'ends_at' => $payload['ends_at'] ?? $popup->ends_at,
            'timezone' => $payload['timezone'] ?? $popup->timezone,
            'content' => $payload['content'] ?? $popup->content,
            'settings' => $payload['settings'] ?? $popup->settings,
            'rules' => $payload['rules'] ?? $popup->rules,
            'updated_by' => $request->user()->id ?? null,
        ]);
        $this->storeVersion($popup, $request->user()->id ?? null);
        return redirect()->route('admin.popups.edit', $popup)->with('success', 'Version restored.');
    }

    private function validatePopup(Request $request, ?int $popupId): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:popups,slug,' . ($popupId ?? 'NULL') . ',id',
            'status' => 'required|in:draft,scheduled,active,expired',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'timezone' => 'nullable|string|max:100',
            'content_json' => 'nullable|string',
            'settings_json' => 'nullable|string',
            'rules_json' => 'nullable|string',
        ]);
        $slug = $validated['slug'] ?: Str::slug($validated['name']);
        $content = $this->decodeJson($validated['content_json'] ?? null);
        $settings = $this->decodeJson($validated['settings_json'] ?? null);
        $rules = $this->decodeJson($validated['rules_json'] ?? null);
        return [
            'name' => $validated['name'],
            'slug' => $slug,
            'status' => $validated['status'],
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'timezone' => $validated['timezone'] ?? null,
            'content' => $content,
            'settings' => $settings,
            'rules' => $rules,
        ];
    }

    private function decodeJson(?string $raw): array
    {
        if (! $raw) {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function storeVersion(Popup $popup, ?int $userId): void
    {
        $nextVersion = (int) ($popup->versions()->max('version') ?? 0) + 1;
        PopupVersion::create([
            'popup_id' => $popup->id,
            'version' => $nextVersion,
            'payload' => [
                'name' => $popup->name,
                'slug' => $popup->slug,
                'status' => $popup->status,
                'starts_at' => $popup->starts_at?->toISOString(),
                'ends_at' => $popup->ends_at?->toISOString(),
                'timezone' => $popup->timezone,
                'content' => $popup->content ?? [],
                'settings' => $popup->settings ?? [],
                'rules' => $popup->rules ?? [],
            ],
            'created_by' => $userId,
        ]);
    }

    private function getTemplates(): array
    {
        return [
            [
                'name' => 'Promo Run Space',
                'blocks' => [
                    ['type' => 'text', 'content' => 'Diskon 20% untuk booking running space minggu ini', 'style' => ['size' => 'xl', 'weight' => 'bold']],
                    ['type' => 'text', 'content' => 'Gunakan kode: PACER20 di checkout', 'style' => ['size' => 'sm']],
                    ['type' => 'button', 'content' => 'Booking Sekarang', 'style' => ['variant' => 'primary', 'url' => '/booking']],
                ],
            ],
            [
                'name' => 'Event Reminder',
                'blocks' => [
                    ['type' => 'image', 'content' => 'https://images.unsplash.com/photo-1521412644187-c49fa049e84d?auto=format&fit=crop&w=1200&q=80', 'style' => ['radius' => 'xl']],
                    ['type' => 'text', 'content' => 'Latihan bersama PacerHub hari Sabtu', 'style' => ['size' => 'lg', 'weight' => 'bold']],
                    ['type' => 'button', 'content' => 'Lihat Detail', 'style' => ['variant' => 'outline', 'url' => '/events']],
                ],
            ],
            [
                'name' => 'Membership Upgrade',
                'blocks' => [
                    ['type' => 'text', 'content' => 'Upgrade ke Premium dan dapatkan prioritas booking', 'style' => ['size' => 'lg', 'weight' => 'bold']],
                    ['type' => 'button', 'content' => 'Upgrade Sekarang', 'style' => ['variant' => 'primary', 'url' => '/membership']],
                ],
            ],
        ];
    }
}
