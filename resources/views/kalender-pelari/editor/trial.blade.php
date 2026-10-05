@extends('layouts.pacerhub', [
    'hideFooter' => true,
    'hideChat' => true,
    'hideNav' => true,
])

@section('title', 'Editor — ' . $project->name)
@section('meta_robots', 'noindex, nofollow')

@push('styles')
@vite(['resources/css/kalender-pelari.css', 'resources/js/kalender-pelari-editor.js'])
@endpush

@php
    $_kpProject = [
        'id' => $project->id,
        'anonymous_uuid' => $project->anonymous_uuid,
        'name' => $project->name,
        'year' => $project->year,
        'format_type' => $project->format_type,
        'start_week_on' => $project->start_week_on,
        'status' => $project->status,
        'user_id' => $project->user_id,
        'version_counter' => $project->version_counter,
        'expires_at' => $project->expires_at?->toIso8601String(),
    ];
    $_kpPages = $project->pages->map(function($p) {
        return [
            'id' => $p->id,
            'month_number' => $p->month_number,
            'page_type' => $p->page_type,
            'canvas_width_mm' => $p->canvas_width_mm,
            'canvas_height_mm' => $p->canvas_height_mm,
            'month_label' => $p->monthName(),
            'layout_config' => $p->layout_config,
            'elements' => $p->elements->map(function($e) {
                return [
                    'id' => $e->id,
                    'element_type' => $e->element_type,
                    'x_mm' => $e->x_mm,
                    'y_mm' => $e->y_mm,
                    'width_mm' => $e->width_mm,
                    'height_mm' => $e->height_mm,
                    'rotation_deg' => $e->rotation_deg,
                    'z_index' => $e->z_index,
                    'locked' => $e->locked,
                    'visible' => $e->visible,
                    'style_config' => $e->style_config,
                    'content_json' => $e->content_json,
                    'data_binding' => $e->data_binding,
                    'asset_id' => $e->asset_id,
                ];
            })->values(),
        ];
    })->values();
    if (is_array($project->state_draft) && isset($project->state_draft['pages']) && is_array($project->state_draft['pages'])) {
        $_kpPages = $project->state_draft['pages'];
    }
    $_kpApi = [
        'autosave' => $autosaveApiUrl,
        'asset_mark' => route('kalender-pelari.api.asset.local-only'),
        'login' => $authLoginUrl,
        'runner_calendar' => route('runner.calendar'),
        'event_search' => route('kalender-pelari.api.events.search'),
    ];
    $_kpConfig = [
        'trial' => !($isAuthenticatedEditor ?? false),
        'csrf' => $csrfToken,
        'anon_max_files' => config('kalender-pelari.anon_trial_photo_max_files', 8),
        'anon_max_total_mb' => config('kalender-pelari.anon_trial_photo_max_total_mb', 20),
        'autosave_ms' => config('kalender-pelari.autosave_debounce_ms', 2000),
        'has_saved_draft' => is_array($project->state_draft) && isset($project->state_draft['pages']),
    ];
@endphp

