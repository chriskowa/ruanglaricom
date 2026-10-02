@extends('layouts.pacerhub', ['hideFooter' => true, 'hideChat' => true, 'hideNav' => true])

@section('title', 'Buat Kalender Pelari | Pilih Isi dan Ukuran')
@section('meta_title', 'Buat Kalender Pelari | Pilih Isi dan Ukuran')
@section('meta_description', 'Pilih tipe kalender, tahun, ukuran halaman, dan tampilan awal. Kalender langsung tersusun di editor.')

@push('styles')
@vite(['resources/css/kalender-pelari.css'])
@endpush

@php
    $_kpTypeLabels = collect($calendarTypes)->mapWithKeys(fn ($item, $key) => [$key => $item['label']])->all();
    $_kpFormatLabels = collect($formats)->mapWithKeys(fn ($item, $key) => [$key => $item['label']])->all();
    $_kpTemplateLabels = collect($templates)->mapWithKeys(fn ($item, $key) => [$key => $item['name']])->all();
    $_kpErrorStep = $errors->has('type') ? 1
        : ($errors->hasAny(['year', 'project_name', 'start_week_on']) ? 2
        : ($errors->has('format_type') ? 3 : ($errors->has('template_family') ? 4 : null)));
@endphp

@section('content')
<main class="kp-public-page min-h-dvh bg-slate-950 text-slate-100">
    <div class="mx-auto max-w-6xl px-5 py-6 sm:px-8 lg:py-10"
         x-data="wizardState()" x-init="init()">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-5">
            <a href="{{ route('kalender-pelari.landing') }}" class="text-sm font-semibold text-slate-200 underline underline-offset-4 hover:text-white">
                KalenderPelari
            </a>
            <span class="text-xs text-slate-300">Buat kalender / <span x-text="step + ' dari 5'"></span></span>
        </div>

        <div class="grid gap-8 pt-8 lg:grid-cols-[minmax(0,0.68fr)_minmax(0,1.32fr)] lg:gap-14">
            <div class="lg:sticky lg:top-8 lg:self-start">
                <p class="text-sm font-semibold text-neon">Kalender baru</p>
                <h1 class="kp-public-title mt-3 text-3xl text-white sm:text-4xl">Susun kalender lari Anda.</h1>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-200">
                    Tentukan isi dan ukuran halaman. Editor akan membuka kalender yang sudah memiliki tanggal
                    dan ruang untuk disesuaikan.
                </p>
                <nav aria-label="Langkah pembuatan kalender" class="mt-8 grid grid-cols-5 gap-1 sm:gap-2 lg:grid-cols-1">
                    @foreach([1 => 'Jenis kalender', 2 => 'Tahun', 3 => 'Ukuran', 4 => 'Tampilan', 5 => 'Periksa'] as $n => $label)
                    <button type="button" @click="goStep({{ $n }})"
                            :aria-current="step === {{ $n }} ? 'step' : null"
                            :class="step === {{ $n }} ? 'kp-wizard-step-active' : 'hover:bg-slate-900'"
                            class="kp-wizard-step flex min-w-0 flex-col items-center gap-1 rounded-md px-1 py-2 text-left transition sm:flex-row sm:gap-2 sm:px-2 lg:gap-3 lg:px-3">
                        <span class="shrink-0 text-xs font-semibold tabular-nums" :class="step === {{ $n }} ? 'text-neon' : 'text-slate-300'">
                            {{ str_pad((string) $n, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="hidden text-sm font-semibold text-slate-200 sm:inline">{{ $label }}</span>
                        <span class="sr-only sm:hidden">{{ $label }}</span>
                    </button>
                    @endforeach
                </nav>
            </div>

            <form method="POST" action="{{ route('kalender-pelari.wizard.store') }}"
                  class="min-w-0" @submit="submitting = true">
                @csrf

                @if($errors->any())
                <div role="alert" class="mb-6 rounded-md border border-red-500 bg-red-950 p-4 text-sm text-red-100">
                    Periksa pilihan Anda. {{ $errors->first() }}
                </div>
                @endif
                <p x-show="notice" x-cloak role="alert" x-text="notice"
                   class="mb-6 rounded-md border border-amber-500 bg-amber-950 p-4 text-sm text-amber-100"></p>

                <section x-show="step === 1" aria-labelledby="kp-step-type">
                    <h2 id="kp-step-type" tabindex="-1" class="kp-public-heading text-2xl text-white">Jenis kalender</h2>
                    <p class="mt-2 text-sm text-slate-200">Pilih isi awal yang paling dekat dengan kebutuhan Anda.</p>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        @foreach($calendarTypes as $key => $type)
                        <label class="kp-wizard-choice flex cursor-pointer items-start gap-4 rounded-lg border p-4 transition"
                               :class="form.type === @js($key) ? 'kp-wizard-choice-active' : 'border-slate-700 bg-slate-900 hover:border-slate-500'">
                            <input type="radio" name="type" value="{{ $key }}" x-model="form.type" class="kp-wizard-radio mt-1">
                            <span class="min-w-0">
                                <span class="block text-base font-semibold text-white">{{ $type['label'] }}</span>
                                <span class="mt-1 block text-sm leading-relaxed text-slate-200">{{ $type['tagline'] }}</span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                </section>

                <section x-show="step === 2" x-cloak aria-labelledby="kp-step-year">
                    <h2 id="kp-step-year" tabindex="-1" class="kp-public-heading text-2xl text-white">Tahun dan nama</h2>
                    <p class="mt-2 text-sm text-slate-200">Tanggal pada setiap halaman mengikuti tahun dan awal minggu ini.</p>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="kp-year" class="kp-wizard-label">Tahun kalender</label>
                            <select id="kp-year" name="year" x-model="form.year" class="kp-wizard-input">
                                @foreach($years as $year => $label)
                                <option value="{{ $year }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="kp-project-name-input" class="kp-wizard-label">Nama proyek <span class="font-normal text-slate-300">(opsional)</span></label>
                            <input id="kp-project-name-input" type="text" name="project_name" x-model="form.project_name"
                                   maxlength="120" placeholder="Misalnya: Tahun Lari Saya"
                                   class="kp-wizard-input placeholder:text-slate-400">
                        </div>
                    </div>
                    <fieldset class="mt-7">
                        <legend class="kp-wizard-label">Mulai minggu pada hari</legend>
                        <div class="mt-3 flex flex-wrap gap-3">
                            @foreach(['MONDAY' => 'Senin', 'SUNDAY' => 'Minggu'] as $value => $label)
                            <label class="kp-wizard-choice flex min-w-28 cursor-pointer items-center gap-3 rounded-md border px-4 py-3 text-sm font-semibold transition"
                                   :class="form.start_week_on === @js($value) ? 'kp-wizard-choice-active' : 'border-slate-700 bg-slate-900 hover:border-slate-500'">
                                <input type="radio" name="start_week_on" value="{{ $value }}" x-model="form.start_week_on" class="kp-wizard-radio">
                                <span>{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </fieldset>
                </section>

                <section x-show="step === 3" x-cloak aria-labelledby="kp-step-format">
                    <h2 id="kp-step-format" tabindex="-1" class="kp-public-heading text-2xl text-white">Ukuran halaman</h2>
                    <p class="mt-2 text-sm text-slate-200">Ukuran fisik tetap sama saat Anda memperbesar tampilan di editor.</p>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        @foreach($formats as $key => $format)
                        <label class="kp-wizard-choice flex cursor-pointer items-center gap-5 rounded-lg border p-4 transition"
                               :class="form.format_type === @js($key) ? 'kp-wizard-choice-active' : 'border-slate-700 bg-slate-900 hover:border-slate-500'">
                            <input type="radio" name="format_type" value="{{ $key }}" x-model="form.format_type" class="kp-wizard-radio">
                            <span class="flex h-16 w-20 shrink-0 items-center justify-center">
                                <span class="block border-2 border-slate-400 bg-white"
                                      style="aspect-ratio: {{ $format['w_mm'] }} / {{ $format['h_mm'] }}; width: {{ $format['w_mm'] / 420 * 80 }}px"></span>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-white">{{ $format['label'] }}</span>
                                <span class="mt-1 block text-xs text-slate-200">{{ $format['w_mm'] }} × {{ $format['h_mm'] }} mm</span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                </section>

                <section x-show="step === 4" x-cloak aria-labelledby="kp-step-template">
                    <h2 id="kp-step-template" tabindex="-1" class="kp-public-heading text-2xl text-white">Tampilan awal</h2>
                    <p class="mt-2 text-sm text-slate-200">Struktur dan warna halaman. Isi tetap mengikuti jenis kalender pilihan Anda.</p>
                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        @foreach($templates as $key => $template)
                        <label class="kp-wizard-choice min-w-0 cursor-pointer rounded-lg border p-3 transition"
                               :class="form.template_family === @js($key) ? 'kp-wizard-choice-active' : 'border-slate-700 bg-slate-900 hover:border-slate-500'">
                            <span class="flex items-center gap-2">
                                <input type="radio" name="template_family" value="{{ $key }}" x-model="form.template_family" class="kp-wizard-radio">
                                <span class="text-sm font-semibold text-white">{{ $template['name'] }}</span>
                            </span>
                            <span class="kp-template-sheet mt-4 block p-3 {{ $key === 'RACE_SEASON' ? 'kp-template-dark' : '' }}" aria-hidden="true">
                                <span class="flex items-center justify-between text-[10px] font-bold">
                                    <span>Januari</span><span x-text="form.year"></span>
                                </span>
                                <span class="mt-3 grid grid-cols-7 gap-0.5">
                                    <template x-for="(day, index) in previewDays()" :key="index">
                                        <span class="kp-template-day" x-text="day || ''"></span>
                                    </template>
                                </span>
                                <span class="mt-2 block border-t pt-2 text-[9px]">Catatan bulan ini</span>
                            </span>
                            <span class="mt-3 block text-xs leading-relaxed text-slate-200">{{ $template['tagline'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </section>

                <section x-show="step === 5" x-cloak aria-labelledby="kp-step-review">
                    <h2 id="kp-step-review" tabindex="-1" class="kp-public-heading text-2xl text-white">Periksa pilihan Anda</h2>
                    <p class="mt-2 text-sm text-slate-200">Editor akan membuka halaman Januari terlebih dahulu.</p>
                    <dl class="mt-6 divide-y divide-slate-700 rounded-lg border border-slate-700 bg-slate-900 px-5">
                        <div class="kp-wizard-summary"><dt>Jenis kalender</dt><dd x-text="typeLabels[form.type] || 'Belum dipilih'"></dd></div>
                        <div class="kp-wizard-summary"><dt>Tahun dan awal minggu</dt><dd x-text="form.year + ' · ' + (form.start_week_on === 'SUNDAY' ? 'Minggu' : 'Senin')"></dd></div>
                        <div class="kp-wizard-summary"><dt>Ukuran</dt><dd x-text="formatLabels[form.format_type] || 'Belum dipilih'"></dd></div>
                        <div class="kp-wizard-summary"><dt>Tampilan</dt><dd x-text="templateLabels[form.template_family] || 'Belum dipilih'"></dd></div>
                        <div class="kp-wizard-summary"><dt>Nama proyek</dt><dd x-text="form.project_name.trim() || typeLabels[form.type] + ' ' + form.year"></dd></div>
                    </dl>
                    <p class="mt-5 text-sm leading-relaxed text-slate-200">
                        Trial tanpa login tersedia selama {{ (int) (config('kalender-pelari.trial_expire_minutes', 10080) / 1440) }} hari.
                        Perubahan disimpan otomatis; masuk ke akun untuk menyimpan proyek secara permanen.
                    </p>
                </section>

                <div class="mt-9 flex flex-wrap items-center justify-between gap-3 border-t border-slate-700 pt-6">
                    <button type="button" x-show="step > 1" x-cloak @click="prev()" class="kp-secondary-link">Sebelumnya</button>
                    <span x-show="step === 1" class="hidden sm:block"></span>
                    <button type="button" x-show="step < 5" @click="next()" class="kp-primary-link">Lanjut</button>
                    <button type="submit" x-show="step === 5" x-cloak :disabled="submitting" class="kp-primary-link disabled:opacity-70">
                        <span x-text="submitting ? 'Menyiapkan kalender...' : 'Buka editor'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

@push('scripts')
<script>
    function wizardState() {
        return {
            step: {{ $_kpErrorStep ?? ($selected['type'] ? 2 : 1) }},
            notice: '',
            submitting: false,
            typeLabels: @js($_kpTypeLabels),
            formatLabels: @js($_kpFormatLabels),
            templateLabels: @js($_kpTemplateLabels),
            form: {
                type: @js($selected['type']),
                year: {{ (int) $selected['year'] }},
                format_type: @js($selected['format']),
                template_family: @js($selected['template']),
                start_week_on: @js(old('start_week_on', 'MONDAY')),
                project_name: @js(old('project_name', '')),
            },
            init() {},
            previewDays() {
                const year = Number(this.form.year);
                const offset = (new Date(Date.UTC(year, 0, 1)).getUTCDay() + 6) % 7;
                const cellCount = Math.ceil((offset + 31) / 7) * 7;
                return Array.from({ length: cellCount }, (_, index) => {
                    const day = index - offset + 1;
                    return day > 0 && day <= 31 ? day : '';
                });
            },
            validateBefore(target) {
                if (target > 1 && !this.form.type) return 'Pilih jenis kalender terlebih dahulu.';
                if (target > 2 && !this.form.year) return 'Pilih tahun kalender terlebih dahulu.';
                if (target > 3 && !this.form.format_type) return 'Pilih ukuran halaman terlebih dahulu.';
                if (target > 4 && !this.form.template_family) return 'Pilih tampilan awal terlebih dahulu.';
                return '';
            },
            goStep(target) {
                const message = this.validateBefore(target);
                if (message) {
                    this.notice = message;
                    return;
                }
                this.notice = '';
                this.step = target;
                this.$nextTick(() => {
                    const heading = this.$el.querySelector(`section[x-show="step === ${target}"] h2`);
                    heading?.focus({ preventScroll: true });
                    heading?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            },
            next() { this.goStep(Math.min(this.step + 1, 5)); },
            prev() { this.goStep(Math.max(this.step - 1, 1)); },
        };
    }
</script>
@endpush
@endsection