@section('content')
<div id="kp-editor-root" class="bg-slate-950 flex flex-col kp-app-surface"
     data-project='{!! json_encode($_kpProject, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}'
     data-pages='{!! json_encode($_kpPages, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}'
     data-year-months='{!! json_encode($yearMonths, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}'
     data-canvas='{!! json_encode($canvas, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}'
     data-api='{!! json_encode($_kpApi, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}'
     data-config='{!! json_encode($_kpConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}'>
    <div class="kp-editor-header shrink-0 border-b border-slate-700 bg-slate-900">
        <div class="w-full px-4 h-auto min-h-16 py-2 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('kalender-pelari.landing') }}" class="text-sm text-slate-200 hover:text-white underline underline-offset-4">KalenderPelari</a>
                <span class="text-slate-600">/</span>
                <div class="min-w-0">
                    <h1 id="kp-project-name" class="truncate text-sm font-bold text-white">{{ $project->name }}</h1>
                    <p class="text-xs text-slate-300">
                        @if($project->expires_at)
                        Trial sampai {{ $project->expires_at->format('d M Y') }} ·
                        @endif
                        <span id="kp-save-indicator" class="text-slate-300">Belum ada perubahan</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button id="kp-tool-undo" disabled class="kp-icon-btn" title="Belum ada perubahan untuk dibatalkan" aria-label="Batalkan perubahan">Urung</button>
                <button id="kp-tool-redo" disabled class="kp-icon-btn" title="Belum ada perubahan untuk diulangi" aria-label="Ulangi perubahan">Ulangi</button>
                <span class="w-px h-6 bg-slate-700 mx-1"></span>
                <button id="kp-tool-zoom-out" class="kp-icon-btn" aria-label="Perkecil tampilan">−</button>
                <span id="kp-zoom-label" class="text-xs tabular-nums text-slate-200 w-12 text-center">100%</span>
                <button id="kp-tool-zoom-in" class="kp-icon-btn" aria-label="Perbesar tampilan">+</button>
                <button id="kp-tool-fit" class="kp-icon-btn" title="Sesuaikan halaman dengan ruang kerja">Pas</button>
                @unless($isAuthenticatedEditor ?? false)
                <a href="{{ $authLoginUrl }}?redirect_to={{ urlencode(route('kalender-pelari.trial.editor', $project->anonymous_uuid)) }}"
                   class="inline-flex items-center rounded-md border border-slate-600 bg-slate-800 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                    Simpan permanen
                </a>
                @else
                <a href="{{ route('kalender-pelari.my-projects') }}" class="text-sm text-slate-200 hover:text-white">Proyek saya</a>
                @endunless
            </div>
        </div>
    </div>

    <div class="flex-1 flex flex-col md:flex-row min-h-0">
        <aside class="kp-sidebar order-2 md:order-1 shrink-0 w-full md:w-56 border-t md:border-t-0 md:border-r border-slate-700 bg-slate-900 overflow-y-auto">
            <div class="p-4 border-b border-slate-700">
                <h2 class="text-sm font-semibold text-white mb-1">Isi halaman</h2>
                <p class="text-xs text-slate-300 mb-3">Pilih bagian kalender untuk mengubahnya, atau tambah elemen.</p>
                <div class="grid grid-cols-3 md:grid-cols-2 gap-2">
                    @foreach([
                        ['PHOTO', 'Foto'],
                        ['TEXT', 'Teks'],
                        ['CALENDAR_GRID', 'Tanggal'],
                        ['MONTHLY_STATS', 'Statistik'],
                        ['MONTHLY_GOAL', 'Target'],
                        ['NOTES', 'Catatan'],
                    ] as [$type, $label])
                    <button data-add-element="{{ $type }}"
                            class="kp-element-chip" title="Tambah {{ $label }}">
                        + {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="p-4 border-b border-slate-700" id="kp-element-properties-empty">
                <h2 class="text-sm font-semibold text-white mb-2">Pengaturan elemen</h2>
                <p class="text-sm text-slate-300">Klik teks, tanggal, atau bagian lainnya pada halaman untuk mengedit.</p>
            </div>

            <div id="kp-element-properties" class="p-4 border-b border-slate-700 hidden"></div>
        </aside>

            <section class="order-1 md:order-2 flex-1 min-w-0 flex flex-col">
            <div class="shrink-0 bg-slate-950 px-4 py-2 border-b border-slate-800 text-xs text-slate-200 flex justify-between gap-2">
                <span id="kp-active-page-label">Halaman kalender</span>
                <span>{{ $canvas['width_mm'] }} × {{ $canvas['height_mm'] }} mm · {{ str_replace('_', ' ', $project->format_type) }}</span>
            </div>
            <div id="kp-canvas-viewport" class="flex-1 min-h-0 overflow-auto bg-slate-800 p-4 md:p-8 relative">
                <div id="kp-canvas-scroll-content" class="min-w-full min-h-full flex items-center justify-center">
                    <div id="kp-canvas-stage" class="relative shrink-0">
                    <div id="kp-canvas-wrap"
                         class="kp-canvas-wrap">
                    </div>
                    </div>
                </div>
            </div>

            <div class="shrink-0 border-t border-slate-700 bg-slate-900 px-4 py-3 overflow-x-auto">
                <div id="kp-thumbnails" class="kp-thumbnails"></div>
            </div>
        </section>
    </div>
</div>
@endsection
