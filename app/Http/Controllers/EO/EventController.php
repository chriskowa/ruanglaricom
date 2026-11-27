<?php

namespace App\Http\Controllers\EO;

use App\Actions\EO\StoreManualParticipantAction;
use App\Http\Controllers\Controller;
use App\Mail\EventRegistrationSuccess;
use App\Mail\EventParticipantRejectedMail;
use App\Models\Event;
use App\Models\RaceCategory;
use App\Models\Transaction;
use App\Jobs\SendPendingPaymentReminder;
use App\Services\EventCacheService;
use App\Services\EventReportService;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class EventController extends Controller
{
    protected $cacheService;

    protected $reportService;

    public function __construct(EventCacheService $cacheService, EventReportService $reportService)
    {
        $this->cacheService = $cacheService;
        $this->reportService = $reportService;
    }

    public function index()
    {
        $events = Event::where('user_id', auth()->id())
            ->with(['categories' => function ($query) {
                $query->withCount(['participants as total_participants', 'participants as paid_participants' => function ($q) {
                    $q->whereHas('transaction', function ($t) {
                        $t->where('payment_status', 'paid');
                    });
                }]);
            }])
            ->latest()
            ->paginate(10);

        return view('eo.events.index', compact('events'));
    }

    protected function isPlatformFeeCustomizable($user = null): bool
    {
        $u = $user ?? auth()->user();
        if (!$u) return false;

        return $u->email === 'eo@ruanglari.com' 
            || $u->username === 'eo-jakarta-marathon' 
            || $u->role === 'admin';
    }

    public function create()
    {
        $gpxList = \App\Models\MasterGpx::where('is_published', true)->orderBy('title')->get();
        $defaultPlatformFee = (float) \App\Models\AppSettings::get('default_platform_fee', 5000);
        $canCustomPlatformFee = $this->isPlatformFeeCustomizable();

        return view('eo.events.create', compact('gpxList', 'defaultPlatformFee', 'canCustomPlatformFee'));
    }

    /**
     * Preview event landing page live without saving data
     */
    public function livePreview(Request $request)
    {
        $data = $request->all();
        
        $event = new Event();
        $event->id = 999999;
        $event->name = !empty($data['name']) ? $data['name'] : 'Preview Event Name';
        $event->short_description = $data['short_description'] ?? '';
        $event->full_description = $data['full_description'] ?? '';
        $event->terms_and_conditions = $data['terms_and_conditions'] ?? '';
        $event->start_at = !empty($data['start_at']) ? \Carbon\Carbon::parse($data['start_at']) : now();
        $event->end_at = !empty($data['end_at']) ? \Carbon\Carbon::parse($data['end_at']) : null;
        $event->registration_open_at = !empty($data['registration_open_at']) ? \Carbon\Carbon::parse($data['registration_open_at']) : null;
        $event->registration_close_at = !empty($data['registration_close_at']) ? \Carbon\Carbon::parse($data['registration_close_at']) : null;
        $event->location_name = !empty($data['location_name']) ? $data['location_name'] : 'Lokasi Event';
        $event->location_address = $data['location_address'] ?? '';
        
        // Handle images
        $event->hero_image = $data['hero_image_url'] ?? ($data['hero_image'] ?? null);
        $event->logo_image = $data['logo_image'] ?? null;
        $event->favicon_image = $data['favicon_image'] ?? null;
        $event->floating_image = $data['floating_image'] ?? null;
        
        $event->template = $data['template'] ?? 'modern-dark';
        $event->event_kind = $data['event_kind'] ?? 'directory';
        $event->is_registration_open = isset($data['is_registration_open']) ? (bool) $data['is_registration_open'] : true;
        $event->status = 'published';
        $event->is_active = true;
        $event->slug = Str::slug($event->name);

        if (!empty($data['city_id'])) {
            $event->city = \App\Models\City::find($data['city_id']);
        }
        if (!empty($data['race_type_id'])) {
            $event->raceType = \App\Models\RaceType::find($data['race_type_id']);
        }

        $event->setRelation('raceDistances', collect());
        if (isset($data['race_distances']) && is_array($data['race_distances'])) {
            $distances = collect();
            foreach ($data['race_distances'] as $distData) {
                if (is_array($distData) && !empty($distData['name'])) {
                    $d = new \App\Models\RaceDistance();
                    $d->name = $distData['name'];
                    $d->distance_km = $distData['distance_km'] ?? 0;
                    $d->price = $distData['price'] ?? 0;
                    $distances->push($d);
                }
            }
            $event->setRelation('raceDistances', $distances);
        }

        $relatedEvents = collect();
        $sameDateEvents = collect();
        $ratingAverage = 5.0;
        $ratingCount = 1;
        $isPreviewMode = true;

        return response()->view('events.running-event-detail', compact('event', 'relatedEvents', 'sameDateEvents', 'ratingAverage', 'ratingCount', 'isPreviewMode'));
    }

    /**
     * Render full interactive demo page for any event registration theme using complete dummy data & local images.
     */
    public function themeDemo(string $template)
    {
        $allowedTemplates = [
            'modern-dark',
            'quick-light',
            'light-clean',
            'beach-theme',
            'simple-minimal',
            'professional-city-run',
            'paolo-fest',
            'paolo-fest-dark',
            'golden-run',
        ];

        if (! in_array($template, $allowedTemplates, true)) {
            abort(404);
        }

        $demoAssets = $this->ensureDemoAssets();
        $isCoastal = $template === 'beach-theme';

        $event = new Event();
        $event->id = 0;
        $event->name = $isCoastal ? 'Nusantara Coastal Run 2026' : 'Nusantara City Marathon 2026';
        $event->slug = 'demo-' . $template;
        $event->organizer_name = 'RuangLari Event Organizer';
        $event->organizer_contact = '081234567890';
        $event->short_description = $isCoastal
            ? 'Lari pesisir pantai sepanjang rute sunrise terbaik dengan atmosfer liburan, cheering zone, dan podium kategori resmi.'
            : 'Event lari jalan raya resmi dengan rute terukur, jersey eksklusif, medali finisher, dan sistem registrasi instan.';
        $event->full_description = '<p><strong>' . e($event->name) . '</strong> menghadirkan pengalaman lomba lari yang terorganisir rapi mulai dari pengambilan race pack hingga garis finis.</p><p>Rute steril dilengkapi water station setiap 2,5 KM, tim medis profesional di beberapa titik, fotografer race resmi, dan hiburan panggung di area race village.</p>';
        $event->terms_and_conditions = "<ul><li>Peserta wajib dalam kondisi sehat jasmani dan rohani pada hari perlombaan.</li><li>Nomor BIB wajib terpasang jelas di bagian dada selama lomba berlangsung.</li><li>Keputusan juri dan panitia bersifat mutlak sesuai regulasi perlombaan.</li></ul>";
        $event->start_at = now()->addDays(45)->setTime(5, 30, 0);
        $event->end_at = now()->addDays(45)->setTime(11, 0, 0);
        $event->registration_open_at = now()->subDays(5);
        $event->registration_close_at = now()->addDays(35);
        $event->location_name = $isCoastal ? 'Pantai Sanur, Bali' : 'Kawasan Silang Monas, Jakarta Pusat';
        $event->location_address = $isCoastal ? 'Jl. Hang Tuah, Sanur Kaja, Denpasar Selatan, Bali' : 'Jl. Medan Merdeka Utara, Gambir, Jakarta Pusat';
        $event->location_lat = $isCoastal ? -8.6901 : -6.1754;
        $event->location_lng = $isCoastal ? 115.2615 : 106.8272;
        $event->rpc_location_name = $isCoastal ? 'Bali Beach Convention Hall' : 'FX Sudirman Atrium Lt. 3';
        $event->rpc_location_address = $isCoastal ? 'Jl. Bypass Ngurah Rai No. 88, Sanur' : 'Jl. Jenderal Sudirman, Pintu Satu Senayan, Jakarta';
        $event->hero_image = $demoAssets['hero_primary'];
        $event->hero_images = $demoAssets['hero_slider'];
        $event->logo_image = $demoAssets['logo'];
        $event->jersey_image = $demoAssets['jersey'];
        $event->medal_image = $demoAssets['medal'];
        $event->gallery = $demoAssets['gallery'];
        $event->sponsors = [
            $demoAssets['logo'],
            $demoAssets['gallery'][0] ?? $demoAssets['logo'],
        ];
        $event->template = $template;
        $event->event_kind = 'managed';
        $event->status = 'published';
        $event->is_active = true;
        $event->platform_fee = 5000;
        $event->jersey_sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL'];
        $event->theme_colors = [
            'primary' => match ($template) {
                'beach-theme' => '#0f766e',
                'golden-run' => '#eab308',
                'quick-light' => '#f97316',
                'professional-city-run' => '#0f172a',
                'paolo-fest', 'paolo-fest-dark' => '#2563eb',
                default => '#ccff00',
            },
        ];
        $event->payment_config = [
            'allowed_methods' => ['midtrans', 'manual_transfer'],
            'manual_bank' => [
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_name' => 'PT Ruang Lari Indonesia',
            ],
        ];
        $event->facilities = [
            ['name' => 'Official Race Jersey', 'description' => 'Bahan dry-fit breathable ringan untuk performa maksimal.'],
            ['name' => 'Finisher Medal', 'description' => 'Medali logam eksklusif bagi seluruh pelari sebelum COT.'],
            ['name' => 'Hydration & Medic', 'description' => 'Water station isotonik setiap 2,5 KM dan ambulans standby.'],
            ['name' => 'Timing & E-Certificate', 'description' => 'Catatan waktu resmi dan sertifikat digital pasca-lomba.'],
        ];
        $event->addons = [
            ['name' => 'Shuttle Bus Area Start', 'price' => 35000, 'quota' => 200],
            ['name' => 'Paket Foto Race HD', 'price' => 50000, 'quota' => null],
        ];
        $event->premium_amenities = [
            'jersey' => [
                'enabled' => true,
                'description' => 'Jersey resmi edisi 2026 berbahan micro-mesh cepat kering dengan potongan ergonomis.',
            ],
            'medal' => [
                'enabled' => true,
                'description' => 'Medali finisher die-cast berlapis emas matte dengan pita tenun resmi.',
            ],
            'benefits' => [
                'enabled' => true,
                'items' => [
                    ['title' => 'Official Jersey & BIB', 'description' => 'Termasuk dalam paket lomba seluruh kategori.'],
                    ['title' => 'Finisher Medal', 'description' => 'Diberikan langsung di area finis sebelum batas Cut Off Time.'],
                    ['title' => 'Refreshment & Buah Segar', 'description' => 'Air mineral, minuman isotonik, dan buah segar di race village.'],
                    ['title' => 'Dokumentasi Race', 'description' => 'Foto aksi peserta di beberapa titik ikonik sepanjang rute.'],
                ],
            ],
            'rundown' => [
                'enabled' => true,
                'items' => [
                    ['time' => '04:30 WIB', 'title' => 'Pembukaan Race Village & Drop Bag', 'description' => 'Penitipan barang dan area pemanasan dibuka.'],
                    ['time' => '05:15 WIB', 'title' => 'Flag Off Half Marathon 21K', 'description' => 'Pelepasan kategori 21K dari garis start utama.'],
                    ['time' => '05:45 WIB', 'title' => 'Flag Off 10K & 5K Fun Run', 'description' => 'Pelepasan berurutan gelombang 10K dan 5K.'],
                    ['time' => '08:30 WIB', 'title' => 'Penyerahan Podium & Hiburan', 'description' => 'Pengumuman juara podium tiap kategori dan doorprize.'],
                ],
            ],
            'faq' => [
                'enabled' => true,
                'items' => [
                    ['question' => 'Apakah bisa mendaftar tanpa nomor KTP?', 'answer' => 'Bisa. Kolom KTP bersifat opsional sesuai pengaturan penyelenggara.'],
                    ['question' => 'Apakah peserta boleh mewakilkan pengambilan Race Pack?', 'answer' => 'Boleh dengan membawa surat kuasa bermaterai dan salinan identitas pendaftar.'],
                    ['question' => 'Bagaimana ketentuan batas usia kategori?', 'answer' => 'Usia dihitung pada tanggal pelaksanaan event sesuai data tanggal lahir.'],
                ],
            ],
            'rpc' => [
                'enabled' => true,
                'schedule' => 'Jumat - Sabtu, 10:00 - 20:00 WIB',
                'notes' => 'Tunjukkan QR e-Tiket dari email konfirmasi saat pengambilan race pack.',
            ],
            'venue' => [
                'enabled' => true,
                'parking_info' => 'Tersedia area parkir resmi roda 2 dan roda 4 di sektor selatan race village mulai pukul 03:30 WIB.',
            ],
            'what_to_bring' => [
                'enabled' => true,
                'items' => [
                    'e-Tiket QR Code pada ponsel',
                    'Sepatu dan pakaian lari yang nyaman',
                    'Nomor BIB terpasang rapi di dada',
                ],
            ],
            'form_fields' => [
                '_configured' => '1',
                'id_card' => '1',
                'address' => '1',
                'date_of_birth' => '1',
                'emergency_contact' => '1',
                'jersey_size' => '1',
                'target_time' => '1',
                'blood_type' => '1',
                'bib_name' => '1',
            ],
        ];

        $categories = collect([
            new \App\Models\RaceCategory([
                'id' => 101,
                'event_id' => 0,
                'name' => '5K Fun Run',
                'code' => '5K',
                'distance_km' => 5,
                'quota' => 1000,
                'min_age' => 12,
                'max_age' => 65,
                'cutoff_minutes' => 90,
                'price_early' => 150000,
                'price_regular' => 195000,
                'early_bird_end_at' => now()->addDays(15),
                'start_time' => '05:50:00',
                'is_active' => true,
                'prizes' => [
                    1 => '2500000',
                    2 => '1500000',
                    3 => '1000000',
                ],
            ]),
            new \App\Models\RaceCategory([
                'id' => 102,
                'event_id' => 0,
                'name' => '10K Competitive',
                'code' => '10K',
                'distance_km' => 10,
                'quota' => 750,
                'min_age' => 15,
                'max_age' => 60,
                'cutoff_minutes' => 150,
                'price_early' => 250000,
                'price_regular' => 310000,
                'early_bird_end_at' => now()->addDays(15),
                'start_time' => '05:45:00',
                'is_active' => true,
                'prizes' => [
                    1 => '5000000',
                    2 => '3500000',
                    3 => '2000000',
                ],
            ]),
            new \App\Models\RaceCategory([
                'id' => 103,
                'event_id' => 0,
                'name' => 'Half Marathon 21K',
                'code' => '21K',
                'distance_km' => 21.1,
                'quota' => 500,
                'min_age' => 17,
                'max_age' => null,
                'cutoff_minutes' => 240,
                'price_early' => 425000,
                'price_regular' => 495000,
                'early_bird_end_at' => now()->addDays(15),
                'start_time' => '05:15:00',
                'is_active' => true,
                'prizes' => [
                    1 => '10000000',
                    2 => '7000000',
                    3 => '4500000',
                ],
            ]),
        ])->each(function ($cat) {
            $cat->setRelation('masterGpx', null);
        });

        $event->setRelation('categories', $categories);
        $event->setRelation('jerseyStock', null);

        $seo = [
            'title' => 'Demo Tema: ' . $event->name,
            'description' => $event->short_description,
            'keywords' => 'demo tema ruanglari, ' . $template,
            'url' => url()->current(),
            'image' => asset('storage/' . $demoAssets['hero_primary']),
        ];

        $hasPaidParticipants = false;
        $successTransaction = null;

        if (! view()->shared('errors')) {
            view()->share('errors', session('errors', new \Illuminate\Support\ViewErrorBag()));
        }

        return response()->view('events.show', compact('event', 'categories', 'seo', 'hasPaidParticipants', 'successTransaction'));
    }

    private function ensureDemoAssets(): array
    {
        $map = [
            'hero_primary' => ['images/hero/marathon-hero-cinematic.jpg', 'demo-themes/hero-marathon.jpg'],
            'hero_sunrise' => ['images/hero/indonesian-runner-sunrise.jpg', 'demo-themes/hero-sunrise.jpg'],
            'gallery_1' => ['images/hero/runner-hero.jpg', 'demo-themes/gallery-1.jpg'],
            'gallery_2' => ['images/hero/vdot-runner-training.jpg', 'demo-themes/gallery-2.jpg'],
            'gallery_3' => ['images/hero/jadwal-lari.webp', 'demo-themes/gallery-3.webp'],
            'logo' => ['images/ruanglari_green.png', 'demo-themes/logo-event.png'],
            'jersey' => ['images/demo/demo-jersey.jpg', 'demo-themes/demo-jersey.jpg'],
            'medal' => ['images/demo/demo-medal.svg', 'demo-themes/demo-medal.svg'],
        ];

        $disk = Storage::disk('public');
        foreach ($map as [$src, $dest]) {
            if (! $disk->exists($dest) && file_exists(public_path($src))) {
                $disk->put($dest, file_get_contents(public_path($src)));
            }
        }

        return [
            'hero_primary' => $map['hero_primary'][1],
            'hero_slider' => [
                $map['hero_primary'][1],
                $map['hero_sunrise'][1],
                $map['gallery_1'][1],
            ],
            'logo' => $map['logo'][1],
            'jersey' => $map['jersey'][1],
            'medal' => $map['medal'][1],
            'gallery' => [
                $map['hero_sunrise'][1],
                $map['gallery_1'][1],
                $map['gallery_2'][1],
                $map['gallery_3'][1],
            ],
        ];
    }

    /**
     * Upload media via Dropzone
     */
    public function uploadMedia(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
            'folder' => 'nullable|string',
        ]);

        $folder = $request->input('folder', 'events/temp');
        $path = $this->processImage($request->file('file'), $folder, 1920, 85);

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => Storage::url($path),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:events,slug',
            'hardcoded' => 'nullable|string|max:50',
            'organizer_name' => 'nullable|string|max:255',
            'organizer_contact' => 'nullable|string|max:255',
            'short_description' => 'nullable|string',
            'full_description' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after:start_at',
            'location_name' => 'required|string|max:255',
            'location_address' => 'nullable|string',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
            'rpc_location_name' => 'nullable|string|max:255',
            'rpc_location_address' => 'nullable|string',
            'rpc_latitude' => 'nullable|numeric',
            'rpc_longitude' => 'nullable|numeric',
            'hero_image_url' => 'nullable|url',
            'hero_image' => 'nullable|string', // Changed to string (path)
            'hero_images' => 'nullable|array|max:5',
            'hero_images.*' => 'nullable|string',
            'logo_image' => 'nullable|string',
            'favicon_image' => 'nullable|string',
            'floating_image' => 'nullable|string',
            'medal_image' => 'nullable|string',
            'jersey_image' => 'nullable|string',
            'jersey_chart_image' => 'nullable|string',
            'twibbon_image' => 'nullable|string',
            'map_embed_url' => 'nullable|string',
            'google_calendar_url' => 'nullable|url',
            'registration_open_at' => 'nullable|date',
            'registration_close_at' => 'nullable|date|after:registration_open_at',
            'promo_code' => 'nullable|string|max:50',
            'promo_buy_x' => 'nullable|integer|min:1',
            'custom_email_message' => 'nullable|string',
            'ticket_email_use_qr' => 'nullable|boolean',
            'ticket_email_template' => 'nullable|string|in:modern,minimalist,sporty',
            'ticket_email_show_jersey' => 'nullable|boolean',
            'ticket_email_show_addons' => 'nullable|boolean',
            'ticket_email_show_pic' => 'nullable|boolean',
            'show_participant_list' => 'required|boolean',
            'is_instant_notification' => 'nullable|boolean',
            'ticket_email_rate_limit_per_minute' => 'nullable|integer|min:1|max:10000',
            'blast_email_rate_limit_per_minute' => 'nullable|integer|min:1|max:10000',
            'facilities' => 'nullable|array',
            'facilities.*.name' => 'nullable|string|max:255',
            'facilities.*.description' => 'nullable|string',
            'facilities.*.enabled' => 'nullable|boolean',
            'facilities.*.image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'gallery' => 'nullable|array', // Now expects array of paths
            'gallery.*' => 'string',
            'sponsors' => 'nullable|array|max:30',
            'sponsors.*' => 'string',
            'theme_colors' => 'nullable|array',
            'theme_colors.primary' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.primary_dark' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.dark' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.card' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.input' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.neon' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.neonHover' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.accent' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.danger' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'jersey_sizes' => 'nullable|array',
            'jersey_sizes.*' => 'nullable|string|in:XXS,XS,S,M,L,XL,2XL,3XL,4XL,5XL',
            'jersey_stock' => 'nullable|array',
            'jersey_stock.*' => 'nullable|integer|min:0',
            'addons' => 'nullable|array',
            'addons.*.name' => 'required_with:addons|string|max:255',
            'addons.*.price' => 'nullable|numeric|min:0',
            'addons.*.quota' => 'nullable|integer|min:0',
            'premium_amenities' => 'nullable|array',
            'surat_kuasa_file' => 'nullable|file|mimes:pdf,doc,docx,zip,jpeg,jpg,png|max:10240',
            'template' => 'nullable|string|in:modern-dark,light-clean,beach-theme,simple-minimal,paolo-fest,paolo-fest-dark,professional-city-run,golden-run,quick-light',
            'platform_fee' => 'nullable|numeric|min:0',
            'categories' => 'required|array|min:1',
            'categories.*.master_gpx_id' => 'nullable|exists:master_gpxes,id',
            'categories.*.name' => 'required|string|max:255',
            'categories.*.distance_km' => 'nullable|numeric|min:0',
            'categories.*.code' => 'nullable|string|max:50',
            'categories.*.quota' => 'nullable|integer|min:0',
            'categories.*.min_age' => 'nullable|integer|min:0|max:120',
            'categories.*.max_age' => [
                'nullable',
                'integer',
                'min:0',
                'max:120',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $index = explode('.', $attribute)[1] ?? null;
                    $minAge = $index !== null ? $request->input("categories.{$index}.min_age") : null;
                    if ($minAge !== null && $minAge !== '' && (int) $value < (int) $minAge) {
                        $fail('Usia maksimal kategori tidak boleh lebih kecil dari usia minimal.');
                    }
                },
            ],
            'categories.*.cutoff_minutes' => 'nullable|integer|min:0',
            'categories.*.price_early' => 'nullable|integer|min:0',
            'categories.*.price_regular' => 'nullable|integer|min:0',
            'categories.*.price_late' => 'nullable|integer|min:0',
            'categories.*.early_bird_quota' => 'nullable|integer|min:0',
            'categories.*.early_bird_end_at' => 'nullable|date',
            'categories.*.reg_start_at' => 'nullable|date',
            'categories.*.reg_end_at' => 'nullable|date|after:categories.*.reg_start_at',
            'categories.*.is_active' => 'nullable|boolean',
            'categories.*.prizes' => 'nullable|array',
            'categories.*.prizes.*' => 'nullable|string|max:255',
            'payment_config' => 'nullable|array',
            'payment_config.midtrans_demo_mode' => 'nullable|boolean',
            'payment_config.allowed_methods' => 'nullable|array|min:1',
            'payment_config.allowed_methods.*' => 'in:midtrans,moota,cod,manual_transfer,all',
            'payment_config.manual_bank' => 'nullable|array',
            'payment_config.manual_bank.bank_name' => 'nullable|string|max:100',
            'payment_config.manual_bank.account_number' => 'nullable|string|max:100',
            'payment_config.manual_bank.account_name' => 'nullable|string|max:150',
            'payment_config.manual_bank.instructions' => 'nullable|string|max:1000',
            'payment_config.manual_transfer_waive_fee' => 'nullable|boolean',
            'whatsapp_config' => 'nullable|array',
            'whatsapp_config.enabled' => 'nullable|boolean',
            'whatsapp_config.template' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        if ($this->isPlatformFeeCustomizable() && $request->has('platform_fee')) {
            $validated['platform_fee'] = (float) ($request->input('platform_fee') ?? \App\Models\AppSettings::get('default_platform_fee', 5000));
        } else {
            $validated['platform_fee'] = (float) \App\Models\AppSettings::get('default_platform_fee', 5000);
        }

        if (Schema::hasColumn('events', 'ticket_email_use_qr')) {
            $validated['ticket_email_use_qr'] = array_key_exists('ticket_email_use_qr', $validated)
                ? (bool) $validated['ticket_email_use_qr']
                : true;
        } else {
            unset($validated['ticket_email_use_qr']);
        }

        if (Schema::hasColumn('events', 'ticket_email_template')) {
            $validated['ticket_email_template'] = $validated['ticket_email_template'] ?? 'modern';
        }
        if (Schema::hasColumn('events', 'ticket_email_show_jersey')) {
            $validated['ticket_email_show_jersey'] = array_key_exists('ticket_email_show_jersey', $validated) ? (bool)$validated['ticket_email_show_jersey'] : true;
        }
        if (Schema::hasColumn('events', 'ticket_email_show_addons')) {
            $validated['ticket_email_show_addons'] = array_key_exists('ticket_email_show_addons', $validated) ? (bool)$validated['ticket_email_show_addons'] : true;
        }
        if (Schema::hasColumn('events', 'ticket_email_show_pic')) {
            $validated['ticket_email_show_pic'] = array_key_exists('ticket_email_show_pic', $validated) ? (bool)$validated['ticket_email_show_pic'] : true;
        }

        if (Schema::hasColumn('events', 'is_instant_notification')) {
            $validated['is_instant_notification'] = isset($validated['is_instant_notification']) ? (bool) $validated['is_instant_notification'] : false;
        } else {
            unset($validated['is_instant_notification']);
        }

        if (Schema::hasColumn('events', 'ticket_email_rate_limit_per_minute')) {
            $validated['ticket_email_rate_limit_per_minute'] = $validated['ticket_email_rate_limit_per_minute'] ?? null;
        } else {
            unset($validated['ticket_email_rate_limit_per_minute']);
        }

        if (Schema::hasColumn('events', 'blast_email_rate_limit_per_minute')) {
            $validated['blast_email_rate_limit_per_minute'] = $validated['blast_email_rate_limit_per_minute'] ?? null;
        } else {
            unset($validated['blast_email_rate_limit_per_minute']);
        }

        // Default premium_amenities to empty array if not present (for new events to not be treated as legacy)
        if (! isset($validated['premium_amenities'])) {
            $validated['premium_amenities'] = [];
        } else {
            // Re-index nested items arrays to ensure JSON array structure (not object)
            foreach ($validated['premium_amenities'] as $key => &$amenity) {
                if (isset($amenity['items']) && is_array($amenity['items'])) {
                    $amenity['items'] = array_values($amenity['items']);
                }
                if (isset($amenity['images']) && is_array($amenity['images'])) {
                    $amenity['images'] = array_values(array_filter($amenity['images']));
                }
            }
        }

        // Handle Surat Kuasa / Dokumen Peserta upload
        if ($request->hasFile('surat_kuasa_file')) {
            $file = $request->file('surat_kuasa_file');
            $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('events/documents', $fileName, 'public');

            if (! isset($validated['premium_amenities']['surat_kuasa'])) {
                $validated['premium_amenities']['surat_kuasa'] = [];
            }
            $validated['premium_amenities']['surat_kuasa']['file_path'] = $filePath;
            $validated['premium_amenities']['surat_kuasa']['original_name'] = $file->getClientOriginalName();
        }

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            // Ensure uniqueness
            $count = Event::where('slug', $validated['slug'])->count();
            if ($count > 0) {
                $validated['slug'] .= '-'.uniqid();
            }
        }

        // Extract categories
        $categories = $validated['categories'];
        unset($validated['categories']);

        // Process facilities - filter only enabled facilities
        if (isset($validated['facilities']) && is_array($validated['facilities'])) {
            $enabledFacilities = [];
            foreach ($validated['facilities'] as $key => $facility) {
                if (! empty($facility['enabled']) && ! empty($facility['name'])) {
                    $facilityData = [
                        'name' => $facility['name'],
                        'description' => $facility['description'] ?? '',
                    ];

                    // Handle facility image (Still using old method for now, or update if needed)
                    if (isset($request->facilities[$key]['image']) && $request->hasFile("facilities.{$key}.image")) {
                        $facilityData['image'] = $this->processImage($request->file("facilities.{$key}.image"), 'events/facilities', 800, 85);
                    }

                    $enabledFacilities[] = $facilityData;
                }
            }
            $validated['facilities'] = ! empty($enabledFacilities) ? $enabledFacilities : null;
        } else {
            $validated['facilities'] = null;
        }

        // Process Gallery (Already paths from Dropzone)
        // No processing needed, just usage
        if (isset($validated['gallery'])) {
            // Move temp files to permanent location if needed, or just use as is
            // Since we used 'events/temp' or similar in uploadMedia, we might want to move them?
            // But for simplicity, let's assume uploadMedia puts them in a usable place.
            // Ideally uploadMedia should accept a folder.
        }

        // Process Hero Images (Slider max 5)
        if ($request->has('hero_images')) {
            $heroImages = (array) $request->input('hero_images', []);
            $heroImages = array_values(array_filter($heroImages));
            $heroImages = array_slice($heroImages, 0, 5);
            $validated['hero_images'] = ! empty($heroImages) ? $heroImages : null;
            if (! empty($heroImages)) {
                $validated['hero_image'] = $heroImages[0];
            }
        } elseif (! empty($validated['hero_image'])) {
            $validated['hero_images'] = [$validated['hero_image']];
        }

        // Process Sponsors (Already paths)
        // No processing needed

        // Process Theme Colors (remove nulls)
        if (isset($validated['theme_colors']) && is_array($validated['theme_colors'])) {
            $validated['theme_colors'] = array_filter($validated['theme_colors']);
            if (empty($validated['theme_colors'])) {
                $validated['theme_colors'] = null;
            }
        }

        // Process jersey_sizes - ensure it's an array
        if (isset($validated['jersey_sizes']) && is_array($validated['jersey_sizes'])) {
            $validated['jersey_sizes'] = array_values(array_filter($validated['jersey_sizes']));
            if (empty($validated['jersey_sizes'])) {
                $validated['jersey_sizes'] = [];
            }
        } else {
            $validated['jersey_sizes'] = [];
        }

        // Extract jersey_stock — not a column on events table
        $jerseyStockInput = $validated['jersey_stock'] ?? [];
        unset($validated['jersey_stock']);

        // Process payment_config (Handle 'all' option)
        if (isset($validated['payment_config']['allowed_methods'])) {
            $methods = $validated['payment_config']['allowed_methods'];
            if (is_string($methods)) {
                $methods = [$methods];
            }
            if (in_array('all', $methods)) {
                $validated['payment_config']['allowed_methods'] = ['midtrans', 'manual_transfer', 'moota', 'cod'];
            } else {
                $validated['payment_config']['allowed_methods'] = array_values(array_unique($methods));
            }
        }
        if (isset($validated['payment_config']['midtrans_demo_mode'])) {
            $validated['payment_config']['midtrans_demo_mode'] = (bool) $validated['payment_config']['midtrans_demo_mode'];
        }
        if (isset($validated['payment_config']) && is_array($validated['payment_config'])) {
            $validated['payment_config']['manual_transfer_waive_fee'] = (bool) ($validated['payment_config']['manual_transfer_waive_fee'] ?? false);
        }

        // Single images are now paths from Dropzone
        // Process addons
        if (isset($validated['addons']) && is_array($validated['addons'])) {
            $processedAddons = [];
            foreach ($validated['addons'] as $addon) {
                if (! empty($addon['name'])) {
                    $processedAddons[] = [
                        'name' => trim($addon['name']),
                        'price' => isset($addon['price']) ? (int) $addon['price'] : 0,
                        'quota' => (isset($addon['quota']) && $addon['quota'] !== '' && $addon['quota'] !== null) ? max(0, (int) $addon['quota']) : null,
                    ];
                }
            }
            $validated['addons'] = ! empty($processedAddons) ? $processedAddons : null;
        } else {
            $validated['addons'] = null;
        }

        if (isset($validated['hero_image'])) {
            $validated['hero_image_url'] = null;
        }

        $promoCodesInput = $request->input('promo_codes', []);
        if (is_string($promoCodesInput)) {
            $promoCodesInput = explode(',', $promoCodesInput);
        }
        if (! is_array($promoCodesInput)) {
            $promoCodesInput = [];
        }
        if (! empty($validated['promo_code'])) {
            $promoCodesInput = array_merge($promoCodesInput, explode(',', $validated['promo_code']));
        }
        $cleanCodes = array_values(array_unique(array_filter(array_map('trim', array_map('strtoupper', $promoCodesInput)))));
        if (! empty($cleanCodes)) {
            $validated['promo_code'] = implode(', ', $cleanCodes);
        }

        // Create event
        $event = Event::create($validated);

        if (! empty($cleanCodes)) {
            \App\Models\Coupon::whereIn('code', $cleanCodes)
                ->where(function ($q) use ($event) {
                    $q->whereNull('event_id')->orWhere('event_id', $event->id);
                })
                ->update(['event_id' => $event->id]);
        }

        // Create categories
        \Illuminate\Support\Facades\Log::info('Creating categories', ['categories' => $categories]);
        $hasPrizesColumn = Schema::hasColumn('race_categories', 'prizes');
        foreach ($categories as $categoryData) {
            $categoryData['event_id'] = $event->id;
            $categoryData['is_active'] = isset($categoryData['is_active']) ? (bool) $categoryData['is_active'] : true;
            if ($hasPrizesColumn) {
                $prizes = is_array($categoryData['prizes'] ?? null) ? $categoryData['prizes'] : [];
                $cleanedPrizes = [];
                $rank = 1;
                foreach ($prizes as $value) {
                    $value = is_string($value) ? trim($value) : $value;
                    if ($value === null || $value === '') {
                        continue;
                    }
                    $cleanedPrizes[$rank++] = $value;
                }
                $categoryData['prizes'] = $cleanedPrizes;
            } else {
                unset($categoryData['prizes']);
            }
            \App\Models\RaceCategory::create($categoryData);
        }

        // Save Jersey Size Stock Quotas
        if ($event->premium_amenities['jersey']['enabled'] ?? false) {
            $stockData = ['event_id' => $event->id];
            foreach (['XXS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL'] as $size) {
                $col = strtolower($size);
                if (in_array($size, $validated['jersey_sizes'] ?? [])) {
                    $stockData[$col] = isset($jerseyStockInput[$size]) && $jerseyStockInput[$size] !== ''
                        ? (int) $jerseyStockInput[$size]
                        : null;
                } else {
                    $stockData[$col] = null;
                }
            }
            \App\Models\JerseySize::create($stockData);
        }

        return redirect()->route('eo.events.index')
            ->with('success', 'Event berhasil dibuat!');
    }

    public function show(Event $event)
    {
        $this->authorizeEvent($event);

        $event->load([
            'categories' => function ($query) {
                $query->withCount([
                    'participants as total_participants',
                    'participants as paid_participants' => function ($q) {
                        $q->whereHas('transaction', function ($t) {
                            $t->where('payment_status', 'paid');
                        });
                    }
                ]);
            },
            'user'
        ]);

        $totalRegistered = $event->categories->sum('total_participants');
        $totalPaid = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id)->where('payment_status', 'paid');
        })->count();
        $totalEarnings = (float) Transaction::where('event_id', $event->id)
            ->where('payment_status', 'paid')
            ->selectRaw('COALESCE(SUM(final_amount - COALESCE(admin_fee, 0)), 0) as net')
            ->value('net');

        return view('eo.events.show', compact('event', 'totalRegistered', 'totalPaid', 'totalEarnings'));
    }

    public function edit(Event $event)
    {
        $this->authorizeEvent($event);

        $event->load(['categories']);
        $gpxList = \App\Models\MasterGpx::where('is_published', true)->orderBy('title')->get();
        $defaultPlatformFee = $event->platform_fee ?? (float) \App\Models\AppSettings::get('default_platform_fee', 5000);
        $canCustomPlatformFee = $this->isPlatformFeeCustomizable();

        return view('eo.events.edit', compact('event', 'gpxList', 'defaultPlatformFee', 'canCustomPlatformFee'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        // Debug Log
        \Illuminate\Support\Facades\Log::info('Update Event Payload:', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:events,slug,'.$event->id,
            'hardcoded' => 'nullable|string|max:50',
            'organizer_name' => 'nullable|string|max:255',
            'organizer_contact' => 'nullable|string|max:255',
            'short_description' => 'nullable|string',
            'full_description' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after:start_at',
            'location_name' => 'required|string|max:255',
            'location_address' => 'nullable|string',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
            'rpc_location_name' => 'nullable|string|max:255',
            'rpc_location_address' => 'nullable|string',
            'rpc_latitude' => 'nullable|numeric',
            'rpc_longitude' => 'nullable|numeric',
            'hero_image_url' => 'nullable|url',
            'hero_image' => 'nullable|string',
            'hero_images' => 'nullable|array|max:5',
            'hero_images.*' => 'nullable|string',
            'logo_image' => 'nullable|string',
            'favicon_image' => 'nullable|string',
            'floating_image' => 'nullable|string',
            'medal_image' => 'nullable|string',
            'jersey_image' => 'nullable|string',
            'jersey_chart_image' => 'nullable|string',
            'twibbon_image' => 'nullable|string',
            'map_embed_url' => 'nullable|string',
            'google_calendar_url' => 'nullable|url',
            'registration_open_at' => 'nullable|date',
            'registration_close_at' => 'nullable|date|after:registration_open_at',
            'promo_code' => 'nullable|string|max:50',
            'promo_buy_x' => 'nullable|integer|min:1',
            'custom_email_message' => 'nullable|string',
            'ticket_email_use_qr' => 'nullable|boolean',
            'ticket_email_template' => 'nullable|string|in:modern,minimalist,sporty',
            'ticket_email_show_jersey' => 'nullable|boolean',
            'ticket_email_show_addons' => 'nullable|boolean',
            'ticket_email_show_pic' => 'nullable|boolean',
            'show_participant_list' => 'required|boolean',
            'is_instant_notification' => 'nullable|boolean',
            'ticket_email_rate_limit_per_minute' => 'nullable|integer|min:1|max:10000',
            'blast_email_rate_limit_per_minute' => 'nullable|integer|min:1|max:10000',
            'facilities' => 'nullable|array',
            'facilities.*.name' => 'nullable|string|max:255',
            'facilities.*.description' => 'nullable|string',
            'facilities.*.enabled' => 'nullable|boolean',
            'facilities.*.image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'facilities.*.existing_image' => 'nullable|string',
            'gallery' => 'nullable|array',
            'gallery.*' => 'string',
            'sponsors' => 'nullable|array|max:30',
            'sponsors.*' => 'string',
            'theme_colors' => 'nullable|array',
            'theme_colors.primary' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.primary_dark' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.dark' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.card' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.input' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.neon' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.neonHover' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.accent' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.danger' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'jersey_sizes' => 'nullable|array',
            'jersey_sizes.*' => 'nullable|string|in:XXS,XS,S,M,L,XL,2XL,3XL,4XL,5XL',
            'jersey_stock' => 'nullable|array',
            'jersey_stock.*' => 'nullable|integer|min:0',
            'addons' => 'nullable|array',
            'addons.*.name' => 'required_with:addons|string|max:255',
            'addons.*.price' => 'nullable|numeric|min:0',
            'addons.*.quota' => 'nullable|integer|min:0',
            'premium_amenities' => 'nullable|array',
            'surat_kuasa_file' => 'nullable|file|mimes:pdf,doc,docx,zip,jpeg,jpg,png|max:10240',
            'remove_surat_kuasa_file' => 'nullable|boolean',
            'template' => 'nullable|string|in:modern-dark,light-clean,beach-theme,simple-minimal,professional-city-run,paolo-fest,paolo-fest-dark,golden-run,quick-light',
            'platform_fee' => 'nullable|numeric|min:0',
            'categories' => 'sometimes|array',
            'categories.*.id' => 'nullable|exists:race_categories,id',
            'categories.*.master_gpx_id' => 'nullable|exists:master_gpxes,id',
            'categories.*.name' => 'required_with:categories|string|max:255',
            'categories.*.distance_km' => 'nullable|numeric|min:0',
            'categories.*.code' => 'nullable|string|max:50',
            'categories.*.quota' => 'nullable|integer|min:0',
            'categories.*.min_age' => 'nullable|integer|min:0|max:120',
            'categories.*.max_age' => [
                'nullable',
                'integer',
                'min:0',
                'max:120',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $index = explode('.', $attribute)[1] ?? null;
                    $minAge = $index !== null ? $request->input("categories.{$index}.min_age") : null;
                    if ($minAge !== null && $minAge !== '' && (int) $value < (int) $minAge) {
                        $fail('Usia maksimal kategori tidak boleh lebih kecil dari usia minimal.');
                    }
                },
            ],
            'categories.*.cutoff_minutes' => 'nullable|integer|min:0',
            'categories.*.price_early' => 'nullable|integer|min:0',
            'categories.*.price_regular' => 'nullable|integer|min:0',
            'categories.*.price_late' => 'nullable|integer|min:0',
            'categories.*.early_bird_quota' => 'nullable|integer|min:0',
            'categories.*.early_bird_end_at' => 'nullable|date',
            'categories.*.reg_start_at' => 'nullable|date',
            'categories.*.reg_end_at' => 'nullable|date|after:categories.*.reg_start_at',
            'categories.*.is_active' => 'nullable|boolean',
            'categories.*.prizes' => 'nullable|array',
            'categories.*.prizes.*' => 'nullable|string|max:255',
            'payment_config' => 'nullable|array',
            'payment_config.midtrans_demo_mode' => 'nullable|boolean',
            'payment_config.allowed_methods' => 'nullable|array|min:1',
            'payment_config.allowed_methods.*' => 'in:midtrans,moota,cod,manual_transfer,all',
            'payment_config.manual_bank' => 'nullable|array',
            'payment_config.manual_bank.bank_name' => 'nullable|string|max:100',
            'payment_config.manual_bank.account_number' => 'nullable|string|max:100',
            'payment_config.manual_bank.account_name' => 'nullable|string|max:150',
            'payment_config.manual_bank.instructions' => 'nullable|string|max:1000',
            'payment_config.manual_transfer_waive_fee' => 'nullable|boolean',
            'whatsapp_config' => 'nullable|array',
            'whatsapp_config.enabled' => 'nullable|boolean',
            'whatsapp_config.template' => 'nullable|string',
        ]);

        if ($this->isPlatformFeeCustomizable() && $request->has('platform_fee')) {
            $validated['platform_fee'] = (float) ($request->input('platform_fee') ?? $event->platform_fee ?? \App\Models\AppSettings::get('default_platform_fee', 5000));
        } else {
            $validated['platform_fee'] = (float) \App\Models\AppSettings::get('default_platform_fee', 5000);
        }

        if (Schema::hasColumn('events', 'is_instant_notification')) {
            $validated['is_instant_notification'] = isset($validated['is_instant_notification']) ? (bool) $validated['is_instant_notification'] : false;
        } else {
            unset($validated['is_instant_notification']);
        }

        if (Schema::hasColumn('events', 'ticket_email_rate_limit_per_minute')) {
            $validated['ticket_email_rate_limit_per_minute'] = $validated['ticket_email_rate_limit_per_minute'] ?? null;
        } else {
            unset($validated['ticket_email_rate_limit_per_minute']);
        }

        if (Schema::hasColumn('events', 'blast_email_rate_limit_per_minute')) {
            $validated['blast_email_rate_limit_per_minute'] = $validated['blast_email_rate_limit_per_minute'] ?? null;
        } else {
            unset($validated['blast_email_rate_limit_per_minute']);
        }

        if (Schema::hasColumn('events', 'ticket_email_use_qr')) {
            $validated['ticket_email_use_qr'] = array_key_exists('ticket_email_use_qr', $validated)
                ? (bool) $validated['ticket_email_use_qr']
                : (bool) ($event->ticket_email_use_qr ?? true);
        } else {
            unset($validated['ticket_email_use_qr']);
        }

        if (Schema::hasColumn('events', 'ticket_email_template')) {
            $validated['ticket_email_template'] = $validated['ticket_email_template'] ?? ($event->ticket_email_template ?? 'modern');
        }
        if (Schema::hasColumn('events', 'ticket_email_show_jersey')) {
            $validated['ticket_email_show_jersey'] = array_key_exists('ticket_email_show_jersey', $validated) ? (bool)$validated['ticket_email_show_jersey'] : false;
        }
        if (Schema::hasColumn('events', 'ticket_email_show_addons')) {
            $validated['ticket_email_show_addons'] = array_key_exists('ticket_email_show_addons', $validated) ? (bool)$validated['ticket_email_show_addons'] : false;
        }
        if (Schema::hasColumn('events', 'ticket_email_show_pic')) {
            $validated['ticket_email_show_pic'] = array_key_exists('ticket_email_show_pic', $validated) ? (bool)$validated['ticket_email_show_pic'] : false;
        }

        // Default premium_amenities to empty array if not present (to allow unchecking all)
        if (! isset($validated['premium_amenities'])) {
            $validated['premium_amenities'] = [];
        } else {
            // Re-index nested items arrays to ensure JSON array structure (not object)
            foreach ($validated['premium_amenities'] as $key => &$amenity) {
                if (isset($amenity['items']) && is_array($amenity['items'])) {
                    $amenity['items'] = array_values($amenity['items']);
                }
                if (isset($amenity['images']) && is_array($amenity['images'])) {
                    $amenity['images'] = array_values(array_filter($amenity['images']));
                }
            }
        }

        // Handle Surat Kuasa / Dokumen Peserta file upload and deletion
        $existingDoc = $event->premium_amenities['surat_kuasa'] ?? [];
        $existingFilePath = $existingDoc['file_path'] ?? null;
        $existingOriginalName = $existingDoc['original_name'] ?? null;

        if ($request->boolean('remove_surat_kuasa_file')) {
            if ($existingFilePath && Storage::disk('public')->exists($existingFilePath)) {
                Storage::disk('public')->delete($existingFilePath);
            }
            if (isset($validated['premium_amenities']['surat_kuasa'])) {
                $validated['premium_amenities']['surat_kuasa']['file_path'] = null;
                $validated['premium_amenities']['surat_kuasa']['original_name'] = null;
            }
        } elseif ($request->hasFile('surat_kuasa_file')) {
            if ($existingFilePath && Storage::disk('public')->exists($existingFilePath)) {
                Storage::disk('public')->delete($existingFilePath);
            }
            $file = $request->file('surat_kuasa_file');
            $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('events/documents', $fileName, 'public');

            if (! isset($validated['premium_amenities']['surat_kuasa'])) {
                $validated['premium_amenities']['surat_kuasa'] = [];
            }
            $validated['premium_amenities']['surat_kuasa']['file_path'] = $filePath;
            $validated['premium_amenities']['surat_kuasa']['original_name'] = $file->getClientOriginalName();
        } else {
            // Keep existing file if not removed and no new file uploaded
            if ($existingFilePath && isset($validated['premium_amenities']['surat_kuasa'])) {
                $validated['premium_amenities']['surat_kuasa']['file_path'] = $existingFilePath;
                $validated['premium_amenities']['surat_kuasa']['original_name'] = $existingOriginalName;
            }
        }

        // Process facilities - filter only enabled facilities
        if (isset($validated['facilities']) && is_array($validated['facilities'])) {
            $enabledFacilities = [];
            foreach ($validated['facilities'] as $key => $facility) {
                if (! empty($facility['enabled']) && ! empty($facility['name'])) {
                    $facilityData = [
                        'name' => $facility['name'],
                        'description' => $facility['description'] ?? '',
                    ];

                    // Handle facility image
                    if (isset($request->facilities[$key]['image']) && $request->hasFile("facilities.{$key}.image")) {
                        // Delete old image if exists (optional, if we track it)
                        if (isset($facility['existing_image']) && ! empty($facility['existing_image'])) {
                            $this->deleteImage($facility['existing_image']);
                        }
                        $facilityData['image'] = $this->processImage($request->file("facilities.{$key}.image"), 'events/facilities', 800, 85);
                    } elseif (isset($facility['existing_image']) && ! empty($facility['existing_image'])) {
                        $facilityData['image'] = $facility['existing_image'];
                    }

                    $enabledFacilities[] = $facilityData;
                }
            }
            $validated['facilities'] = ! empty($enabledFacilities) ? $enabledFacilities : null;
        } else {
            $validated['facilities'] = null;
        }

        // Process Gallery
        // The frontend now sends the full ordered list of paths in 'gallery' array
        // If not present, it might mean cleared, or just not updated?
        // With Dropzone form logic, we should send empty array if cleared.
        // If 'gallery' key is missing from request, it might be safer to keep existing?
        // But with standard form submit, unchecked checkboxes/empty selects usually send nothing.
        // We will assume if 'gallery' is present (even empty array), we update.
        if ($request->has('gallery')) {
            $validated['gallery'] = $request->input('gallery');
        } else {
            // If completely missing from request (e.g. not even empty array), keep old?
            // Or maybe frontend sends empty array.
            // Let's assume frontend sends hidden inputs 'gallery[]'.
            $validated['gallery'] = null;
        }

        // Process Sponsors
        if ($request->has('sponsors')) {
            $validated['sponsors'] = $request->input('sponsors');
        } else {
            $validated['sponsors'] = null;
        }

        // Process Theme Colors (remove nulls)
        if (isset($validated['theme_colors']) && is_array($validated['theme_colors'])) {
            $validated['theme_colors'] = array_filter($validated['theme_colors']);
            if (empty($validated['theme_colors'])) {
                $validated['theme_colors'] = null;
            }
        }

        // Process jersey_sizes - ensure it's an array
        if (isset($validated['jersey_sizes']) && is_array($validated['jersey_sizes'])) {
            $validated['jersey_sizes'] = array_values(array_filter($validated['jersey_sizes']));
            if (empty($validated['jersey_sizes'])) {
                $validated['jersey_sizes'] = [];
            }
        } else {
            // Preserve existing jersey_sizes — do NOT reset when not submitted
            $validated['jersey_sizes'] = $event->jersey_sizes ?? [];
        }

        // Extract jersey_stock from validated (not a real DB column on events table)
        $jerseyStockInput = $validated['jersey_stock'] ?? [];
        unset($validated['jersey_stock']);

        // Process addons
        if (isset($validated['addons']) && is_array($validated['addons'])) {
            $processedAddons = [];
            foreach ($validated['addons'] as $addon) {
                if (! empty($addon['name'])) {
                    $processedAddons[] = [
                        'name' => trim($addon['name']),
                        'price' => isset($addon['price']) ? (int) $addon['price'] : 0,
                        'quota' => (isset($addon['quota']) && $addon['quota'] !== '' && $addon['quota'] !== null) ? max(0, (int) $addon['quota']) : null,
                    ];
                }
            }
            $validated['addons'] = ! empty($processedAddons) ? $processedAddons : null;
        } else {
            $validated['addons'] = null;
        }

        $categories = [];
        if (isset($validated['categories']) && is_array($validated['categories'])) {
            $categories = $validated['categories'];
        }
        unset($validated['categories']);

        // Auto-generate slug if not provided and name changed
        if (empty($validated['slug']) && $validated['name'] !== $event->name) {
            $validated['slug'] = Str::slug($validated['name']);
            // Ensure uniqueness
            $count = Event::where('slug', $validated['slug'])->where('id', '!=', $event->id)->count();
            if ($count > 0) {
                $validated['slug'] .= '-'.uniqid();
            }
        } elseif (empty($validated['slug'])) {
            unset($validated['slug']);
        }

        // Single images - already paths
        if ($request->filled('hero_image')) {
            $validated['hero_image_url'] = null;
        }

        // Explicitly handle image updates if present in request but not in validated (though validate should catch them)
        // This ensures that if the frontend sends a new path, it overrides whatever was there
        foreach (['hero_image', 'logo_image', 'favicon_image', 'floating_image', 'medal_image', 'jersey_image', 'jersey_chart_image', 'twibbon_image'] as $imgField) {
            if ($request->has($imgField)) {
                $validated[$imgField] = $request->input($imgField);
            }
        }

        // Process Hero Images (Slider max 5)
        if ($request->has('hero_images')) {
            $heroImages = (array) $request->input('hero_images', []);
            $heroImages = array_values(array_filter($heroImages));
            $heroImages = array_slice($heroImages, 0, 5);
            $validated['hero_images'] = ! empty($heroImages) ? $heroImages : null;
            if (! empty($heroImages)) {
                $validated['hero_image'] = $heroImages[0];
            }
        } elseif (! empty($validated['hero_image'])) {
            $validated['hero_images'] = [$validated['hero_image']];
        }

        // Process payment_config (Handle 'all' option)
        if (isset($validated['payment_config']['allowed_methods'])) {
            $methods = $validated['payment_config']['allowed_methods'];
            if (is_string($methods)) {
                $methods = [$methods];
            }
            if (in_array('all', $methods)) {
                $validated['payment_config']['allowed_methods'] = ['midtrans', 'manual_transfer', 'moota', 'cod'];
            } else {
                $validated['payment_config']['allowed_methods'] = array_values(array_unique($methods));
            }
        }
        if (isset($validated['payment_config']['midtrans_demo_mode'])) {
            $validated['payment_config']['midtrans_demo_mode'] = (bool) $validated['payment_config']['midtrans_demo_mode'];
        }
        if (isset($validated['payment_config']) && is_array($validated['payment_config'])) {
            $validated['payment_config']['manual_transfer_waive_fee'] = (bool) ($validated['payment_config']['manual_transfer_waive_fee'] ?? false);
            if (! empty($event->payment_config['report_token'])) {
                $validated['payment_config']['report_token'] = $event->payment_config['report_token'];
            }
        }

        $promoCodesInput = $request->input('promo_codes', []);
        if (is_string($promoCodesInput)) {
            $promoCodesInput = explode(',', $promoCodesInput);
        }
        if (! is_array($promoCodesInput)) {
            $promoCodesInput = [];
        }
        if (! empty($validated['promo_code'])) {
            $promoCodesInput = array_merge($promoCodesInput, explode(',', $validated['promo_code']));
        }
        $cleanCodes = array_values(array_unique(array_filter(array_map('trim', array_map('strtoupper', $promoCodesInput)))));
        if (! empty($cleanCodes)) {
            $validated['promo_code'] = implode(', ', $cleanCodes);
        }

        if (array_key_exists('custom_email_message', $validated)) {
            $validated['custom_email_message'] = $this->normalizeCustomEmailMessage($validated['custom_email_message'] ?? null, $event);
        }

        $affectedCategoryIds = [];
        $deletedCategoryIds = [];

        DB::transaction(function () use ($event, $validated, $categories, $jerseyStockInput, $cleanCodes, &$affectedCategoryIds, &$deletedCategoryIds) {
            $event->update($validated);

            if (! empty($cleanCodes)) {
                \App\Models\Coupon::whereIn('code', $cleanCodes)
                    ->where(function ($q) use ($event) {
                        $q->whereNull('event_id')->orWhere('event_id', $event->id);
                    })
                    ->update(['event_id' => $event->id]);
            }

            // Handle categories if provided
            if (! empty($categories)) {
                $existingCategoryIds = $event->categories()->pluck('id')->toArray();
                $submittedCategoryIds = array_values(array_filter(array_column($categories, 'id')));
                $raceCategoryColumns = Schema::getColumnListing('race_categories');
                $raceCategoryColumnMap = array_flip($raceCategoryColumns);
                $hasPrizesColumn = array_key_exists('prizes', $raceCategoryColumnMap);

                // Delete removed categories
                if (! empty($submittedCategoryIds)) {
                    $categoriesToDelete = array_diff($existingCategoryIds, $submittedCategoryIds);
                    if (! empty($categoriesToDelete)) {
                        \App\Models\RaceCategory::whereIn('id', $categoriesToDelete)->delete();
                        $deletedCategoryIds = array_merge($deletedCategoryIds, $categoriesToDelete);
                    }
                }

                // Update or create categories
                \Illuminate\Support\Facades\Log::info('Processing categories update', ['categories' => $categories]);
                foreach ($categories as $categoryData) {
                    $categoryId = $categoryData['id'] ?? null;
                    unset($categoryData['id']);

                    if ($hasPrizesColumn) {
                        $prizes = is_array($categoryData['prizes'] ?? null) ? $categoryData['prizes'] : [];
                        $cleanedPrizes = [];
                        $rank = 1;
                        foreach ($prizes as $value) {
                            $value = is_string($value) ? trim($value) : $value;
                            if ($value === null || $value === '') {
                                continue;
                            }
                            $cleanedPrizes[$rank++] = $value;
                        }
                        $categoryData['prizes'] = $cleanedPrizes;
                    } else {
                        unset($categoryData['prizes']);
                    }

                    if ($categoryId && in_array($categoryId, $existingCategoryIds)) {
                        // Update existing category
                        $category = $event->categories()->whereKey($categoryId)->first();
                        if ($category) {
                            $categoryData['is_active'] = isset($categoryData['is_active']) ? (bool) $categoryData['is_active'] : true;
                            $category->update($categoryData);
                            $affectedCategoryIds[] = $category->id;
                        }
                    } else {
                        // Create new category
                        $categoryData['event_id'] = $event->id;
                        $categoryData['is_active'] = isset($categoryData['is_active']) ? (bool) $categoryData['is_active'] : true;
                        $category = \App\Models\RaceCategory::create($categoryData);
                        $affectedCategoryIds[] = $category->id;
                    }
                }
            }

            // Handle Jersey Size Stock Quotas
            if ($event->premium_amenities['jersey']['enabled'] ?? false) {
                $stockData = [];
                foreach (['XXS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL'] as $size) {
                    $col = strtolower($size);
                    if (in_array($size, $validated['jersey_sizes'] ?? [])) {
                        $stockData[$col] = isset($jerseyStockInput[$size]) && $jerseyStockInput[$size] !== ''
                            ? (int) $jerseyStockInput[$size]
                            : null;
                    } else {
                        $stockData[$col] = null;
                    }
                }
                $event->jerseyStock()->updateOrCreate(['event_id' => $event->id], $stockData);
            } else {
                $event->jerseyStock()->delete();
            }
        });

        // Invalidate cache
        $this->cacheService->invalidateEventCache($event);
        foreach (array_unique(array_merge($affectedCategoryIds, $deletedCategoryIds)) as $categoryId) {
            Cache::forget("category:quota:{$categoryId}");
        }

        return redirect()->route('eo.events.index')
            ->with('success', 'Event berhasil diperbarui!');
    }

    /**
     * Preview event (redirect to public event page)
     */
    public function preview(Event $event)
    {
        $this->authorizeEvent($event);

        return redirect()->route('events.show', $event->slug)
            ->with('preview', true);
    }

    public function duplicate(Event $event)
    {
        if ((int) $event->user_id !== (int) auth()->id()) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Kamu tidak punya akses untuk menduplikasi event ini.',
                ], 403);
            }

            abort(403, 'Kamu tidak punya akses untuk menduplikasi event ini.');
        }

        $event->load(['categories']);

        $newEvent = DB::transaction(function () use ($event) {
            $newEvent = $event->replicate();
            $newEvent->name = $this->duplicateName($event->name);
            $newEvent->slug = $this->uniqueEventSlug($event->slug);
            $newEvent->created_at = now();
            $newEvent->updated_at = now();
            $newEvent->save();

            foreach ($event->categories as $category) {
                $newCategory = $category->replicate();
                $newCategory->event_id = $newEvent->id;
                $newCategory->created_at = now();
                $newCategory->updated_at = now();
                $newCategory->save();
            }

            $packages = \App\Models\EventPackage::query()->where('event_id', $event->id)->get();
            foreach ($packages as $package) {
                $newPackage = $package->replicate();
                $newPackage->event_id = $newEvent->id;
                $newPackage->sold_count = 0;
                $newPackage->is_sold_out = false;
                $newPackage->created_at = now();
                $newPackage->updated_at = now();
                $newPackage->save();
            }

            return $newEvent;
        });

        return redirect()
            ->route('eo.events.edit', $newEvent)
            ->with('success', 'Event berhasil diduplikasi!');
    }

    public function destroy(Event $event)
    {
        $this->authorizeEvent($event);

        $event->delete();

        // Invalidate cache
        $this->cacheService->invalidateEventCache($event);

        return redirect()->route('eo.events.index')
            ->with('success', 'Event berhasil dihapus!');
    }

    /**
     * Show participants list for an event
     */
    public function participants(Event $event)
    {
        $this->authorizeEvent($event);

        $eventDetailAnalytics = $this->getPublicEventDetailAnalytics($event);

        $query = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })
            ->with(['transaction.coupon', 'category']);

        // Filter by payment status
        if (request()->has('payment_status') && request()->payment_status) {
            $query->whereHas('transaction', function ($q) {
                $q->where('payment_status', request()->payment_status);
            });
        }

        // Filter by payment gateway / registration source
        if (request()->has('payment_gateway') && request()->payment_gateway) {
            $query->whereHas('transaction', function ($q) {
                $q->where('payment_gateway', request()->payment_gateway);
            });
        }

        // Filter by picked up status
        if (request()->has('is_picked_up') && request()->is_picked_up !== '') {
            $query->where('is_picked_up', request()->is_picked_up == '1');
        }

        // Filter by gender
        if (request()->has('gender') && request()->gender) {
            $query->where('gender', request()->gender);
        }

        // Filter by category
        if (request()->has('category_id') && request()->category_id) {
            $query->where('race_category_id', request()->category_id);
        }

        // Filter by coupon
        if (request()->has('coupon_id') && request()->coupon_id) {
            $couponFilter = request()->coupon_id;
            if ($couponFilter === 'with') {
                $query->whereHas('transaction', function ($q) {
                    $q->whereNotNull('coupon_id');
                });
            } elseif ($couponFilter === 'without') {
                $query->whereHas('transaction', function ($q) {
                    $q->whereNull('coupon_id');
                });
            } else {
                $query->whereHas('transaction', function ($q) use ($couponFilter) {
                    $q->where('coupon_id', $couponFilter);
                });
            }
        }

        // Filter by addons
        if (request()->filled('addon')) {
            $addonFilter = request()->query('addon');
            if ($addonFilter === 'with') {
                $query->whereNotNull('addons')->whereJsonLength('addons', '>', 0);
            } elseif ($addonFilter === 'without') {
                $query->where(function ($q) {
                    $q->whereNull('addons')->orWhereJsonLength('addons', 0);
                });
            } else {
                $query->whereJsonContains('addons', ['name' => $addonFilter]);
            }
        }

        // Filter by Jersey Size
        if (request()->filled('jersey_size')) {
            $jerseySizeFilter = request()->query('jersey_size');
            $matchSizes = [strtoupper($jerseySizeFilter)];
            if (in_array(strtoupper($jerseySizeFilter), ['2XL', 'XXL'])) {
                $matchSizes = ['2XL', 'XXL'];
            } elseif (in_array(strtoupper($jerseySizeFilter), ['3XL', 'XXXL'])) {
                $matchSizes = ['3XL', 'XXXL'];
            }
            $query->whereNotNull('jersey_size')->whereIn(\DB::raw('UPPER(TRIM(jersey_size))'), $matchSizes);
        }

        // Filter by Age Group
        if (request()->has('age_group') && request()->age_group) {
            $query->filterAgeGroup(request()->age_group, $event->start_at);
        }

        // Filter by Min Age
        if (request()->filled('min_age')) {
            $minAge = (int) request()->min_age;
            $eventDate = $event->start_at ?: now();
            $query->whereDate('date_of_birth', '<=', $eventDate->copy()->subYears($minAge));
        }

        // Filter by Max Age
        if (request()->filled('max_age')) {
            $maxAge = (int) request()->max_age;
            $eventDate = $event->start_at ?: now();
            $query->whereDate('date_of_birth', '>', $eventDate->copy()->subYears($maxAge + 1));
        }

        // Filter by Reg Date
        if (request()->filled('start_date')) {
            $query->whereDate('participants.created_at', '>=', request()->start_date);
        }
        if (request()->filled('end_date')) {
            $query->whereDate('participants.created_at', '<=', request()->end_date);
        }
        if (request()->filled('approval_status')) {
            $appStatus = request('approval_status');
            if ($appStatus === 'pending_approval') {
                $query->where('participants.isApproved', 0)
                    ->where('participants.status', '!=', 'cancelled')
                    ->where('participants.status', '!=', 'rejected')
                    ->whereHas('transaction', function ($q) {
                        $q->whereNotIn('payment_status', ['failed', 'expired']);
                    });
            } elseif ($appStatus === 'awaiting_payment') {
                $query->where('participants.isApproved', 1)
                    ->where('participants.status', '!=', 'cancelled')
                    ->where('participants.status', '!=', 'rejected')
                    ->whereHas('transaction', function ($q) {
                        $q->whereNotIn('payment_status', ['paid', 'failed', 'expired']);
                    });
            } elseif ($appStatus === 'approved') {
                $query->where('participants.isApproved', 1)
                    ->where('participants.status', '!=', 'cancelled')
                    ->where('participants.status', '!=', 'rejected')
                    ->whereHas('transaction', function ($q) {
                        $q->where('payment_status', 'paid');
                    });
            } elseif ($appStatus === 'rejected') {
                $query->where(function ($q) {
                    $q->where('participants.status', 'cancelled')
                        ->orWhere('participants.status', 'rejected')
                        ->orWhereHas('transaction', function ($t) {
                            $t->whereIn('payment_status', ['failed', 'expired']);
                        });
                });
            }
        }

        if (request()->has('search') && trim(request()->search) !== '') {
            $search = trim(request()->search);
            $query->where(function ($qq) use ($search) {
                $qq->where('participants.name', 'like', "%{$search}%")
                    ->orWhere('participants.email', 'like', "%{$search}%")
                    ->orWhere('participants.phone', 'like', "%{$search}%")
                    ->orWhere('participants.bib_number', 'like', "%{$search}%")
                    ->orWhere('participants.id_card', 'like', "%{$search}%")
                    ->orWhere('participants.address', 'like', "%{$search}%")
                    ->orWhere('participants.city', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('transaction', function ($t) use ($search) {
                        $t->where(function ($st) use ($search) {
                            $st->where('public_ref', 'like', "%{$search}%");
                            if (is_numeric($search) && (int) $search > 0) {
                                $st->orWhere('unique_code', (int) $search);
                            }
                        })
                        ->orWhere(function ($jt) use ($search) {
                            $jt->where('pic_data->name', 'like', "%{$search}%")
                                ->orWhere('pic_data->email', 'like', "%{$search}%")
                                ->orWhere('pic_data->phone', 'like', "%{$search}%")
                                ->orWhere('pic_data', 'like', "%{$search}%");
                        })->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    });
            });
        }

        $sortBy = request()->query('sort_by', 'created_at');
        $sortDir = strtolower((string) request()->query('sort_dir', 'desc'));
        if (! in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $allowedSorts = [
            'created_at' => 'participants.created_at',
            'name' => 'participants.name',
            'id_card' => 'participants.id_card',
            'bib_number' => 'participants.bib_number',
            'is_picked_up' => 'participants.is_picked_up',
            'payment_status' => 'transactions.payment_status',
            'coupon_code' => 'coupons.code',
        ];

        $perPage = (int) request()->query('per_page', 20);
        if ($perPage < 5) {
            $perPage = 5;
        }
        if ($perPage > 200) {
            $perPage = 200;
        }

        if (! array_key_exists($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        if (in_array($sortBy, ['payment_status', 'coupon_code'])) {
            $query->join('transactions', 'transactions.id', '=', 'participants.transaction_id');
            if ($sortBy === 'coupon_code') {
                $query->leftJoin('coupons', 'transactions.coupon_id', '=', 'coupons.id');
            }
            $query->where('transactions.event_id', $event->id)
                  ->select('participants.*')
                  ->orderBy($allowedSorts[$sortBy], $sortDir);
        } else {
            $query->orderBy($allowedSorts[$sortBy], $sortDir);
        }

        $participants = $query->paginate($perPage)->withQueryString();

        $financials = [
            'gross_revenue' => \App\Models\Transaction::where('event_id', $event->id)
                ->where('payment_status', 'paid')
                ->sum('final_amount'),
            'platform_fee' => \App\Models\Transaction::where('event_id', $event->id)
                ->where('payment_status', 'paid')
                ->sum('admin_fee'),
        ];
        $financials['net_revenue'] = $financials['gross_revenue'] - $financials['platform_fee'];

        if (request()->ajax() || request()->wantsJson()) {
            // Handle Report AJAX
            if (request('action') === 'get_report') {
                $reportFilters = [
                    'start_date' => request('report_start_date'),
                    'end_date' => request('report_end_date'),
                    'ticket_type' => request('report_ticket_type'),
                    'sort_dir' => request('report_sort_dir'),
                ];
                $eventReport = $this->reportService->getEventReport($event, $reportFilters);
                $eventReport['jersey_sizes_pending_pickup'] = $this->getJerseyPendingPickupCounts($event);

                return response()->json([
                    'success' => true,
                    'report' => $eventReport,
                ]);
            }

            $items = $participants->getCollection()->map(function ($p) use ($event) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'bib_name' => $p->bib_name,
                    'gender' => $p->gender,
                    'email' => $p->email,
                    'phone' => $p->phone,
                    'id_card' => $p->id_card,
                    'emergency_contact_name' => $p->emergency_contact_name,
                    'emergency_contact_number' => $p->emergency_contact_number,
                    'emergency_contact_relation' => $p->emergency_contact_relation,
                    'date_of_birth' => $p->date_of_birth ? $p->date_of_birth->toDateString() : null,
                    'address' => $p->address,
                    'city' => $p->city,
                    'province' => $p->province,
                    'postal_code' => $p->postal_code,
                    'category' => $p->category ? $p->category->name : '-',
                    'race_category_id' => $p->race_category_id,
                    'bib_number' => $p->bib_number,
                    'age_group' => $p->getAgeGroup($event->start_at) . ($p->date_of_birth ? ' (' . number_format($p->date_of_birth->floatDiffInYears($event->start_at), 1) . ' thn)' : ''),
                    'jersey_size' => $p->jersey_size,
                    'created_at' => $p->created_at ? $p->created_at->format('d M Y') : '',
                    'payment_status' => $p->transaction->payment_status ?? 'pending',
                    'transaction_id' => $p->transaction->id,
                    'public_ref' => $p->transaction->public_ref ?? ('REG-'.$p->transaction_id),
                    'unique_code' => $p->transaction->unique_code ?? 0,
                    'final_amount' => (float) ($p->transaction->final_amount ?? 0),
                    'payment_proof' => $p->transaction->payment_proof ? asset('storage/'.$p->transaction->payment_proof) : null,
                    'payment_proof_uploaded_at' => $p->transaction->payment_proof_uploaded_at ? $p->transaction->payment_proof_uploaded_at->format('d M Y H:i') : null,
                    'proof_notes' => $p->transaction->proof_notes ?? null,
                    'is_picked_up' => $p->is_picked_up,
                    'picked_up_by' => $p->picked_up_by,
                    'isApproved' => in_array($p->transaction->payment_status ?? '', ['failed', 'expired']) ? false : (bool) $p->isApproved,
                    'status' => in_array($p->transaction->payment_status ?? '', ['failed', 'expired']) ? 'cancelled' : $p->status,
                    'category_quota' => $p->category ? $p->category->quota : 0,
                    'blood_type' => $p->blood_type,
                    'medical_history' => $p->medical_history,
                    'payment_update_url' => route('eo.events.transactions.payment-status', [$event, $p->transaction->id]),
                    'pic_name' => $p->transaction->pic_data['name'] ?? '-',
                    'pic_phone' => $p->transaction->pic_data['phone'] ?? '-',
                    'pic_email' => $p->transaction->pic_data['email'] ?? '-',
                    'transaction_date' => $p->transaction->created_at ? $p->transaction->created_at->format('d M Y H:i') : '-',
                    'payment_method' => $p->transaction->payment_gateway ?? '-',
                    'coupon_code' => $p->transaction->coupon?->code ?? null,
                    'coupon_id' => $p->transaction->coupon_id ?? null,
                    'addons' => $p->addons,
                    'photo' => $p->photo ? asset('storage/' . $p->photo) : null,
                    'photo_path' => $p->photo,
                    'notes' => $p->notes,
                ];
            });

            $stats = [
                'total_registered' => $participants->total(),
                'paid_confirmed' => \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
                    $q->where('event_id', $event->id)->where('payment_status', 'paid');
                })->count(),
                'race_pack_picked_up' => \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
                    $q->where('event_id', $event->id);
                })->where('is_picked_up', true)->count(),
                'pending_pickup' => \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
                    $q->where('event_id', $event->id)->where('payment_status', 'paid');
                })->where('is_picked_up', false)->count(),
                'jersey_sizes_pending_pickup' => $this->getJerseyPendingPickupCounts($event),
            ];

            return response()->json([
                'success' => true,
                'data' => $items,
                'stats' => $stats,
                'financials' => $financials,
                'meta' => [
                    'current_page' => $participants->currentPage(),
                    'last_page' => $participants->lastPage(),
                    'per_page' => $participants->perPage(),
                    'total' => $participants->total(),
                    'next_page_url' => $participants->nextPageUrl(),
                    'prev_page_url' => $participants->previousPageUrl(),
                ],
            ]);
        }

        // Initial Report Data
        $eventReport = $this->reportService->getEventReport($event, [
            'start_date' => request('report_start_date'),
            'end_date' => request('report_end_date'),
            'ticket_type' => request('report_ticket_type'),
        ]);
        $eventReport['jersey_sizes_pending_pickup'] = $this->getJerseyPendingPickupCounts($event);

        $reportLink = $this->buildReportLink($event);

        $bibNumbers = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })
            ->whereNotNull('bib_number')
            ->pluck('bib_number');

        $nextBibNumber = null;

        if ($bibNumbers->isNotEmpty()) {
            $maxNumber = null;
            $prefix = '';
            $suffixLength = null;

            foreach ($bibNumbers as $bib) {
                if (! preg_match('/(\d+)\s*$/', $bib, $m)) {
                    continue;
                }

                $number = (int) $m[1];

                if ($maxNumber === null || $number > $maxNumber) {
                    $maxNumber = $number;
                    $suffixLength = strlen($m[1]);
                    $prefix = substr($bib, 0, -$suffixLength);
                }
            }

            if ($maxNumber !== null) {
                $next = $maxNumber + 1;
                $nextBibNumber = $prefix.str_pad((string) $next, $suffixLength, '0', STR_PAD_LEFT);
            }
        }

        if (! $nextBibNumber) {
            $nextBibNumber = '1';
        }

        $coupons = \App\Models\Coupon::where('event_id', $event->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('eo.events.participants', compact('event', 'participants', 'financials', 'eventReport', 'reportLink', 'nextBibNumber', 'coupons', 'eventDetailAnalytics'));
    }

    private function buildReportLink(Event $event): string
    {
        $token = $event->payment_config['report_token'] ?? null;
        $params = ['event' => $event->id];
        if (! empty($token)) {
            $params['token'] = $token;
        }

        return URL::signedRoute('report.show', $params);
    }

    public function regenerateReportLink(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $config = is_array($event->payment_config) ? $event->payment_config : [];
        $config['report_token'] = \Illuminate\Support\Str::random(24);
        $event->payment_config = $config;
        $event->save();

        session()->forget([
            'report_access_' . $event->id,
            'report_access_' . $event->slug,
            'report_access_token_' . $event->id,
        ]);

        return response()->json([
            'success' => true,
            'report_link' => $this->buildReportLink($event),
            'message' => 'Report link berhasil di-generate ulang. Link lama sudah tidak berlaku.',
        ]);
    }

    private function getJerseyPendingPickupCounts(Event $event): array
    {
        $soldStatuses = ['paid'];

        $raw = \App\Models\Participant::query()
            ->join('transactions', 'transactions.id', '=', 'participants.transaction_id')
            ->where('transactions.event_id', $event->id)
            ->whereIn('transactions.payment_status', $soldStatuses)
            ->where('participants.is_picked_up', false)
            ->whereNotNull('participants.jersey_size')
            ->where('participants.jersey_size', '!=', '')
            ->selectRaw('UPPER(participants.jersey_size) as jersey_size, COUNT(*) as total')
            ->groupBy('jersey_size')
            ->pluck('total', 'jersey_size')
            ->toArray();

        $normalized = [];
        foreach ($raw as $size => $total) {
            $key = strtoupper(trim((string) $size));
            if ($key === 'XXL') {
                $key = '2XL';
            } elseif ($key === 'XXXL') {
                $key = '3XL';
            }
            $normalized[$key] = (int) ($normalized[$key] ?? 0) + (int) $total;
        }

        return $normalized;
    }

    public function participantsApi(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $perPage = (int) $request->query('per_page', 200);
        if ($perPage < 1) {
            $perPage = 1;
        }
        if ($perPage > 500) {
            $perPage = 500;
        }

        $query = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })
            ->with(['transaction', 'category']);

        if ($request->filled('payment_status')) {
            $status = $request->query('payment_status');
            $query->whereHas('transaction', function ($q) use ($status) {
                $q->where('payment_status', $status);
            });
        }

        if ($request->filled('payment_gateway')) {
            $gateway = $request->query('payment_gateway');
            $query->whereHas('transaction', function ($q) use ($gateway) {
                $q->where('payment_gateway', $gateway);
            });
        }

        if ($request->has('is_picked_up') && $request->query('is_picked_up') !== '') {
            $query->where('is_picked_up', (string) $request->query('is_picked_up') === '1');
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->query('gender'));
        }

        if ($request->filled('category_id')) {
            $query->where('race_category_id', $request->query('category_id'));
        }

        if ($request->filled('addon')) {
            $addonFilter = $request->query('addon');
            if ($addonFilter === 'with') {
                $query->whereNotNull('addons')->whereJsonLength('addons', '>', 0);
            } elseif ($addonFilter === 'without') {
                $query->where(function ($q) {
                    $q->whereNull('addons')->orWhereJsonLength('addons', 0);
                });
            } else {
                $query->whereJsonContains('addons', ['name' => $addonFilter]);
            }
        }

        // Filter by Jersey Size
        if ($request->filled('jersey_size')) {
            $jerseySizeFilter = $request->query('jersey_size');
            $matchSizes = [strtoupper($jerseySizeFilter)];
            if (in_array(strtoupper($jerseySizeFilter), ['2XL', 'XXL'])) {
                $matchSizes = ['2XL', 'XXL'];
            } elseif (in_array(strtoupper($jerseySizeFilter), ['3XL', 'XXXL'])) {
                $matchSizes = ['3XL', 'XXXL'];
            }
            $query->whereNotNull('jersey_size')->whereIn(\DB::raw('UPPER(TRIM(jersey_size))'), $matchSizes);
        }

        if ($request->filled('age_group')) {
            $query->filterAgeGroup($request->query('age_group'), $event->start_at);
        }

        // Filter by Min Age
        if ($request->filled('min_age')) {
            $minAge = (int) $request->query('min_age');
            $eventDate = $event->start_at ?: now();
            $query->whereDate('date_of_birth', '<=', $eventDate->copy()->subYears($minAge));
        }

        // Filter by Max Age
        if ($request->filled('max_age')) {
            $maxAge = (int) $request->query('max_age');
            $eventDate = $event->start_at ?: now();
            $query->whereDate('date_of_birth', '>', $eventDate->copy()->subYears($maxAge + 1));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('participants.created_at', '>=', $request->query('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('participants.created_at', '<=', $request->query('end_date'));
        }
        if ($request->filled('approval_status')) {
            $appStatus = $request->query('approval_status');
            if ($appStatus === 'pending_approval') {
                $query->where('participants.isApproved', 0)
                    ->where('participants.status', '!=', 'cancelled')
                    ->where('participants.status', '!=', 'rejected')
                    ->whereHas('transaction', function ($q) {
                        $q->whereNotIn('payment_status', ['failed', 'expired']);
                    });
            } elseif ($appStatus === 'awaiting_payment') {
                $query->where('participants.isApproved', 1)
                    ->where('participants.status', '!=', 'cancelled')
                    ->where('participants.status', '!=', 'rejected')
                    ->whereHas('transaction', function ($q) {
                        $q->whereNotIn('payment_status', ['paid', 'failed', 'expired']);
                    });
            } elseif ($appStatus === 'approved') {
                $query->where('participants.isApproved', 1)
                    ->where('participants.status', '!=', 'cancelled')
                    ->where('participants.status', '!=', 'rejected')
                    ->whereHas('transaction', function ($q) {
                        $q->where('payment_status', 'paid');
                    });
            } elseif ($appStatus === 'rejected') {
                $query->where(function ($q) {
                    $q->where('participants.status', 'cancelled')
                        ->orWhere('participants.status', 'rejected')
                        ->orWhereHas('transaction', function ($t) {
                            $t->whereIn('payment_status', ['failed', 'expired']);
                        });
                });
            }
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($qq) use ($search) {
                $qq->where('participants.name', 'like', "%{$search}%")
                    ->orWhere('participants.email', 'like', "%{$search}%")
                    ->orWhere('participants.phone', 'like', "%{$search}%")
                    ->orWhere('participants.bib_number', 'like', "%{$search}%")
                    ->orWhere('participants.id_card', 'like', "%{$search}%")
                    ->orWhere('participants.address', 'like', "%{$search}%")
                    ->orWhere('participants.city', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('transaction', function ($t) use ($search) {
                        $t->where(function ($st) use ($search) {
                            $st->where('public_ref', 'like', "%{$search}%");
                            if (is_numeric($search) && (int) $search > 0) {
                                $st->orWhere('unique_code', (int) $search);
                            }
                        })->orWhere(function ($jt) use ($search) {
                            $jt->where('pic_data->name', 'like', "%{$search}%")
                                ->orWhere('pic_data->email', 'like', "%{$search}%")
                                ->orWhere('pic_data->phone', 'like', "%{$search}%")
                                ->orWhere('pic_data', 'like', "%{$search}%");
                        })->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    });
            });
        }

        $participants = $query->orderBy('id')->cursorPaginate($perPage)->withQueryString();

        $items = collect($participants->items())->map(function ($p) use ($event) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'bib_name' => $p->bib_name,
                'gender' => $p->gender,
                'email' => $p->email,
                'phone' => $p->phone,
                'id_card' => $p->id_card,
                'emergency_contact_name' => $p->emergency_contact_name,
                'emergency_contact_number' => $p->emergency_contact_number,
                'emergency_contact_relation' => $p->emergency_contact_relation,
                'address' => $p->address,
                'city' => $p->city,
                'province' => $p->province,
                'postal_code' => $p->postal_code,
                'race_category_id' => $p->race_category_id,
                'category' => $p->category ? $p->category->name : '-',
                'category_quota' => $p->category ? $p->category->quota : 0,
                'bib_number' => $p->bib_number,
                'date_of_birth' => $p->date_of_birth ? $p->date_of_birth->toDateString() : null,
                'age_group' => $p->getAgeGroup($event->start_at),
                'jersey_size' => $p->jersey_size,
                'blood_type' => $p->blood_type,
                'medical_history' => $p->medical_history,
                'created_at' => $p->created_at ? $p->created_at->format('Y-m-d H:i:s') : null,
                'payment_status' => $p->transaction->payment_status ?? 'pending',
                'payment_update_url' => route('eo.events.participants.payment-update', ['event' => $event->slug, 'participant' => $p->id]),
                'payment_method' => $p->transaction->payment_channel ?? $p->transaction->payment_gateway ?? '-',
                'transaction_id' => $p->transaction->id,
                'transaction_date' => $p->transaction->created_at ? $p->transaction->created_at->format('Y-m-d H:i:s') : '-',
                'is_picked_up' => $p->is_picked_up,
                'picked_up_at' => $p->picked_up_at ? $p->picked_up_at->format('Y-m-d H:i:s') : null,
                'picked_up_by' => $p->picked_up_by,
                'isApproved' => (bool) $p->isApproved,
                'status' => $p->status,
                'pic_name' => $p->pic_name,
                'pic_phone' => $p->pic_phone,
                'pic_email' => $p->pic_email,
                'addons' => $p->addons,
                'photo' => $p->photo ? asset('storage/' . $p->photo) : null,
                'photo_path' => $p->photo,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'per_page' => $participants->perPage(),
                'next_cursor' => $participants->nextCursor()?->encode(),
                'prev_cursor' => $participants->previousCursor()?->encode(),
            ],
        ]);
    }

    public function storeParticipant(Request $request, Event $event, StoreManualParticipantAction $action)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bib_name' => 'nullable|string|max:30',
            'gender' => 'nullable|in:male,female',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|min:10|max:15|regex:/^[0-9]+$/',
            'id_card' => 'nullable|string|max:50',
            'address' => 'required|string|max:500',
            'category_id' => [
                'required',
                'exists:race_categories,id',
                function ($attribute, $value, $fail) use ($event) {
                    $category = RaceCategory::find($value);
                    if (! $category || (int) $category->event_id !== (int) $event->id) {
                        $fail('Kategori tidak valid untuk event ini.');
                    }
                },
            ],
            'date_of_birth' => 'nullable|date|before:today',
            'target_time' => ['nullable', 'string', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', 'not_in:00:00:00'],
            'jersey_size' => 'nullable|string|max:10',
            'blood_type' => 'nullable|string|in:A,B,AB,O',
            'medical_history' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|min:10|max:15|regex:/^[0-9]+$/',
            'emergency_contact_relation' => 'nullable|string|max:50',
            'bib_number' => ['nullable', 'string', 'max:20', Rule::unique('participants', 'bib_number')],
            'send_whatsapp' => 'nullable|boolean',
            'use_queue' => 'nullable|boolean',
            'coupon_id' => [
                'nullable',
                'exists:coupons,id',
                function ($attribute, $value, $fail) use ($event) {
                    $coupon = \App\Models\Coupon::find($value);
                    if ($coupon && (int) $coupon->event_id !== (int) $event->id) {
                        $fail('Kupon tidak valid untuk event ini.');
                    }
                },
            ],
        ]);

        $transaction = $action->execute($event, $validated, $request->user());

        if ($request->boolean('use_queue')) {
            \App\Jobs\SendEventRegistrationNotification::dispatch($transaction);
        } else {
            \App\Jobs\SendEventRegistrationNotification::dispatchSync($transaction);
        }

        if ($request->wantsJson() || $request->ajax()) {
            $participant = $transaction->participants->first();

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->id,
                'participant_id' => $participant?->id,
            ], 201);
        }

        return redirect()
            ->route('eo.events.participants', $event)
            ->with('success', 'Peserta berhasil ditambahkan dan email konfirmasi dikirim.');
    }

    public function downloadParticipantsImportTemplate(Event $event)
    {
        $this->authorizeEvent($event);

        $headers = [
            'group_key',
            'pic_name',
            'pic_email',
            'pic_phone',
            'name',
            'email',
            'phone',
            'gender',
            'category_id',
            'id_card',
            'address',
            'city',
            'province',
            'postal_code',
            'emergency_contact_name',
            'emergency_contact_number',
            'date_of_birth',
            'target_time',
            'jersey_size',
            'blood_type',
            'bib_number',
            'payment_status',
            'coupon_code',
        ];

        $example = [
            'GROUP-001',
            'PIC Name',
            'pic@example.com',
            '081234567890',
            'Nama Peserta',
            'peserta@example.com',
            '081234567891',
            'male',
            optional($event->categories->first())->id ?: '',
            'IDCARD-001',
            'Alamat peserta',
            '',
            '',
            '',
            '',
            '',
            '1990-01-01',
            '01:30:00',
            'M',
            'O',
            '',
            'paid',
            '',
        ];

        $out = implode(',', $headers)."\n".implode(',', array_map(function ($v) {
            $v = (string) $v;
            $v = str_replace('"', '""', $v);
            return '"'.$v.'"';
        }, $example))."\n";

        $filename = 'participants-import-template-'.$event->id.'.csv';

        return response($out, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function importParticipantsCsv(Request $request, Event $event, \App\Actions\EO\ImportParticipantsCsvAction $action)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'file' => 'required|file|max:5120|mimes:csv,txt',
            'dry_run' => 'nullable|boolean',
            'send_email_if_paid' => 'nullable|boolean',
            'use_queue' => 'nullable|boolean',
        ]);

        $result = $action->execute(
            $event,
            $request->file('file'),
            [
                'dry_run' => $request->boolean('dry_run', true),
                'send_email_if_paid' => $request->boolean('send_email_if_paid', true),
                'use_queue' => $request->boolean('use_queue', true),
            ],
            $request->user()
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Update participant details
     */
    public function updateParticipant(Request $request, Event $event, \App\Models\Participant $participant)
    {
        $this->authorizeEvent($event);

        // Ensure participant belongs to event (via Transaction or Category)
        $belongsToEvent = false;
        if ($participant->transaction && $participant->transaction->event_id == $event->id) {
            $belongsToEvent = true;
        } elseif ($participant->category && $participant->category->event_id == $event->id) {
            $belongsToEvent = true;
        }

        if (! $belongsToEvent) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bib_name' => 'nullable|string|max:30',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|min:8|max:20',
            'id_card' => 'nullable|string|max:50',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|max:20',
            'emergency_contact_relation' => 'nullable|string|max:50',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'race_category_id' => 'required|exists:race_categories,id',
            'bib_number' => ['nullable', 'string', 'max:20', Rule::unique('participants', 'bib_number')->ignore($participant->id)],
            'jersey_size' => 'nullable|string|max:10',
            'blood_type' => 'nullable|string|in:A,B,AB,O',
            'medical_history' => 'nullable|string|max:500',
            'strava_url' => 'nullable|string|max:500',
            'is_picked_up' => 'nullable|boolean',
            'picked_up_by' => 'nullable|string|max:255',
            'payment_status' => 'nullable|string|in:paid,pending,cod,failed,expired,cancelled',
            'coupon_id' => 'nullable|exists:coupons,id',
            'target_time' => ['nullable', 'string', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/'],
            'pic_name' => 'nullable|string|max:255',
            'pic_email' => 'nullable|email|max:255',
            'pic_phone' => 'nullable|string|min:8|max:20',
            'addons' => 'nullable|array|max:50',
            'addons.*' => 'array',
            'addons.*.name' => 'nullable|string|max:100',
            'addons.*.price' => 'nullable|numeric|min:0',
            'addons.*.value' => 'nullable',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (!empty($validated['jersey_size'])) {
            $sz = strtoupper(trim($validated['jersey_size']));
            if ($sz === 'XXL') {
                $sz = '2XL';
            } elseif ($sz === 'XXXL') {
                $sz = '3XL';
            }
            $validated['jersey_size'] = $sz;
        }

        // If is_picked_up is toggled, handle timestamp
        if (isset($validated['is_picked_up'])) {
            $validated['is_picked_up'] = (bool) $validated['is_picked_up'];
            $pickedUpBy = ! empty($validated['picked_up_by']) ? trim($validated['picked_up_by']) : (auth()->user()->name ?? 'Panitia');
            if ($validated['is_picked_up'] && ! $participant->is_picked_up) {
                $validated['picked_up_at'] = now();
                $validated['picked_up_by'] = $pickedUpBy;
            } elseif (! $validated['is_picked_up'] && $participant->is_picked_up) {
                $validated['picked_up_at'] = null;
                $validated['picked_up_by'] = null;
            } elseif ($validated['is_picked_up'] && ! empty($validated['picked_up_by'])) {
                $validated['picked_up_by'] = $pickedUpBy;
            }
        }

        // Handle payment_status update if supplied
        if ($participant->transaction && ! empty($validated['payment_status'])) {
            $newPaymentStatus = $validated['payment_status'];
            $oldPaymentStatus = $participant->transaction->payment_status;
            if ($oldPaymentStatus !== $newPaymentStatus) {
                $participant->transaction->update([
                    'payment_status' => $newPaymentStatus,
                    'paid_at' => ($newPaymentStatus === 'paid' ? now() : null),
                ]);
                if ($newPaymentStatus === 'paid') {
                    \App\Jobs\ProcessPaidEventTransaction::dispatchAfterResponse($participant->transaction);
                } elseif (in_array($newPaymentStatus, ['failed', 'expired', 'cancelled'])) {
                    $participant->update([
                        'isApproved' => 0,
                        'status' => 'cancelled',
                    ]);
                }
            }
        }

        if ($participant->transaction && ($request->has('pic_name') || $request->has('pic_email') || $request->has('pic_phone'))) {
            $pic = is_array($participant->transaction->pic_data) ? $participant->transaction->pic_data : [];

            $picName = trim((string) $request->input('pic_name', ''));
            $picEmail = trim((string) $request->input('pic_email', ''));
            $picPhone = trim((string) $request->input('pic_phone', ''));

            if ($picName === '') {
                unset($pic['name']);
            } else {
                $pic['name'] = $picName;
            }
            if ($picEmail === '') {
                unset($pic['email']);
            } else {
                $pic['email'] = $picEmail;
            }
            if ($picPhone === '') {
                unset($pic['phone']);
            } else {
                $pic['phone'] = $picPhone;
            }

            $participant->transaction->update([
                'pic_data' => empty($pic) ? null : $pic,
            ]);
        }

        $participantData = $validated;
        $participantData['id_card'] = ! empty($participantData['id_card']) ? trim((string) $participantData['id_card']) : '-';
        unset($participantData['coupon_id'], $participantData['pic_name'], $participantData['pic_email'], $participantData['pic_phone']);
        if (array_key_exists('addons', $participantData)) {
            $eventAddonsByName = collect($event->addons ?? [])->keyBy('name');
            $normalizedAddons = [];
            foreach (($participantData['addons'] ?? []) as $addonItem) {
                $aName = trim((string) ($addonItem['name'] ?? ''));
                if ($aName === '') {
                    continue;
                }
                $matchedEventAddon = $eventAddonsByName->get($aName);
                $price = isset($addonItem['price'])
                    ? (int) $addonItem['price']
                    : ($matchedEventAddon ? (int) ($matchedEventAddon['price'] ?? 0) : 0);
                $entry = [
                    'name' => $aName,
                    'price' => $price,
                ];
                if (array_key_exists('value', $addonItem) && $addonItem['value'] !== null && $addonItem['value'] !== '') {
                    $entry['value'] = $addonItem['value'];
                }
                $normalizedAddons[] = $entry;
            }
            $participantData['addons'] = $normalizedAddons;
        }
        $participant->update($participantData);

        if ($participant->transaction) {
            $transaction = $participant->transaction;
            $event = $transaction->event;

            // Load all participants for this transaction to calculate totals
            $allParticipants = $transaction->participants()->with('category')->get();

            $categoryQuantities = [];
            foreach ($allParticipants as $p) {
                if ($p->category) {
                    $catId = $p->category->id;
                    $categoryQuantities[$catId] = ($categoryQuantities[$catId] ?? 0) + 1;
                }
            }

            $totalOriginal = 0;
            foreach ($categoryQuantities as $catId => $qty) {
                $category = \App\Models\RaceCategory::find($catId);
                if (!$category) continue;

                $catParticipants = $allParticipants->where('race_category_id', $catId);
                $categorySum = 0;

                foreach ($catParticipants as $p) {
                    $priceType = $p->price_type ?? 'regular';
                    $price = 0;
                    if ($priceType === 'early' && isset($category->price_early) && $category->price_early > 0) {
                        $price = (int) $category->price_early;
                    } elseif ($priceType === 'late' && isset($category->price_late) && $category->price_late > 0) {
                        $price = (int) $category->price_late;
                    } else {
                        $price = (int) ($category->price_regular ?? 0);
                    }
                    $categorySum += $price;
                }

                if ($event->promo_buy_x && $event->promo_buy_x > 0) {
                    $bundleSize = $event->promo_buy_x + 1;
                    $freeCount = floor($qty / $bundleSize);
                    if ($freeCount > 0) {
                        $avgPrice = $categorySum / $qty;
                        $categorySum -= $avgPrice * $freeCount;
                    }
                }

                $totalOriginal += $categorySum;
            }

            // Sum addons price
            $totalAddonsPrice = 0;
            foreach ($allParticipants as $p) {
                if (! empty($p->addons) && is_array($p->addons)) {
                    foreach ($p->addons as $addon) {
                        $price = isset($addon['price']) ? (int) $addon['price'] : (isset($addon['value']) ? (int) $addon['value'] : 0);
                        $totalAddonsPrice += $price;
                    }
                }
            }
            $totalOriginal += $totalAddonsPrice;

            // Apply coupon
            $couponId = array_key_exists('coupon_id', $validated) ? $validated['coupon_id'] : $transaction->coupon_id;
            $discountAmount = 0.00;
            if ($couponId) {
                $coupon = \App\Models\Coupon::find($couponId);
                if ($coupon) {
                    $discountAmount = (float) $coupon->applyDiscount((float) $totalOriginal);
                }
            }

            // Recalculate platform fee
            $totalParticipants = $allParticipants->count();
            $platformFeePerParticipant = $event->platform_fee ?? 0;
            
            if (($totalOriginal - $discountAmount) <= 0) {
                $totalAdminFee = 0;
            } else {
                $totalAdminFee = $platformFeePerParticipant * $totalParticipants;
            }

            $finalAmount = ($totalOriginal - $discountAmount) + $totalAdminFee;
            if ($finalAmount < 0) {
                $finalAmount = 0;
            }

            if ($transaction->payment_gateway === 'moota' && $transaction->unique_code > 0) {
                $finalAmount += (float) $transaction->unique_code;
            }

            $transaction->update([
                'coupon_id' => $couponId ?: null,
                'total_original' => $totalOriginal,
                'discount_amount' => $discountAmount,
                'admin_fee' => $totalAdminFee,
                'final_amount' => $finalAmount,
            ]);
        }

        // Refresh to get relationship data if needed (e.g. category name)
        $participant->load(['category', 'transaction.coupon', 'transaction.user']);

        return response()->json([
            'success' => true,
            'message' => 'Data peserta berhasil diperbarui.',
            'data' => [
                'id' => $participant->id,
                'name' => $participant->name,
                'bib_name' => $participant->bib_name,
                'email' => $participant->email,
                'phone' => $participant->phone,
                'id_card' => $participant->id_card,
                'emergency_contact_name' => $participant->emergency_contact_name,
                'emergency_contact_number' => $participant->emergency_contact_number,
                'emergency_contact_relation' => $participant->emergency_contact_relation,
                'gender' => $participant->gender,
                'date_of_birth' => $participant->date_of_birth ? $participant->date_of_birth->toDateString() : null,
                'address' => $participant->address,
                'city' => $participant->city,
                'province' => $participant->province,
                'postal_code' => $participant->postal_code,
                'race_category_id' => $participant->race_category_id,
                'category_name' => $participant->category->name ?? '-',
                'target_time' => $participant->target_time,
                'bib_number' => $participant->bib_number,
                'jersey_size' => $participant->jersey_size,
                'blood_type' => $participant->blood_type,
                'medical_history' => $participant->medical_history,
                'age_group' => $participant->getAgeGroup($event->start_at) . ($participant->date_of_birth ? ' (' . number_format($participant->date_of_birth->floatDiffInYears($event->start_at), 1) . ' thn)' : ''),
                'is_picked_up' => $participant->is_picked_up,
                'picked_up_at' => $participant->picked_up_at,
                'picked_up_by' => $participant->picked_up_by,
                'payment_status' => $participant->transaction?->payment_status ?? 'pending',
                'coupon_id' => $participant->transaction->coupon_id ?? null,
                'coupon_code' => $participant->transaction->coupon?->code ?? null,
                'pic_name' => $participant->pic_name,
                'pic_phone' => $participant->pic_phone,
                'pic_email' => $participant->pic_email,
                'addons' => $participant->addons,
                'notes' => $participant->notes,
            ],
        ]);
    }

    /**
     * Resend event registration email to a specific participant
     */
    public function resendEmail(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $request->validate([
            'participant_id' => 'required|exists:participants,id',
            'custom_html' => 'nullable|string',
        ]);

        $participant = \App\Models\Participant::with('transaction')->find($request->participant_id);

        $participantEventId = (int) ($participant?->transaction?->event_id ?? 0);
        if (! $participant || $participantEventId !== (int) $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Participant not found or does not belong to this event.',
            ], 404);
        }

        try {
            // Construct data for the email
            // We pass a collection containing only this participant so the email is specific to them
            $participants = collect([$participant]);

            Mail::to($participant->email)->send(
                new EventRegistrationSuccess(
                    $event,
                    $participant->transaction,
                    $participants,
                    $participant->name,
                    $request->custom_html
                )
            );

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully to '.$participant->email,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Resend Email Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resend event registration email to multiple participants in bulk
     */
    public function resendEmailBulk(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $request->validate([
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'exists:participants,id',
            'custom_html' => 'nullable|string',
        ]);

        $participants = \App\Models\Participant::with('transaction')
            ->whereIn('id', $request->participant_ids)
            ->whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            })
            ->get();

        if ($participants->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada peserta valid yang ditemukan.',
            ], 400);
        }

        $sentCount = 0;
        $errors = [];

        foreach ($participants as $participant) {
            try {
                Mail::to($participant->email)->send(
                    new EventRegistrationSuccess(
                        $event,
                        $participant->transaction,
                        collect([$participant]),
                        $participant->name,
                        $request->custom_html
                    )
                );
                $sentCount++;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Bulk Resend Email Error for ' . $participant->email . ': ' . $e->getMessage());
                $errors[] = $participant->email;
            }
        }

        if ($sentCount > 0) {
            $msg = "Berhasil mengirim {$sentCount} email konfirmasi tiket.";
            if (count($errors) > 0) {
                $msg .= " Gagal mengirim ke: " . implode(', ', $errors);
            }
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim email konfirmasi tiket ke semua peserta terpilih.',
        ], 500);
    }

    /**
     * Approve single participant and dispatch confirmation email
     */
    public function approveParticipant(Request $request, Event $event, \App\Models\Participant $participant)
    {
        $this->authorizeEvent($event);

        if ((int) ($participant->transaction?->event_id ?? $participant->category?->event_id ?? 0) !== (int) $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta tidak ditemukan pada event ini.',
            ], 404);
        }

        if ($participant->transaction && in_array($participant->transaction->payment_status, ['failed', 'expired'])) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui: Status pembayaran peserta adalah ' . $participant->transaction->payment_status . '.',
            ], 422);
        }

        // Validate category quota before approving (Option A quota control)
        if ($participant->category && $participant->category->quota !== null) {
            $catId = $participant->category->id;
            $approvedCount = \App\Models\Participant::where('race_category_id', $catId)
                ->where('isApproved', 1)
                ->where('status', '!=', 'cancelled')
                ->where('id', '!=', $participant->id)
                ->count();
            if ($approvedCount >= $participant->category->quota) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyetujui: Kuota untuk kategori ' . $participant->category->name . ' sudah penuh (' . $participant->category->quota . ' peserta).',
                ], 422);
            }
        }

        $participant->update([
            'isApproved' => 1,
            'status' => 'confirmed',
        ]);

        if ($participant->transaction && ($participant->transaction->payment_gateway === 'free' || (float)($participant->transaction->final_amount ?? 0) <= 0)) {
            $participant->transaction->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);
        }

        if ($participant->category) {
            try {
                app(\App\Services\EventCacheService::class)->invalidateCategoryCache($participant->category);
            } catch (\Throwable $e) {}
        }

        // Generate BIB if missing (global per-event increment)
        if (empty($participant->bib_number)) {
            $this->assignNextEventBibNumber($event, $participant);
        }

        // Send email confirmation
        $emailSent = false;
        try {
            if (! empty($participant->email)) {
                Mail::to($participant->email)->send(
                    new EventRegistrationSuccess(
                        $event,
                        $participant->transaction,
                        collect([$participant]),
                        $participant->name
                    )
                );
                $emailSent = true;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Approval Email Error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Peserta ' . $participant->name . ' berhasil disetujui (Approved)' . ($emailSent ? ' dan e-Tiket telah dikirim.' : '.'),
        ]);
    }

    /**
     * Reject single participant
     */
    public function rejectParticipant(Request $request, Event $event, \App\Models\Participant $participant)
    {
        $this->authorizeEvent($event);

        if ((int) ($participant->transaction?->event_id ?? $participant->category?->event_id ?? 0) !== (int) $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta tidak ditemukan pada event ini.',
            ], 404);
        }

        $reason = $request->input('reason', 'Pendaftaran ditolak oleh panitia.');

        $participant->update([
            'isApproved' => 0,
            'status' => 'cancelled',
            'notes' => $participant->notes ? ($participant->notes . ' | Alasan Ditolak: ' . $reason) : ('Alasan Ditolak: ' . $reason),
        ]);

        if ($participant->category) {
            try {
                app(\App\Services\EventCacheService::class)->invalidateCategoryCache($participant->category);
            } catch (\Throwable $e) {}
        }

        $emailSent = false;
        try {
            if (! empty($participant->email)) {
                Mail::to($participant->email)->send(
                    new EventParticipantRejectedMail($event, $participant, $reason)
                );
                $emailSent = true;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Participant Rejection Email Error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Peserta ' . $participant->name . ' telah ditolak (Rejected)' . ($emailSent ? ' dan email pemberitahuan penolakan telah dikirim.' : '.'),
        ]);
    }

    /**
     * Bulk approve participants
     */
    public function bulkApproveParticipants(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $request->validate([
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'exists:participants,id',
        ]);

        $participants = \App\Models\Participant::with(['transaction', 'category'])
            ->whereIn('id', $request->participant_ids)
            ->whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            })
            ->get();

        if ($participants->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada peserta valid yang dipilih.',
            ], 400);
        }

        $approvedCount = 0;
        $errors = [];
        $affectedCategories = [];

        foreach ($participants as $participant) {
            if ($participant->transaction && in_array($participant->transaction->payment_status, ['failed', 'expired'])) {
                $errors[] = "Pembayaran untuk {$participant->name} berstatus {$participant->transaction->payment_status}";
                continue;
            }

            if ($participant->category && $participant->category->quota !== null) {
                $catId = $participant->category->id;
                $approvedCountInCat = \App\Models\Participant::where('race_category_id', $catId)
                    ->where('isApproved', 1)
                    ->where('status', '!=', 'cancelled')
                    ->where('id', '!=', $participant->id)
                    ->count();
                if ($approvedCountInCat >= $participant->category->quota) {
                    $errors[] = "Kuota kategori {$participant->category->name} sudah penuh untuk {$participant->name}";
                    continue;
                }
            }

            $participant->update([
                'isApproved' => 1,
                'status' => 'confirmed',
            ]);

            if ($participant->transaction && ($participant->transaction->payment_gateway === 'free' || (float)($participant->transaction->final_amount ?? 0) <= 0)) {
                $participant->transaction->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            if ($participant->category) {
                $affectedCategories[$participant->category->id] = $participant->category;
            }

            // Generate BIB if missing (global per-event increment)
            if (empty($participant->bib_number)) {
                $this->assignNextEventBibNumber($event, $participant);
            }

            try {
                if (! empty($participant->email)) {
                    Mail::to($participant->email)->send(
                        new EventRegistrationSuccess(
                            $event,
                            $participant->transaction,
                            collect([$participant]),
                            $participant->name
                        )
                    );
                }
                $approvedCount++;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Bulk Approval Email Error for ' . $participant->email . ': ' . $e->getMessage());
                $errors[] = $participant->email;
                $approvedCount++;
            }
        }

        foreach ($affectedCategories as $cat) {
            try {
                app(\App\Services\EventCacheService::class)->invalidateCategoryCache($cat);
            } catch (\Throwable $e) {}
        }

        $msg = "Berhasil menyetujui {$approvedCount} peserta dan mengirimkan email konfirmasi e-Tiket.";
        if (! empty($errors)) {
            $msg .= ' Catatan: ' . implode(', ', array_slice($errors, 0, 3));
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
        ]);
    }

    /**
     * Bulk reject participants
     */
    public function bulkRejectParticipants(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $request->validate([
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'exists:participants,id',
            'reason' => 'nullable|string|max:255',
        ]);

        $reason = $request->input('reason', 'Pendaftaran ditolak oleh panitia.');

        $participants = \App\Models\Participant::with(['transaction', 'category'])
            ->whereIn('id', $request->participant_ids)
            ->whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            })
            ->get();

        if ($participants->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada peserta valid yang dipilih.',
            ], 400);
        }

        $rejectedCount = 0;
        $emailSentCount = 0;

        foreach ($participants as $participant) {
            $participant->update([
                'isApproved' => 0,
                'status' => 'cancelled',
                'notes' => $participant->notes ? ($participant->notes . ' | Alasan Ditolak: ' . $reason) : ('Alasan Ditolak: ' . $reason),
            ]);

            if ($participant->category) {
                try {
                    app(\App\Services\EventCacheService::class)->invalidateCategoryCache($participant->category);
                } catch (\Throwable $e) {}
            }

            try {
                if (! empty($participant->email)) {
                    Mail::to($participant->email)->send(
                        new EventParticipantRejectedMail($event, $participant, $reason)
                    );
                    $emailSentCount++;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Bulk Participant Rejection Email Error: ' . $e->getMessage());
            }

            $rejectedCount++;
        }

        $msg = "Berhasil menolak {$rejectedCount} peserta terpilih";
        if ($emailSentCount > 0) {
            $msg .= " dan {$emailSentCount} email penolakan telah dikirim.";
        } else {
            $msg .= '.';
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
        ]);
    }

    /**
     * Export participants as CSV
     */
    public function exportParticipants(Event $event)
    {
        $this->authorizeEvent($event);

        \Illuminate\Support\Facades\Log::info('participants_export_csv_start', [
            'event_id' => $event->id,
            'payment_status' => request()->payment_status,
            'is_picked_up' => request()->is_picked_up,
            'gender' => request()->gender,
            'category_id' => request()->category_id,
            'addon' => request()->query('addon'),
        ]);

        $query = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })
            ->with(['transaction.coupon', 'transaction.participants', 'category']);

        // Apply same filters as list
        if (request()->has('payment_status') && request()->payment_status) {
            $query->whereHas('transaction', function ($q) {
                $q->where('payment_status', request()->payment_status);
            });
        }

        if (request()->has('payment_gateway') && request()->payment_gateway) {
            $query->whereHas('transaction', function ($q) {
                $q->where('payment_gateway', request()->payment_gateway);
            });
        }

        if (request()->has('is_picked_up') && request()->is_picked_up !== '') {
            $query->where('is_picked_up', request()->is_picked_up == '1');
        }

        // Filter by gender
        if (request()->has('gender') && request()->gender) {
            $query->where('gender', request()->gender);
        }

        // Filter by category
        if (request()->has('category_id') && request()->category_id) {
            $query->where('race_category_id', request()->category_id);
        }

        // Filter by coupon
        if (request()->has('coupon_id') && request()->coupon_id) {
            $couponFilter = request()->coupon_id;
            if ($couponFilter === 'with') {
                $query->whereHas('transaction', function ($q) {
                    $q->whereNotNull('coupon_id');
                });
            } elseif ($couponFilter === 'without') {
                $query->whereHas('transaction', function ($q) {
                    $q->whereNull('coupon_id');
                });
            } else {
                $query->whereHas('transaction', function ($q) use ($couponFilter) {
                    $q->where('coupon_id', $couponFilter);
                });
            }
        }

        if (request()->filled('addon')) {
            $addonFilter = request()->query('addon');
            if ($addonFilter === 'with') {
                $query->whereNotNull('addons')->whereJsonLength('addons', '>', 0);
            } elseif ($addonFilter === 'without') {
                $query->where(function ($q) {
                    $q->whereNull('addons')->orWhereJsonLength('addons', 0);
                });
            } else {
                $query->whereJsonContains('addons', ['name' => $addonFilter]);
            }
        }

        // Filter by Jersey Size
        if (request()->filled('jersey_size')) {
            $jerseySizeFilter = request()->query('jersey_size');
            $matchSizes = [strtoupper($jerseySizeFilter)];
            if (in_array(strtoupper($jerseySizeFilter), ['2XL', 'XXL'])) {
                $matchSizes = ['2XL', 'XXL'];
            } elseif (in_array(strtoupper($jerseySizeFilter), ['3XL', 'XXXL'])) {
                $matchSizes = ['3XL', 'XXXL'];
            }
            $query->whereNotNull('jersey_size')->whereIn(\DB::raw('UPPER(TRIM(jersey_size))'), $matchSizes);
        }

        // Filter by Age Group
        if (request()->has('age_group') && request()->age_group) {
            $query->filterAgeGroup(request()->age_group, $event->start_at);
        }

        // Filter by Min Age
        if (request()->filled('min_age')) {
            $minAge = (int) request()->min_age;
            $eventDate = $event->start_at ?: now();
            $query->whereDate('date_of_birth', '<=', $eventDate->copy()->subYears($minAge));
        }

        // Filter by Max Age
        if (request()->filled('max_age')) {
            $maxAge = (int) request()->max_age;
            $eventDate = $event->start_at ?: now();
            $query->whereDate('date_of_birth', '>', $eventDate->copy()->subYears($maxAge + 1));
        }

        if (request()->has('search') && trim(request()->search) !== '') {
            $search = trim(request()->search);
            $query->where(function ($qq) use ($search) {
                $qq->where('participants.name', 'like', "%{$search}%")
                    ->orWhere('participants.email', 'like', "%{$search}%")
                    ->orWhere('participants.phone', 'like', "%{$search}%")
                    ->orWhere('participants.bib_number', 'like', "%{$search}%")
                    ->orWhere('participants.id_card', 'like', "%{$search}%")
                    ->orWhere('participants.address', 'like', "%{$search}%")
                    ->orWhere('participants.city', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('transaction', function ($t) use ($search) {
                        $t->where(function ($st) use ($search) {
                            $st->where('public_ref', 'like', "%{$search}%");
                            if (is_numeric($search) && (int) $search > 0) {
                                $st->orWhere('unique_code', (int) $search);
                            }
                        })->orWhere(function ($jt) use ($search) {
                            $jt->where('pic_data->name', 'like', "%{$search}%")
                                ->orWhere('pic_data->email', 'like', "%{$search}%")
                                ->orWhere('pic_data->phone', 'like', "%{$search}%");
                        });
                    });
            });
        }

        $filename = 'participants_'.$event->slug.'_'.date('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $queryForStream = clone $query;

        $callback = function () use ($queryForStream) {
            $file = fopen('php://output', 'w');

            // BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($file, \App\Services\GoogleSheetsParticipantExporter::OUTPUT_COLUMNS);

            // Data
            $rowNumber = 0;
            $queryForStream->join('transactions', 'participants.transaction_id', '=', 'transactions.id')
                ->leftJoin('coupons', 'transactions.coupon_id', '=', 'coupons.id')
                ->select('participants.*')
                ->orderBy('coupons.code', 'asc')
                ->orderBy('participants.created_at', 'asc')
                ->chunk(1000, function ($participants) use ($file, &$rowNumber) {
                foreach ($participants as $participant) {
                    $rowNumber++;
                    
                    $priceType = $participant->price_type ?? 'regular';
                    $ticketPrice = 0;
                    if ($participant->category) {
                        if ($priceType === 'early' && isset($participant->category->price_early) && $participant->category->price_early > 0) {
                            $ticketPrice = (float) $participant->category->price_early;
                        } elseif ($priceType === 'late' && isset($participant->category->price_late) && $participant->category->price_late > 0) {
                            $ticketPrice = (float) $participant->category->price_late;
                        } else {
                            $ticketPrice = (float) ($participant->category->price_regular ?? 0);
                        }
                    }

                    $txParticipantsCount = $participant->transaction && $participant->transaction->participants ? $participant->transaction->participants->count() : 1;
                    $txParticipantsCount = max(1, $txParticipantsCount);

                    $couponDiscount = $participant->transaction ? ((float) $participant->transaction->discount_amount / $txParticipantsCount) : 0.0;
                    $platformFee = $participant->transaction ? ((float) $participant->transaction->admin_fee / $txParticipantsCount) : 0.0;

                    $rawGateway = $participant->transaction->payment_gateway ?? '-';
                    $inputMethodName = match($rawGateway) {
                        'manual' => 'Manual',
                        'manual_csv' => 'Manual CSV',
                        'midtrans' => 'Midtrans',
                        'moota' => 'Moota',
                        'cod' => 'COD',
                        'community_import' => 'Community Import',
                        '-' => '-',
                        default => ucfirst($rawGateway),
                    };

                    fputcsv($file, [
                        $rowNumber,
                        $participant->name,
                        $participant->bib_name ?? '-',
                        ucfirst($participant->gender ?? '-'),
                        $participant->date_of_birth ? $participant->date_of_birth->format('Y-m-d') : '-',
                        $participant->email,
                        $participant->phone,
                        $participant->id_card,
                        $participant->address ?? '-',
                        $participant->category ? $participant->category->name : '-',
                        $participant->bib_number ?? '-',
                        $participant->jersey_size ?? '-',
                        $participant->blood_type ?? '-',
                        $participant->medical_history ?? '-',
                        (! empty($participant->addons) && is_array($participant->addons)) ? collect($participant->addons)->pluck('name')->filter()->implode(', ') : '-',
                        $participant->target_time ?? '-',
                        $participant->transaction && $participant->transaction->coupon ? $participant->transaction->coupon->code : '-',
                        $couponDiscount,
                        $ticketPrice,
                        $platformFee,
                        $participant->emergency_contact_name ?? '-',
                        $participant->emergency_contact_number ?? '-',
                        $participant->emergency_contact_relation ?? '-',
                        ucfirst($participant->transaction->payment_status ?? 'pending'),
                        $inputMethodName,
                        $participant->is_picked_up ? 'Sudah Diambil' : 'Belum Diambil',
                        $participant->created_at ? $participant->created_at->format('Y-m-d H:i:s') : '-',
                        $participant->picked_up_at ? $participant->picked_up_at->format('Y-m-d H:i:s') : '-',
                        $participant->picked_up_by ?? '-',
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportParticipantsXlsx(Event $event)
    {
        $this->authorizeEvent($event);

        \Illuminate\Support\Facades\Log::info('participants_export_xlsx_start', [
            'event_id' => $event->id,
            'payment_status' => request()->payment_status,
            'is_picked_up' => request()->is_picked_up,
            'gender' => request()->gender,
            'category_id' => request()->category_id,
            'addon' => request()->query('addon'),
        ]);

        $query = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })
            ->with(['transaction.coupon', 'transaction.participants', 'category']);

        if (request()->has('payment_status') && request()->payment_status) {
            $query->whereHas('transaction', function ($q) {
                $q->where('payment_status', request()->payment_status);
            });
        }

        if (request()->has('payment_gateway') && request()->payment_gateway) {
            $query->whereHas('transaction', function ($q) {
                $q->where('payment_gateway', request()->payment_gateway);
            });
        }

        if (request()->has('is_picked_up') && request()->is_picked_up !== '') {
            $query->where('is_picked_up', request()->is_picked_up == '1');
        }

        if (request()->has('gender') && request()->gender) {
            $query->where('gender', request()->gender);
        }

        if (request()->has('category_id') && request()->category_id) {
            $query->where('race_category_id', request()->category_id);
        }

        // Filter by coupon
        if (request()->has('coupon_id') && request()->coupon_id) {
            $couponFilter = request()->coupon_id;
            if ($couponFilter === 'with') {
                $query->whereHas('transaction', function ($q) {
                    $q->whereNotNull('coupon_id');
                });
            } elseif ($couponFilter === 'without') {
                $query->whereHas('transaction', function ($q) {
                    $q->whereNull('coupon_id');
                });
            } else {
                $query->whereHas('transaction', function ($q) use ($couponFilter) {
                    $q->where('coupon_id', $couponFilter);
                });
            }
        }

        if (request()->filled('addon')) {
            $addonFilter = request()->query('addon');
            if ($addonFilter === 'with') {
                $query->whereNotNull('addons')->whereJsonLength('addons', '>', 0);
            } elseif ($addonFilter === 'without') {
                $query->where(function ($q) {
                    $q->whereNull('addons')->orWhereJsonLength('addons', 0);
                });
            } else {
                $query->whereJsonContains('addons', ['name' => $addonFilter]);
            }
        }

        // Filter by Jersey Size
        if (request()->filled('jersey_size')) {
            $jerseySizeFilter = request()->query('jersey_size');
            $matchSizes = [strtoupper($jerseySizeFilter)];
            if (in_array(strtoupper($jerseySizeFilter), ['2XL', 'XXL'])) {
                $matchSizes = ['2XL', 'XXL'];
            } elseif (in_array(strtoupper($jerseySizeFilter), ['3XL', 'XXXL'])) {
                $matchSizes = ['3XL', 'XXXL'];
            }
            $query->whereNotNull('jersey_size')->whereIn(\DB::raw('UPPER(TRIM(jersey_size))'), $matchSizes);
        }

        // Filter by Age Group
        if (request()->has('age_group') && request()->age_group) {
            $query->filterAgeGroup(request()->age_group, $event->start_at);
        }

        // Filter by Min Age
        if (request()->filled('min_age')) {
            $minAge = (int) request()->min_age;
            $eventDate = $event->start_at ?: now();
            $query->whereDate('date_of_birth', '<=', $eventDate->copy()->subYears($minAge));
        }

        // Filter by Max Age
        if (request()->filled('max_age')) {
            $maxAge = (int) request()->max_age;
            $eventDate = $event->start_at ?: now();
            $query->whereDate('date_of_birth', '>', $eventDate->copy()->subYears($maxAge + 1));
        }

        if (request()->has('search') && trim(request()->search) !== '') {
            $search = trim(request()->search);
            $query->where(function ($qq) use ($search) {
                $qq->where('participants.name', 'like', "%{$search}%")
                    ->orWhere('participants.email', 'like', "%{$search}%")
                    ->orWhere('participants.phone', 'like', "%{$search}%")
                    ->orWhere('participants.bib_number', 'like', "%{$search}%")
                    ->orWhere('participants.id_card', 'like', "%{$search}%")
                    ->orWhere('participants.address', 'like', "%{$search}%")
                    ->orWhere('participants.city', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('transaction', function ($t) use ($search) {
                        $t->where(function ($st) use ($search) {
                            $st->where('public_ref', 'like', "%{$search}%");
                            if (is_numeric($search) && (int) $search > 0) {
                                $st->orWhere('unique_code', (int) $search);
                            }
                        })->orWhere(function ($jt) use ($search) {
                            $jt->where('pic_data->name', 'like', "%{$search}%")
                                ->orWhere('pic_data->email', 'like', "%{$search}%")
                                ->orWhere('pic_data->phone', 'like', "%{$search}%");
                        });
                    });
            });
        }

        $filename = 'participants_'.$event->slug.'_'.date('Y-m-d').'.xlsx';

        $queryForStream = clone $query;

        return response()->streamDownload(function () use ($queryForStream) {
            $writer = new \OpenSpout\Writer\XLSX\Writer;
            $writer->openToFile('php://output');

            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(\App\Services\GoogleSheetsParticipantExporter::OUTPUT_COLUMNS));

            $rowNumber = 0;
            $queryForStream->join('transactions', 'participants.transaction_id', '=', 'transactions.id')
                ->leftJoin('coupons', 'transactions.coupon_id', '=', 'coupons.id')
                ->select('participants.*')
                ->orderBy('coupons.code', 'asc')
                ->orderBy('participants.created_at', 'asc')
                ->chunk(1000, function ($participants) use (&$rowNumber, $writer) {
                $rows = [];
                foreach ($participants as $participant) {
                    $rowNumber++;

                    $priceType = $participant->price_type ?? 'regular';
                    $ticketPrice = 0;
                    if ($participant->category) {
                        if ($priceType === 'early' && isset($participant->category->price_early) && $participant->category->price_early > 0) {
                            $ticketPrice = (float) $participant->category->price_early;
                        } elseif ($priceType === 'late' && isset($participant->category->price_late) && $participant->category->price_late > 0) {
                            $ticketPrice = (float) $participant->category->price_late;
                        } else {
                            $ticketPrice = (float) ($participant->category->price_regular ?? 0);
                        }
                    }

                    $txParticipantsCount = $participant->transaction && $participant->transaction->participants ? $participant->transaction->participants->count() : 1;
                    $txParticipantsCount = max(1, $txParticipantsCount);

                    $couponDiscount = $participant->transaction ? ((float) $participant->transaction->discount_amount / $txParticipantsCount) : 0.0;
                    $platformFee = $participant->transaction ? ((float) $participant->transaction->admin_fee / $txParticipantsCount) : 0.0;

                    $rawGateway = $participant->transaction->payment_gateway ?? '-';
                    $inputMethodName = match($rawGateway) {
                        'manual' => 'Manual',
                        'manual_csv' => 'Manual CSV',
                        'midtrans' => 'Midtrans',
                        'moota' => 'Moota',
                        'cod' => 'COD',
                        'community_import' => 'Community Import',
                        '-' => '-',
                        default => ucfirst($rawGateway),
                    };

                    $rows[] = \OpenSpout\Common\Entity\Row::fromValues([
                        $rowNumber,
                        $participant->name,
                        $participant->bib_name ?? '-',
                        ucfirst($participant->gender ?? '-'),
                        $participant->date_of_birth ? $participant->date_of_birth->format('Y-m-d') : '-',
                        $participant->email,
                        $participant->phone,
                        $participant->id_card,
                        $participant->address ?? '-',
                        $participant->category ? $participant->category->name : '-',
                        $participant->bib_number ?? '-',
                        $participant->jersey_size ?? '-',
                        $participant->blood_type ?? '-',
                        $participant->medical_history ?? '-',
                        (! empty($participant->addons) && is_array($participant->addons)) ? collect($participant->addons)->pluck('name')->filter()->implode(', ') : '-',
                        $participant->target_time ?? '-',
                        $participant->transaction && $participant->transaction->coupon ? $participant->transaction->coupon->code : '-',
                        $couponDiscount,
                        $ticketPrice,
                        $platformFee,
                        $participant->emergency_contact_name ?? '-',
                        $participant->emergency_contact_number ?? '-',
                        $participant->emergency_contact_relation ?? '-',
                        ucfirst($participant->transaction->payment_status ?? 'pending'),
                        $inputMethodName,
                        $participant->is_picked_up ? 'Sudah Diambil' : 'Belum Diambil',
                        $participant->created_at ? $participant->created_at->format('Y-m-d H:i:s') : '-',
                        $participant->picked_up_at ? $participant->picked_up_at->format('Y-m-d H:i:s') : '-',
                        $participant->picked_up_by ?? '-',
                    ]);
                }
                if ($rows) {
                    $writer->addRows($rows);
                }
            });

            $writer->close();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function saveBibCheckSettings(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'take_last' => 'nullable|string|max:50',
            'prefix' => 'nullable|string|max:30',
            'max_name_length' => 'nullable|integer|min:5|max:60',
            'image_base64' => 'nullable|string',
            'canvas_width' => 'nullable|numeric|min:50|max:5000',
            'canvas_height' => 'nullable|numeric|min:50|max:5000',
            'texts' => 'nullable|array|max:30',
        ]);

        $amenities = is_array($event->premium_amenities) ? $event->premium_amenities : [];
        $existing = is_array($amenities['bib_check'] ?? null) ? $amenities['bib_check'] : [];

        $bgImagePath = $existing['bg_image'] ?? null;
        if (! empty($validated['image_base64']) && preg_match('/^data:image\/(png|jpe?g);base64,(.+)$/i', $validated['image_base64'], $matches)) {
            $ext = strtolower($matches[1]) === 'png' ? 'png' : 'jpg';
            $decoded = base64_decode($matches[2], true);
            if ($decoded !== false) {
                $bgImagePath = 'bib-check/event_' . $event->id . '.' . $ext;
                Storage::disk('public')->put($bgImagePath, $decoded);
            }
        }

        $amenities['bib_check'] = [
            'take_last' => trim((string) ($validated['take_last'] ?? '')),
            'prefix' => (string) ($validated['prefix'] ?? ''),
            'max_name_length' => (int) ($validated['max_name_length'] ?? ($existing['max_name_length'] ?? 18)),
            'bg_image' => $bgImagePath,
            'canvas_width' => isset($validated['canvas_width']) ? (float) $validated['canvas_width'] : ($existing['canvas_width'] ?? null),
            'canvas_height' => isset($validated['canvas_height']) ? (float) $validated['canvas_height'] : ($existing['canvas_height'] ?? null),
            'texts' => array_key_exists('texts', $validated) ? ($validated['texts'] ?? []) : ($existing['texts'] ?? []),
        ];

        $event->premium_amenities = $amenities;
        $event->save();

        return response()->json([
            'success' => true,
            'message' => 'Desain & format BIB Check berhasil disimpan.',
            'bib_check' => $amenities['bib_check'],
        ]);
    }

    public function printBibPdf(Request $request, Event $event)
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        $this->authorizeEvent($event);

        $request->validate([
            'image_base64' => 'required|string',
            'bg_width' => 'required|numeric',
            'bg_height' => 'required|numeric',
            'scale' => 'required|numeric',
            'texts' => 'required|array',
            'take_last' => 'nullable|string',
            'prefix' => 'nullable|string',
            'max_name_length' => 'nullable|integer|min:0|max:255',
            'auto_format_name' => 'nullable|boolean',
            'overrides' => 'nullable|array',
            'single_participant_id' => 'nullable|integer|exists:participants,id',
            'is_custom_range' => 'nullable|boolean',
            'custom_from' => 'nullable|integer|min:0',
            'custom_to' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'imposition' => 'nullable|array',
            'imposition.paperSize' => 'nullable|string|in:ORIGINAL,A3,A3+',
            'imposition.orientation' => 'nullable|string|in:auto,L,P',
            'imposition.rows' => 'nullable|integer|min:1|max:10',
            'imposition.cols' => 'nullable|integer|min:1|max:10',
            'imposition.mode' => 'nullable|string|in:single,step-repeat,vdp,cut-stack',
            'imposition.gapMm' => 'nullable|numeric|min:0|max:30',
            'imposition.cropMarks' => 'nullable|boolean',
            'imposition.previewFirstSheetOnly' => 'nullable|boolean',
        ]);

        if ($request->boolean('is_custom_range')) {
            $from = (int) $request->custom_from;
            $to = (int) $request->custom_to;

            if (($to - $from) > 5000) {
                return response()->json(['success' => false, 'message' => 'Rentang kustom maksimal adalah 5000 nomor BIB sekali cetak.']);
            }

            $participants = collect();
            for ($i = $from; $i <= $to; $i++) {
                $padLength = strlen(strval($to));
                $bibStr = str_pad(strval($i), $padLength, '0', STR_PAD_LEFT);

                $virtualParticipant = new \App\Models\Participant([
                    'id' => $i,
                    'bib_number' => $bibStr,
                    'name' => '-',
                    'gender' => '-',
                    'blood_type' => '-',
                    'emergency_contact_number' => '-',
                    'emergency_contact_name' => '-',
                ]);
                $participants->push($virtualParticipant);
            }
        } else {
            // Print BIB hanya mengambil peserta yang sudah Paid & Approved (bukan cancelled/rejected)
            $query = \App\Models\Participant::where('participants.isApproved', 1)
                ->whereNotIn('participants.status', ['cancelled', 'rejected'])
                ->whereHas('transaction', function ($q) use ($event) {
                    $q->where('event_id', $event->id)
                        ->whereIn('payment_status', ['paid', 'settlement', 'capture']);
                })
                ->with(['transaction.coupon', 'category']);

            if ($request->filled('single_participant_id')) {
                $query->where('participants.id', $request->single_participant_id);
            } else {
                // Filters (payment_status diabaikan agar tetap terkunci pada Paid & Approved)
                if ($request->has('is_picked_up') && $request->is_picked_up !== '') {
                    $query->where('is_picked_up', $request->is_picked_up == '1');
                }
                if ($request->filled('gender')) {
                    $query->where('gender', $request->gender);
                }
                if ($request->filled('category_id')) {
                    $query->where('race_category_id', $request->category_id);
                }
                if ($request->filled('coupon_id')) {
                    $query->whereHas('transaction', function ($q) use ($request) {
                        $q->where('coupon_id', $request->coupon_id);
                    });
                }
                if ($request->filled('addon')) {
                    $addonFilter = $request->query('addon');
                    if ($addonFilter === 'with') {
                        $query->whereNotNull('addons')->whereJsonLength('addons', '>', 0);
                    } elseif ($addonFilter === 'without') {
                        $query->where(function ($q) {
                            $q->whereNull('addons')->orWhereJsonLength('addons', 0);
                        });
                    } else {
                        $query->whereJsonContains('addons', ['name' => $addonFilter]);
                    }
                }
                if ($request->filled('jersey_size')) {
                    $jerseySizeFilter = $request->query('jersey_size');
                    $matchSizes = [strtoupper($jerseySizeFilter)];
                    if (in_array(strtoupper($jerseySizeFilter), ['2XL', 'XXL'])) {
                        $matchSizes = ['2XL', 'XXL'];
                    } elseif (in_array(strtoupper($jerseySizeFilter), ['3XL', 'XXXL'])) {
                        $matchSizes = ['3XL', 'XXXL'];
                    }
                    $query->whereNotNull('jersey_size')->whereIn(\DB::raw('UPPER(TRIM(jersey_size))'), $matchSizes);
                }
                if ($request->filled('age_group')) {
                    $query->filterAgeGroup($request->age_group, $event->start_at);
                }
                if ($request->filled('min_age')) {
                    $minAge = (int) $request->min_age;
                    $eventDate = $event->start_at ?: now();
                    $query->whereDate('date_of_birth', '<=', $eventDate->copy()->subYears($minAge));
                }
                if ($request->filled('max_age')) {
                    $maxAge = (int) $request->max_age;
                    $eventDate = $event->start_at ?: now();
                    $query->whereDate('date_of_birth', '>', $eventDate->copy()->subYears($maxAge + 1));
                }
                if ($request->filled('start_date')) {
                    $query->whereDate('participants.created_at', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $query->whereDate('participants.created_at', '<=', $request->end_date);
                }

                if ($request->filled('search')) {
                    $search = trim($request->search);
                    $query->where(function ($qq) use ($search) {
                        $qq->where('participants.name', 'like', "%{$search}%")
                            ->orWhere('participants.email', 'like', "%{$search}%")
                            ->orWhere('participants.phone', 'like', "%{$search}%")
                            ->orWhere('participants.bib_number', 'like', "%{$search}%")
                            ->orWhere('participants.id_card', 'like', "%{$search}%")
                            ->orWhere('participants.address', 'like', "%{$search}%")
                            ->orWhere('participants.city', 'like', "%{$search}%")
                            ->orWhereHas('category', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('transaction', function ($t) use ($search) {
                                $t->where(function ($st) use ($search) {
                                    $st->where('public_ref', 'like', "%{$search}%");
                                    if (is_numeric($search) && (int) $search > 0) {
                                        $st->orWhere('unique_code', (int) $search);
                                    }
                                })->orWhere(function ($jt) use ($search) {
                                    $jt->where('pic_data->name', 'like', "%{$search}%")
                                        ->orWhere('pic_data->email', 'like', "%{$search}%")
                                        ->orWhere('pic_data->phone', 'like', "%{$search}%");
                                });
                            });
                    });
                }
            }

            $participants = $query->orderBy('bib_number')->get();

            if ($participants->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada peserta berstatus Paid & Approved yang cocok untuk dicetak.']);
            }
        }

        // Decode image
        $imageBase64 = $request->image_base64;
        if (preg_match('/^data:image\/(\w+);base64,/', $imageBase64, $type)) {
            $imageBase64 = substr($imageBase64, strpos($imageBase64, ',') + 1);
            $type = strtolower($type[1]);
            if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                return response()->json(['success' => false, 'message' => 'Format gambar tidak didukung']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Format base64 tidak valid']);
        }

        $imageData = base64_decode($imageBase64);
        
        $tempImgPath = sys_get_temp_dir() . '/' . uniqid('bib_bg_') . '.' . $type;
        file_put_contents($tempImgPath, $imageData);
        
        $canvasWidthPx = $request->bg_width * $request->scale;
        $canvasHeightPx = $request->bg_height * $request->scale;
        
        // Ensure custom fonts are converted/imported in TCPDF dynamically
        $tcpdfFontPath = \TCPDF_FONTS::_getfontpath();
        $customFonts = [
            'montserrat' => 'Montserrat-Regular.ttf',
            'montserratb' => 'Montserrat-Bold.ttf',
            'montserratextrab' => 'Montserrat-ExtraBold.ttf',
            'bebasneue' => 'BebasNeue-Regular.ttf',
            'anton' => 'Anton-Regular.ttf'
        ];

        foreach ($customFonts as $fontName => $ttfFile) {
            if (!file_exists($tcpdfFontPath . $fontName . '.php')) {
                $ttfPath = storage_path('app/public/fonts/' . $ttfFile);
                if (file_exists($ttfPath)) {
                    try {
                        \TCPDF_FONTS::addTTFfont($ttfPath, 'TrueTypeUnicode', '', 96);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Failed to auto-import font {$fontName}: " . $e->getMessage());
                    }
                }
            }
        }
        
        try {
            $impositionInput = is_array($request->input('imposition')) ? $request->input('imposition') : [];
            $impMode = (string) ($impositionInput['mode'] ?? 'single');
            if (!in_array($impMode, ['single', 'step-repeat', 'vdp', 'cut-stack'], true)) {
                $impMode = 'single';
            }
            $impPaper = (string) ($impositionInput['paperSize'] ?? ($impMode === 'single' ? 'ORIGINAL' : 'A3+'));
            if ($impMode !== 'single' && $impPaper === 'ORIGINAL') {
                $impPaper = 'A3+';
            }
            $impOrientation = (string) ($impositionInput['orientation'] ?? 'auto');
            $impRows = $impMode === 'single' ? 1 : max(1, (int) ($impositionInput['rows'] ?? 2));
            $impCols = $impMode === 'single' ? 1 : max(1, (int) ($impositionInput['cols'] ?? 2));
            $impGapMm = $impMode === 'single' ? 0.0 : max(0.0, (float) ($impositionInput['gapMm'] ?? 0));
            $impCropMarks = $impPaper !== 'ORIGINAL'
                ? (array_key_exists('cropMarks', $impositionInput) ? (bool) $impositionInput['cropMarks'] : true)
                : false;
            $previewFirstSheetOnly = !empty($impositionInput['previewFirstSheetOnly']);

            $mmToPt = 72.0 / 25.4;
            $useSheetPaper = in_array($impPaper, ['A3', 'A3+'], true);

            if ($useSheetPaper) {
                // Physical sheet sizes in mm: A3 = 297x420, A3+ = 320x480
                [$shortMm, $longMm] = $impPaper === 'A3+' ? [320.0, 480.0] : [297.0, 420.0];
                $marginMm = $impCropMarks ? 6.0 : 4.0;
                $marginPt = $marginMm * $mmToPt;
                $gapPt = $impGapMm * $mmToPt;

                $calcScale = function (float $wPt, float $hPt) use ($marginPt, $gapPt, $impCols, $impRows, $canvasWidthPx, $canvasHeightPx): float {
                    $availW = max(10.0, $wPt - (2.0 * $marginPt) - (($impCols - 1) * $gapPt));
                    $availH = max(10.0, $hPt - (2.0 * $marginPt) - (($impRows - 1) * $gapPt));
                    return min($availW / ($impCols * $canvasWidthPx), $availH / ($impRows * $canvasHeightPx));
                };

                $landW = $longMm * $mmToPt;
                $landH = $shortMm * $mmToPt;
                $portW = $shortMm * $mmToPt;
                $portH = $longMm * $mmToPt;

                if ($impOrientation === 'L') {
                    $sheetWidthPt = $landW;
                    $sheetHeightPt = $landH;
                } elseif ($impOrientation === 'P') {
                    $sheetWidthPt = $portW;
                    $sheetHeightPt = $portH;
                } else {
                    $scaleLand = $calcScale($landW, $landH);
                    $scalePort = $calcScale($portW, $portH);
                    if ($scalePort > $scaleLand) {
                        $sheetWidthPt = $portW;
                        $sheetHeightPt = $portH;
                    } else {
                        $sheetWidthPt = $landW;
                        $sheetHeightPt = $landH;
                    }
                }

                $slotScale = $calcScale($sheetWidthPt, $sheetHeightPt);
                $bibWidthPt = $canvasWidthPx * $slotScale;
                $bibHeightPt = $canvasHeightPx * $slotScale;
                $gridTotalW = ($impCols * $bibWidthPt) + (($impCols - 1) * $gapPt);
                $gridTotalH = ($impRows * $bibHeightPt) + (($impRows - 1) * $gapPt);
                $gridStartX = ($sheetWidthPt - $gridTotalW) / 2.0;
                $gridStartY = ($sheetHeightPt - $gridTotalH) / 2.0;
            } else {
                $sheetWidthPt = (float) $canvasWidthPx;
                $sheetHeightPt = (float) $canvasHeightPx;
                $slotScale = 1.0;
                $gapPt = 0.0;
                $bibWidthPt = (float) $canvasWidthPx;
                $bibHeightPt = (float) $canvasHeightPx;
                $gridStartX = 0.0;
                $gridStartY = 0.0;
            }

            $pageOrientation = $sheetWidthPt >= $sheetHeightPt ? 'L' : 'P';

            // Enable diskcache (6th parameter = true) to prevent memory issues with large numbers of pages
            $pdf = new SafeTCPDF($pageOrientation, 'pt', [$sheetWidthPt, $sheetHeightPt], true, 'UTF-8', true);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('RuangLari');
            $pdf->SetTitle('BIBs - ' . $event->name);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetAutoPageBreak(false, 0);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetCellPadding(0);
            
            $takeLast = $request->filled('take_last') ? $request->take_last : null;
            $prefix = $request->filled('prefix') ? $request->prefix : '';
            $autoFormatNumber = $request->has('auto_format_number')
                ? $request->boolean('auto_format_number')
                : true;
            $maxNameLength = $request->input('max_name_length') !== null ? (int)$request->input('max_name_length') : 0;
            $autoFormatName = $request->has('auto_format_name')
                ? $request->boolean('auto_format_name')
                : ($maxNameLength > 0);
            $texts = $request->texts;
            $overrides = $request->input('overrides', []);
            $isCustomRange = $request->boolean('is_custom_range');

            $participantsList = $participants->values();
            $sheets = $this->buildImpositionSheets($impMode, $impRows, $impCols, $participantsList->count());
            if ($previewFirstSheetOnly && count($sheets) > 1) {
                $sheets = [$sheets[0]];
            }

            foreach ($sheets as $sheetSlots) {
                $pdf->AddPage($pageOrientation, [$sheetWidthPt, $sheetHeightPt]);

                if ($impCropMarks && $useSheetPaper) {
                    $this->drawSheetCropMarks(
                        $pdf,
                        $gridStartX,
                        $gridStartY,
                        $bibWidthPt,
                        $bibHeightPt,
                        $impRows,
                        $impCols,
                        $gapPt,
                        $mmToPt
                    );
                }

                foreach ($sheetSlots as $slotIdx => $participantIdx) {
                    if ($participantIdx === null || !isset($participantsList[$participantIdx])) {
                        continue;
                    }

                    $p = $participantsList[$participantIdx];
                    $rowIdx = intdiv($slotIdx, $impCols);
                    $colIdx = $slotIdx % $impCols;
                    $offsetX = $gridStartX + ($colIdx * ($bibWidthPt + $gapPt));
                    $offsetY = $gridStartY + ($rowIdx * ($bibHeightPt + $gapPt));

                    $pdf->Image($tempImgPath, $offsetX, $offsetY, $bibWidthPt, $bibHeightPt, '', '', '', false, 300, '', false, false, 0);

                    // Get override for this participant if any
                    $pOverride = isset($overrides[$p->id]) ? $overrides[$p->id] : [];

                    $hasOverrideBib = array_key_exists('bib_number', $pOverride);
                    $bibNum = $hasOverrideBib ? trim((string) ($pOverride['bib_number'] ?? '')) : trim((string) ($p->bib_number ?? ''));
                    $dynamicBib = $bibNum;
                    $takeLastTrimmed = $takeLast !== null ? trim((string) $takeLast) : '';

                    if ($dynamicBib !== '' && $isCustomRange && $autoFormatNumber) {
                        if ($takeLastTrimmed !== '' && is_numeric($takeLastTrimmed)) {
                            $numChars = (int) $takeLastTrimmed;
                            if ($numChars > 0) {
                                $dynamicBib = substr($dynamicBib, -$numChars);
                            }
                        }
                        if ($prefix !== '') {
                            $dynamicBib = $prefix . $dynamicBib;
                        }
                    } elseif ($dynamicBib !== '' && !$hasOverrideBib && $autoFormatNumber) {
                        if ($takeLastTrimmed !== '' && !is_numeric($takeLastTrimmed)) {
                            $dynamicBib = str_replace($takeLastTrimmed, '', $dynamicBib);
                        } elseif (strpos($dynamicBib, '-') !== false) {
                            $parts = explode('-', $dynamicBib);
                            $dynamicBib = end($parts);
                        }

                        if ($p->category && $p->category->quota > 0) {
                            $padLength = strlen((string) $p->category->quota);
                            if (is_numeric($dynamicBib)) {
                                $dynamicBib = str_pad($dynamicBib, $padLength, '0', STR_PAD_LEFT);
                            }
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
                    }

                    $dynamicBib = trim((string) ($dynamicBib ?? '')) === '' ? '-' : trim((string) $dynamicBib);
                    $originalBibNum = trim((string) ($p->bib_number ?? ''));
                    $fullBibNumber = $isCustomRange
                        ? (string) $dynamicBib
                        : ($originalBibNum !== '' ? (string) $originalBibNum : ($bibNum !== '' ? (string) $bibNum : (string) $dynamicBib));

                    $gender = isset($pOverride['gender']) ? $pOverride['gender'] : $p->gender;
                    $genderVal = '';
                    if (!empty($gender)) {
                        $gLower = strtolower(trim($gender));
                        if (in_array($gLower, ['male', 'm', 'laki-laki', 'l', 'men', 'man', 'laki - laki'])) {
                            $genderVal = 'M';
                        } elseif (in_array($gLower, ['female', 'f', 'perempuan', 'p', 'women', 'woman'])) {
                            $genderVal = 'F';
                        } else {
                            $genderVal = strtoupper(substr($gender, 0, 1));
                        }
                    }
                    $genderVal = trim($genderVal ?? '') === '' ? '-' : $genderVal;

                    $hasOverrideName = array_key_exists('name', $pOverride);
                    $rawName = $hasOverrideName ? (string) ($pOverride['name'] ?? '') : (string) $p->display_bib_name;
                    $pName = trim($rawName) === '' ? '-' : trim($rawName);
                    if ($pName !== '-' && !$hasOverrideName && $autoFormatName && $maxNameLength > 0) {
                        $pName = $this->formatBibName($pName, $maxNameLength);
                    }

                    $pCategory = isset($pOverride['category']) ? $pOverride['category'] : ($p->category->name ?? '');
                    $pCategory = trim($pCategory ?? '') === '' ? '-' : $pCategory;

                    $pBloodType = isset($pOverride['blood_type']) ? $pOverride['blood_type'] : $p->blood_type;
                    $pBloodType = trim($pBloodType ?? '') === '' ? '-' : $pBloodType;

                    $pEmergencyNumber = isset($pOverride['emergency_contact_number']) ? $pOverride['emergency_contact_number'] : $p->emergency_contact_number;
                    $pEmergencyNumber = trim($pEmergencyNumber ?? '') === '' ? '-' : $pEmergencyNumber;

                    $pEmergencyName = isset($pOverride['emergency_contact_name']) ? $pOverride['emergency_contact_name'] : $p->emergency_contact_name;
                    $pEmergencyName = trim($pEmergencyName ?? '') === '' ? '-' : $pEmergencyName;

                    $pGroup = isset($pOverride['age_group']) ? $pOverride['age_group'] : $p->getAgeGroup($event->start_at ?: now());
                    $pGroup = trim($pGroup ?? '') === '' ? '-' : $pGroup;

                    $couponCodeDefault = ($p->transaction && $p->transaction->coupon) ? $p->transaction->coupon->code : '';
                    $pCouponCode = isset($pOverride['coupon_code']) ? $pOverride['coupon_code'] : $couponCodeDefault;
                    $pCouponCode = trim($pCouponCode ?? '') === '' ? '-' : $pCouponCode;

                    $pEmergencyCombined = 'Emergency contact: ' . $pEmergencyName . ' - ' . $pEmergencyNumber;

                    foreach ($texts as $t) {
                        if ((isset($t['type']) && $t['type'] === 'qr') || trim($t['text'] ?? '') === '[qr]') {
                            $rawQrSize = isset($t['size']) ? (float) $t['size'] : (float) ($t['fontSize'] ?? 90);
                            $qrSize = $rawQrSize * $slotScale;
                            $qrX = $offsetX + ((float) ($t['left'] ?? 0) * $slotScale);
                            $qrY = $offsetY + ((float) ($t['top'] ?? 0) * $slotScale);
                            if (isset($t['originX']) && $t['originX'] === 'center') {
                                $qrX -= ($qrSize / 2.0);
                            }
                            if (isset($t['originY']) && $t['originY'] === 'center') {
                                $qrY -= ($qrSize / 2.0);
                            }
                            $qrStyle = [
                                'border' => 0,
                                'vpadding' => 2,
                                'hpadding' => 2,
                                'fgcolor' => [0, 0, 0],
                                'bgcolor' => [255, 255, 255],
                                'module_width' => 1,
                                'module_height' => 1,
                            ];
                            $pdf->write2DBarcode((string) $fullBibNumber, 'QRCODE,M', $qrX, $qrY, $qrSize, $qrSize, $qrStyle, 'N');
                            continue;
                        }

                        $textValue = str_replace(
                            [
                                '{emergency_contact}', '[EC]',
                                '{emergency_contact_number}', '[E#]',
                                '{emergency_contact_name}', '[EN]',
                                '{bib_number}', '#',
                                '{bib_name}',
                                '{name}', '[N]',
                                '{category}', '[C]',
                                '{blood_type}', '[B]',
                                '{gender}', '[G]',
                                '{age_group}', '[GR]',
                                '{coupon_code}', '[KP]'
                            ],
                            [
                                $pEmergencyCombined, $pEmergencyCombined,
                                $pEmergencyNumber, $pEmergencyNumber,
                                $pEmergencyName, $pEmergencyName,
                                $dynamicBib, $dynamicBib,
                                $pName,
                                $pName, $pName,
                                $pCategory, $pCategory,
                                $pBloodType, $pBloodType,
                                $genderVal, $genderVal,
                                $pGroup, $pGroup,
                                $pCouponCode, $pCouponCode
                            ],
                            $t['text']
                        );

                        $hex = ltrim($t['fill'], '#');
                        $r = hexdec(substr($hex, 0, 2));
                        $g = hexdec(substr($hex, 2, 2));
                        $b = hexdec(substr($hex, 4, 2));
                        $pdf->SetTextColor($r, $g, $b);

                        $fontStyle = '';
                        if (isset($t['fontWeight']) && $t['fontWeight'] === 'bold') {
                            $fontStyle .= 'B';
                        }
                        if (isset($t['fontStyle']) && $t['fontStyle'] === 'italic') {
                            $fontStyle .= 'I';
                        }
                        $fontSizePt = (float) $t['fontSize'] * $slotScale;
                        $fontFamily = strtolower($t['fontFamily'] ?? 'helvetica');

                        if (in_array($fontFamily, ['times new roman', 'times', 'serif'])) {
                            $fontFamily = 'times';
                        } elseif ($fontFamily === 'montserrat extrabold') {
                            $fontFamily = 'montserratextrab';
                            $fontStyle = '';
                        } elseif (in_array($fontFamily, ['bebas neue', 'bebasneue'])) {
                            $fontFamily = 'bebasneue';
                            $fontStyle = '';
                        } elseif ($fontFamily === 'anton') {
                            $fontFamily = 'anton';
                            $fontStyle = '';
                        } elseif ($fontFamily === 'montserrat') {
                            $fontFamily = 'montserrat';
                        } elseif (in_array($fontFamily, ['courier', 'courier new', 'monospace'])) {
                            $fontFamily = 'courier';
                        } elseif (in_array($fontFamily, ['helvetica', 'arial', 'sans-serif'])) {
                            $fontFamily = 'helvetica';
                        } else {
                            $fontFamily = 'helvetica';
                        }

                        $pdf->SetFont($fontFamily, $fontStyle, $fontSizePt);

                        $textWidth = $pdf->GetStringWidth($textValue);

                        $x = $offsetX + ((float) $t['left'] * $slotScale);
                        $yOrigin = $offsetY + ((float) $t['top'] * $slotScale);

                        $originX = $t['originX'] ?? 'left';
                        if ($originX === 'center') {
                            $x -= ($textWidth / 2.0);
                        } elseif ($originX === 'right') {
                            $x -= $textWidth;
                        }

                        // Match Fabric.js v5 text box metrics (_fontSizeMult = 1.13, _fontSizeFraction = 0.222)
                        // and place TCPDF on the exact baseline (calign='L', valign='T') instead of cell top+M.
                        $fabricBoxMult = 1.13;
                        $fabricFraction = 0.222;
                        $originY = $t['originY'] ?? 'top';
                        if ($originY === 'center') {
                            $baselineY = $yOrigin + ((0.5 - $fabricFraction) * $fabricBoxMult * $fontSizePt);
                        } elseif ($originY === 'bottom') {
                            $baselineY = $yOrigin - ($fabricFraction * $fabricBoxMult * $fontSizePt);
                        } else {
                            $baselineY = $yOrigin + ((1.0 - $fabricFraction) * $fabricBoxMult * $fontSizePt);
                        }

                        $pdf->Text($x, $baselineY, $textValue, 0, false, true, 0, 0, '', false, '', 0, false, 'L', 'T');
                    }
                }
            }
            
            @unlink($tempImgPath);
            
            $modeSuffix = $impMode !== 'single' ? ('_' . strtoupper($impMode) . '_' . $impPaper . '_' . $impRows . 'x' . $impCols) : '';
            if ($previewFirstSheetOnly) {
                $modeSuffix .= '_Sheet1';
            }
            if ($isCustomRange) {
                $pdfFilename = 'BIB_Custom_' . $from . '_to_' . $to . $modeSuffix . '_' . $event->slug . '_' . time() . '.pdf';
            } elseif ($request->filled('single_participant_id') && $participants->count() === 1) {
                $singleParticipant = $participants->first();
                $cleanName = Str::slug($singleParticipant->name);
                $pdfFilename = 'BIB_' . $cleanName . $modeSuffix . '_' . $event->slug . '_' . time() . '.pdf';
            } else {
                $pdfFilename = 'BIB_Export' . $modeSuffix . '_' . $event->slug . '_' . time() . '.pdf';
            }
            
            // Ensure storage path exists
            $bibsDir = public_path('storage/bibs');
            if (!file_exists($bibsDir)) {
                @mkdir($bibsDir, 0755, true);
            }
            // If public/storage is a symlink, write to storage/app/public/bibs directly
            $storageDir = storage_path('app/public/bibs');
            if (!file_exists($storageDir)) {
                @mkdir($storageDir, 0755, true);
            }
            
            $pdfPath = $storageDir . '/' . $pdfFilename;
            $pdf->Output($pdfPath, 'F');
            
            return response()->json([
                'success' => true, 
                'download_url' => asset('storage/bibs/' . $pdfFilename),
                'filename' => $pdfFilename
            ]);
        } catch (\Throwable $e) {
            @unlink($tempImgPath);
            \Illuminate\Support\Facades\Log::error('TCPDF BIB Generation Error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghasilkan PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Map participant indices (0-based) into sheet slot arrays for print imposition.
     * Supports: single, step-repeat, vdp, cut-stack.
     */
    public function buildImpositionSheets(string $mode, int $rows, int $cols, int $totalBib): array
    {
        if ($totalBib <= 0) {
            return [];
        }

        $r = $mode === 'single' ? 1 : max(1, $rows);
        $c = $mode === 'single' ? 1 : max(1, $cols);
        $slotsPerSheet = $r * $c;
        $sheets = [];

        if ($mode === 'single' || $mode === 'step-repeat') {
            for ($n = 1; $n <= $totalBib; $n++) {
                $sheets[] = array_fill(0, $slotsPerSheet, $n - 1);
            }

            return $sheets;
        }

        $totalSheets = (int) ceil($totalBib / $slotsPerSheet);

        for ($n = 1; $n <= $totalSheets; $n++) {
            $slots = [];
            for ($k = 0; $k < $slotsPerSheet; $k++) {
                $pos = ($mode === 'cut-stack')
                    ? ($n + ($k * $totalSheets))
                    : (($n - 1) * $slotsPerSheet + $k + 1);

                $slots[] = ($pos <= $totalBib) ? ($pos - 1) : null;
            }
            $sheets[] = $slots;
        }

        return $sheets;
    }

    /**
     * Draw registration hairlines (crop marks) outside the BIB grid for guillotine cutting.
     */
    private function drawSheetCropMarks(
        \TCPDF $pdf,
        float $startX,
        float $startY,
        float $bibW,
        float $bibH,
        int $rows,
        int $cols,
        float $gapPt,
        float $mmToPt
    ): void {
        $gridW = ($cols * $bibW) + (($cols - 1) * $gapPt);
        $gridH = ($rows * $bibH) + (($rows - 1) * $gapPt);
        $offsetPt = 1.5 * $mmToPt;
        $lenPt = 3.5 * $mmToPt;

        $xCuts = [];
        for ($c = 0; $c < $cols; $c++) {
            $left = $startX + ($c * ($bibW + $gapPt));
            $right = $left + $bibW;
            $xCuts[] = round($left, 2);
            $xCuts[] = round($right, 2);
        }
        $xCuts = array_values(array_unique($xCuts));

        $yCuts = [];
        for ($r = 0; $r < $rows; $r++) {
            $top = $startY + ($r * ($bibH + $gapPt));
            $bottom = $top + $bibH;
            $yCuts[] = round($top, 2);
            $yCuts[] = round($bottom, 2);
        }
        $yCuts = array_values(array_unique($yCuts));

        $style = [
            'width' => 0.3,
            'cap' => 'butt',
            'join' => 'miter',
            'dash' => 0,
            'color' => [0, 0, 0],
        ];

        foreach ($xCuts as $x) {
            $pdf->Line($x, max(0.0, $startY - $offsetPt - $lenPt), $x, max(0.0, $startY - $offsetPt), $style);
            $pdf->Line($x, $startY + $gridH + $offsetPt, $x, $startY + $gridH + $offsetPt + $lenPt, $style);
        }

        foreach ($yCuts as $y) {
            $pdf->Line(max(0.0, $startX - $offsetPt - $lenPt), $y, max(0.0, $startX - $offsetPt), $y, $style);
            $pdf->Line($startX + $gridW + $offsetPt, $y, $startX + $gridW + $offsetPt + $lenPt, $y, $style);
        }
    }

    public function doorprizeList(Event $event)
    {
        $this->authorizeEvent($event);

        $participants = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id)
              ->where('payment_status', 'paid');
        })
        ->select('id', 'bib_number', 'name', 'phone', 'address', 'city', 'province')
        ->orderBy('bib_number')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $participants
        ]);
    }

    /**
     * Return all Paid & Approved participants eligible for BIB printing (supports category filter).
     */
    public function bibParticipantsList(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $query = \App\Models\Participant::where('participants.isApproved', 1)
            ->whereNotIn('participants.status', ['cancelled', 'rejected'])
            ->whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id)
                    ->whereIn('payment_status', ['paid', 'settlement', 'capture']);
            })
            ->with(['transaction.coupon', 'category']);

        if ($request->filled('category_id')) {
            $query->where('participants.race_category_id', $request->query('category_id'));
        }

        $items = $query->orderBy('bib_number')->orderBy('id')->get()->map(function ($p) use ($event) {
            return [
                'id' => $p->id,
                'name' => $p->display_bib_name,
                'full_name' => $p->name,
                'bib_name' => $p->bib_name,
                'bib_number' => $p->bib_number,
                'gender' => $p->gender,
                'blood_type' => $p->blood_type,
                'emergency_contact_name' => $p->emergency_contact_name,
                'emergency_contact_number' => $p->emergency_contact_number,
                'race_category_id' => $p->race_category_id,
                'category' => $p->category ? $p->category->name : '-',
                'category_quota' => $p->category ? (int) $p->category->quota : 0,
                'age_group' => $p->getAgeGroup($event->start_at ?: now()),
                'coupon_code' => $p->transaction?->coupon?->code ?? null,
                'payment_status' => 'paid',
                'isApproved' => true,
                'status' => $p->status,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'total' => $items->count(),
            'data' => $items,
        ]);
    }

    /**
     * Assign next unique BIB number using global per-event increment.
     */
    private function assignNextEventBibNumber(Event $event, \App\Models\Participant $participant): void
    {
        if (! empty($participant->bib_number)) {
            return;
        }

        $bibNumbers = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })
            ->whereNotNull('bib_number')
            ->pluck('bib_number');

        $maxNumber = null;
        $prefix = '';
        $suffixLength = 4;

        foreach ($bibNumbers as $bib) {
            if (! preg_match('/(\d+)\s*$/', (string) $bib, $m)) {
                continue;
            }

            $number = (int) $m[1];
            if ($maxNumber === null || $number > $maxNumber) {
                $maxNumber = $number;
                $suffixLength = strlen($m[1]);
                $prefix = substr((string) $bib, 0, -$suffixLength);
            }
        }

        if ($maxNumber !== null) {
            $nextNumber = $maxNumber + 1;
        } else {
            $eventCode = strtoupper(\Illuminate\Support\Str::limit(\Illuminate\Support\Str::slug($event->name, ''), 10, ''));
            $year = $event->start_at ? $event->start_at->format('Y') : date('Y');
            $prefix = $eventCode.'-'.$year.'-';
            $nextNumber = 1;
        }

        $candidate = $prefix.str_pad((string) $nextNumber, $suffixLength, '0', STR_PAD_LEFT);
        while (\App\Models\Participant::where('bib_number', $candidate)->exists()) {
            $nextNumber++;
            $candidate = $prefix.str_pad((string) $nextNumber, $suffixLength, '0', STR_PAD_LEFT);
        }

        $participant->bib_number = $candidate;
        $participant->save();
    }

    private function formatBibName($name, $maxLength = 18)
    {
        $name = trim($name);
        if (empty($name)) {
            return '';
        }

        // Lowercase first to normalize ALL CAPS
        $name = mb_strtolower($name, 'UTF-8');
        $name = mb_convert_case($name, MB_CASE_TITLE, "UTF-8");

        // Limit to max 2 words in full, abbreviate the rest
        $words = preg_split('/\s+/', $name);
        if (count($words) > 2) {
            $formattedWords = [];
            $formattedWords[] = $words[0];
            $formattedWords[] = $words[1];
            for ($i = 2; $i < count($words); $i++) {
                $firstLetter = mb_substr($words[$i], 0, 1, 'UTF-8');
                if (!empty($firstLetter)) {
                    $formattedWords[] = $firstLetter . '.';
                }
            }
            $name = implode(' ', $formattedWords);
        }

        // Apply maximum character length truncation with smart initial fallback
        if (mb_strlen($name, 'UTF-8') <= $maxLength) {
            return $name;
        }

        // Truncate to maxLength
        $truncated = mb_substr($name, 0, $maxLength, 'UTF-8');

        // Check if the character right after the truncation point is a space, or if we ended exactly on a word boundary
        $nextChar = mb_substr($name, $maxLength, 1, 'UTF-8');
        if ($nextChar === '' || $nextChar === ' ' || mb_substr($truncated, -1) === ' ') {
            return trim($truncated);
        }

        // Find the last word in the truncated string
        $parts = preg_split('/\s+/', $truncated);
        if (count($parts) > 0) {
            $lastWordIndex = count($parts) - 1;
            $lastWord = $parts[$lastWordIndex];

            // If the last word is already an initial (e.g. "E."), leave it
            if (preg_match('/^[A-Z]\.$/', $lastWord)) {
                return implode(' ', $parts);
            }

            // Otherwise, it is a cut-off word. Abbreviate it to its first character + "."
            $originalWords = preg_split('/\s+/', $name);
            if (isset($originalWords[$lastWordIndex])) {
                $firstLetter = mb_substr($originalWords[$lastWordIndex], 0, 1, 'UTF-8');
                $parts[$lastWordIndex] = $firstLetter . '.';
            } else {
                $firstLetter = mb_substr($lastWord, 0, 1, 'UTF-8');
                $parts[$lastWordIndex] = $firstLetter . '.';
            }

            return implode(' ', $parts);
        }

        return $truncated;
    }

    // Google Sheets export removed as per request

    /**
     * Delete a participant (only if not paid)
     */
    public function destroyParticipant(Request $request, Event $event, \App\Models\Participant $participant)
    {
        $this->authorizeEvent($event);

        // Check if participant belongs to event via Transaction OR Category
        $belongsToEvent = false;

        if ($participant->transaction && $participant->transaction->event_id == $event->id) {
            $belongsToEvent = true;
        } elseif ($participant->category && $participant->category->event_id == $event->id) {
            $belongsToEvent = true;
        } elseif ($participant->package && $participant->package->event_id == $event->id) {
            $belongsToEvent = true;
        }

        if (! $belongsToEvent) {
            abort(403, 'Unauthorized: Participant does not belong to this event');
        }

        $status = $participant->transaction->payment_status ?? 'pending';
        if ($status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Peserta dengan transaksi paid tidak dapat dihapus',
            ], 422);
        }
        $participant->delete();
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Peserta berhasil dihapus',
            ]);
        }

        return back()->with('success', 'Peserta berhasil dihapus');
    }

    public function clearParticipants(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $includePaid = (bool) $request->boolean('include_paid', false);

        $baseQuery = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        });

        if ($includePaid) {
            $validated = $request->validate([
                'confirm' => 'required|string',
            ]);

            if (($validated['confirm'] ?? '') !== 'DELETE_ALL') {
                return response()->json([
                    'success' => false,
                    'message' => 'Konfirmasi tidak valid. Ketik DELETE_ALL untuk menghapus peserta termasuk paid.',
                ], 422);
            }

            $deletedCount = (clone $baseQuery)->delete();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$deletedCount} peserta (termasuk paid).",
                'deleted' => $deletedCount,
                'skipped' => 0,
            ]);
        }

        $protectedStatuses = ['paid', 'settlement', 'capture'];

        $skippedCount = (clone $baseQuery)
            ->whereHas('transaction', function ($q) use ($protectedStatuses) {
                $q->whereIn('payment_status', $protectedStatuses);
            })
            ->count();

        $deletedCount = (clone $baseQuery)
            ->whereHas('transaction', function ($q) use ($protectedStatuses) {
                $q->where(function ($t) use ($protectedStatuses) {
                    $t->whereNull('payment_status')
                        ->orWhereNotIn('payment_status', $protectedStatuses);
                });
            })
            ->delete();

        $message = "Berhasil menghapus {$deletedCount} peserta.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} peserta dilewati karena transaksi sudah paid.";
        }

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted' => $deletedCount,
                'skipped' => $skippedCount,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Update participant picked up status
     */
    public function updateParticipantStatus(Request $request, Event $event, \App\Models\Participant $participant)
    {
        $this->authorizeEvent($event);

        // Verify participant belongs to this event
        $belongsToEvent = false;
        if ($participant->transaction && (int) $participant->transaction->event_id === (int) $event->id) {
            $belongsToEvent = true;
        } elseif ($participant->category && (int) $participant->category->event_id === (int) $event->id) {
            $belongsToEvent = true;
        }

        if (! $belongsToEvent) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'is_picked_up' => 'required|boolean',
            'picked_up_by' => 'nullable|string|max:255',
        ]);

        $wasPickedUp = (bool) $participant->is_picked_up;
        $isPickedUp = (bool) $validated['is_picked_up'];
        if ($isPickedUp) {
            $paymentStatus = (string) ($participant->transaction?->payment_status ?? '');
            if (! in_array($paymentStatus, ['paid', 'cod'], true)) {
                $message = 'Tidak bisa pickup: status pembayaran belum paid.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 422);
                }
                return back()->with('error', $message);
            }
        }

        $participant->update([
            'is_picked_up' => $isPickedUp,
            'picked_up_at' => $isPickedUp ? now() : null,
            'picked_up_by' => $isPickedUp ? (($validated['picked_up_by'] ?? null) ?: (auth()->user()->name ?? null)) : null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status pengambilan berhasil diperbarui',
                'pickup_changed' => $wasPickedUp !== $isPickedUp,
                'participant' => [
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'bib_number' => $participant->bib_number,
                    'jersey_size' => $participant->jersey_size,
                    'is_picked_up' => (bool) $participant->is_picked_up,
                    'picked_up_at' => $participant->picked_up_at ? $participant->picked_up_at->format('Y-m-d H:i:s') : null,
                    'picked_up_by' => $participant->picked_up_by,
                    'payment_status' => $participant->transaction?->payment_status ?? 'pending',
                ],
                'jersey_sizes_pending_pickup' => $this->getJerseyPendingPickupCounts($event),
            ]);
        }

        return back()->with('success', 'Status pengambilan berhasil diperbarui');

    }

    /**
     * Create a user account from a participant
     */
    public function createUserFromParticipant(Request $request, Event $event, \App\Models\Participant $participant)
    {
        $this->authorizeEvent($event);

        // Verify participant belongs to this event
        $participantEventId = (int) ($participant?->transaction?->event_id ?? 0);
        if ($participantEventId !== (int) $event->id) {
            abort(403, 'Unauthorized');
        }

        $transaction = $participant->transaction;
        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        // Check if transaction already has a user_id
        if ($transaction->user_id) {
            $existingUser = \App\Models\User::find($transaction->user_id);
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi ini sudah terhubung dengan user: ' . $existingUser->email,
                ], 422);
            }
        }

        // Check if a user with the participant's email already exists
        $email = trim($participant->email);
        if (empty($email)) {
            return response()->json([
                'success' => false,
                'message' => 'Email peserta kosong.',
            ], 422);
        }

        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            // Create new user
            // Default password based on first name/prefix
            $firstName = explode(' ', trim($participant->name))[0];
            $password = strtolower($firstName) . '123';

            $user = \App\Models\User::create([
                'name' => $participant->name,
                'email' => $email,
                'phone' => $participant->phone,
                'password' => \Illuminate\Support\Facades\Hash::make($password),
                'role' => 'runner',
                'is_active' => true,
            ]);
        }

        // Link transaction to user
        $transaction->update([
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dibuat/dihubungkan dengan email: ' . $user->email,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * Update participant picked up status in bulk
     */
    public function bulkUpdateStatus(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'required|exists:participants,id',
            'is_picked_up' => 'required|boolean',
            'picked_up_by' => 'nullable|string|max:255',
        ]);

        $ids = $validated['participant_ids'];
        $isPickedUp = (bool) $validated['is_picked_up'];
        $pickedUpBy = $validated['picked_up_by'] ?? null;
        $currentUser = auth()->user()->name ?? null;

        $participants = \App\Models\Participant::whereIn('id', $ids)
            ->whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            })
            ->with(['transaction'])
            ->get();

        if ($participants->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada peserta valid yang dipilih.',
            ], 422);
        }

        $successCount = 0;
        $failedCount = 0;

        foreach ($participants as $p) {
            if ($isPickedUp) {
                $paymentStatus = (string) ($p->transaction->payment_status ?? '');
                if (! in_array($paymentStatus, ['paid', 'cod'], true)) {
                    $failedCount++;
                    continue;
                }
            }

            $p->update([
                'is_picked_up' => $isPickedUp,
                'picked_up_at' => $isPickedUp ? now() : null,
                'picked_up_by' => $isPickedUp ? ($pickedUpBy ?: $currentUser) : null,
            ]);
            $successCount++;
        }

        $message = "Status pengambilan berhasil diperbarui untuk {$successCount} peserta.";
        if ($failedCount > 0) {
            $message .= " Gagal mengupdate {$failedCount} peserta karena status pembayaran belum paid.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
        ]);
    }

    /**
     * Update transaction payment status
     */
    public function updatePaymentStatus(Request $request, Event $event, $transaction_id)
    {
        $this->authorizeEvent($event);

        $transaction = \App\Models\Transaction::findOrFail($transaction_id);

        // Verify transaction belongs to this event
        if ($transaction->event_id != $event->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,expired,cod',
        ]);

        $transaction->update([
            'payment_status' => $validated['payment_status'],
            'paid_at' => $validated['payment_status'] === 'paid' ? now() : null,
        ]);

        if ($validated['payment_status'] === 'paid') {
            \App\Jobs\ProcessPaidEventTransaction::dispatchAfterResponse($transaction);
        } elseif (in_array($validated['payment_status'], ['failed', 'expired'])) {
            $transaction->participants()->update([
                'isApproved' => 0,
                'status' => 'cancelled',
            ]);
        }

        // Always return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Status pembayaran berhasil diperbarui',
            ]);
        }

        return back()->with('success', 'Status pembayaran berhasil diperbarui');
    }

    protected function authorizeEvent(Event $event)
    {
        $user = auth()->user();
        if (! $user) {
            abort(403, 'Kamu tidak punya akses untuk mengelola event ini.');
        }

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role === 'eo' && (int) $event->user_id === (int) $user->id) {
            return;
        }

        abort(403, 'Kamu tidak punya akses untuk mengelola event ini.');
    }

    private function getPublicEventDetailAnalytics(Event $event): array
    {
        if (! Schema::hasTable('eo_page_stats')) {
            return [
                'today' => ['views' => 0, 'unique' => 0],
                'last30' => ['views' => 0, 'unique' => 0],
            ];
        }

        $today = now()->toDateString();
        $page = 'public_event_detail';

        $todayRow = DB::table('eo_page_stats')
            ->where('event_id', $event->id)
            ->where('page', $page)
            ->where('stat_date', $today)
            ->first();

        $from = now()->subDays(29)->startOfDay()->toDateString();
        $last30 = DB::table('eo_page_stats')
            ->where('event_id', $event->id)
            ->where('page', $page)
            ->where('stat_date', '>=', $from)
            ->selectRaw('COALESCE(SUM(views),0) as views, COALESCE(SUM(unique_views),0) as unique_views')
            ->first();

        return [
            'today' => [
                'views' => (int) ($todayRow->views ?? 0),
                'unique' => (int) ($todayRow->unique_views ?? 0),
            ],
            'last30' => [
                'views' => (int) ($last30->views ?? 0),
                'unique' => (int) ($last30->unique_views ?? 0),
            ],
        ];
    }

    private function duplicateName(string $name): string
    {
        $suffix = ' (Copy)';
        $max = 255;
        if (mb_strlen($name.$suffix) <= $max) {
            return $name.$suffix;
        }

        return mb_substr($name, 0, $max - mb_strlen($suffix)).$suffix;
    }

    private function uniqueEventSlug(string $sourceSlug): string
    {
        $base = $sourceSlug.'-copy';
        $base = Str::limit($base, 240, '');

        $slug = $base;
        $i = 1;
        while (Event::query()->where('slug', $slug)->exists()) {
            $suffix = '-'.$i;
            $slug = Str::limit($base, 240 - mb_strlen($suffix), '').$suffix;
            $i++;
        }

        return $slug;
    }

    /**
     * Process and save uploaded image
     */
    private function processImage($file, $folder = 'events', $maxWidth = 1920, $quality = 85)
    {
        /** @var ImageUploadService $imageService */
        $imageService = app(ImageUploadService::class);
        return $imageService->uploadSingle($file, $folder, $maxWidth, $quality);
    }

    /**
     * Show blast email form
     */
    public function blast(Event $event)
    {
        $this->authorizeEvent($event);
        $event->load('categories');

        return view('eo.events.blast', compact('event'));
    }

    /**
     * Send blast email
     */
    public function sendBlast(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:race_categories,id',
        ]);

        $filters = [];
        if (! empty($validated['category_id'])) {
            $filters['category_id'] = $validated['category_id'];
        }

        // Dispatch job
        \App\Jobs\SendEventBlastEmail::dispatch($event, $validated['subject'], $validated['content'], $filters)
            ->onQueue('emails-blast');
        $msg = 'Email blast sedang diproses dalam antrian.';

        return redirect()->route('eo.events.blast', $event->id)
            ->with('success', $msg);
    }

    /**
     * Preview ticket email
     */
    public function previewEmail(Request $request, ?Event $event = null)
    {
        if (! $event || ! $event->exists) {
            $event = new Event([
                'name' => $request->input('name', 'Jakarta Marathon 2025'),
                'slug' => $request->input('slug', 'jakarta-marathon-2025'),
                'location_name' => 'Gelora Bung Karno (GBK), Jakarta',
                'start_at' => now()->addDays(30)->setTime(6, 0),
            ]);
        } else {
            $this->authorizeEvent($event);
        }

        if ($request->filled('slug')) {
            $event->slug = $request->input('slug');
        } elseif (empty($event->slug)) {
            $event->slug = 'jakarta-marathon-2025';
        }

        $event->custom_email_message = $this->normalizeCustomEmailMessage($request->input('custom_email_message'), $event);
        if ($request->has('ticket_email_use_qr')) {
            $event->ticket_email_use_qr = (bool) $request->boolean('ticket_email_use_qr');
        }
        if ($request->has('ticket_email_template')) {
            $event->ticket_email_template = $request->input('ticket_email_template');
        }
        if ($request->has('ticket_email_show_jersey')) {
            $event->ticket_email_show_jersey = (bool) $request->boolean('ticket_email_show_jersey');
        }
        if ($request->has('ticket_email_show_addons')) {
            $event->ticket_email_show_addons = (bool) $request->boolean('ticket_email_show_addons');
        }
        if ($request->has('ticket_email_show_pic')) {
            $event->ticket_email_show_pic = (bool) $request->boolean('ticket_email_show_pic');
        }
        if ($request->has('name')) {
            $event->name = $request->input('name');
        }
        if ($request->filled('logo_image')) {
            $event->logo_image = $request->input('logo_image');
        }
        if ($request->filled('location_name')) {
            $event->location_name = $request->input('location_name');
        }

        // Mock Participant
        $participant = new \App\Models\Participant([
            'id' => 12345,
            'user_id' => auth()->id(),
            'event_id' => $event->id,
            'name' => auth()->user()->name ?? 'John Doe',
            'email' => auth()->user()->email ?? 'john@example.com',
            'bib_number' => '1001',
            'gender' => 'male',
        ]);

        // Mock Category
        $category = new \App\Models\RaceCategory([
            'name' => '10K Open',
            'distance_km' => 10,
        ]);
        $participant->setRelation('category', $category);
        $participant->transaction_id = 99999;

        // Mock Transaction
        $transaction = new \App\Models\Transaction([
            'id' => 99999,
            'final_amount' => 150000,
            'payment_status' => 'paid',
            'pic_data' => [
                'name' => 'Budi Santoso (PIC)',
                'email' => 'pic@example.com',
                'phone' => '081234567890',
            ],
        ]);

        return view('emails.events.registration-success', [
            'event' => $event,
            'participants' => collect([$participant]),
            'transaction' => $transaction,
            'notifiableName' => $participant->name,
        ]);
    }

    public function sendTestEmail(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'test_email' => 'required|email|max:255',
            'custom_email_message' => 'nullable|string',
            'ticket_email_use_qr' => 'nullable|boolean',
            'name' => 'nullable|string|max:255',
        ]);

        $rateKey = 'eo:test-email:'.$event->id;
        $count = (int) $request->session()->get($rateKey, 0);
        if ($count >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Batas kirim test email tercapai untuk sesi ini (maksimal 3 kali).',
                'remaining' => 0,
            ], 429);
        }

        $request->session()->put($rateKey, $count + 1);
        $remaining = max(0, 3 - ($count + 1));

        $event->custom_email_message = $this->normalizeCustomEmailMessage($validated['custom_email_message'] ?? null, $event);
        if ($request->has('ticket_email_use_qr')) {
            $event->ticket_email_use_qr = (bool) $request->boolean('ticket_email_use_qr');
        }
        if ($request->has('ticket_email_template')) {
            $event->ticket_email_template = $request->input('ticket_email_template');
        }
        if ($request->has('ticket_email_show_jersey')) {
            $event->ticket_email_show_jersey = (bool) $request->boolean('ticket_email_show_jersey');
        }
        if ($request->has('ticket_email_show_addons')) {
            $event->ticket_email_show_addons = (bool) $request->boolean('ticket_email_show_addons');
        }
        if ($request->has('ticket_email_show_pic')) {
            $event->ticket_email_show_pic = (bool) $request->boolean('ticket_email_show_pic');
        }
        if (! empty($validated['name'])) {
            $event->name = $validated['name'];
        }

        $authUser = $request->user();
        $participant = new \App\Models\Participant([
            'id' => 12345,
            'user_id' => $authUser?->id,
            'event_id' => $event->id,
            'name' => $authUser?->name ?? 'John Doe',
            'email' => $authUser?->email ?? 'john@example.com',
            'phone' => $authUser?->phone ?? '08123456789',
            'bib_number' => '1001',
            'gender' => 'male',
        ]);

        $category = new \App\Models\RaceCategory([
            'name' => '10K Open',
            'distance_km' => 10,
        ]);
        $participant->setRelation('category', $category);
        $participant->transaction_id = 99999;

        $transaction = new \App\Models\Transaction([
            'id' => 99999,
            'final_amount' => 150000,
            'payment_status' => 'paid',
            'pic_data' => [
                'name' => $authUser?->name ?? 'John Doe',
                'email' => $authUser?->email ?? 'john@example.com',
                'phone' => $authUser?->phone ?? '08123456789',
            ],
        ]);

        try {
            Mail::to($validated['test_email'])->send(
                new EventRegistrationSuccess($event, $transaction, collect([$participant]), $participant->name)
            );
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email. '.$e->getMessage(),
                'remaining' => $remaining,
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Test email berhasil dikirim.',
            'remaining' => $remaining,
        ]);
    }

    /**
     * Delete image file
     */
    private function deleteImage($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function normalizeCustomEmailMessage(?string $html, Event $event): ?string
    {
        if (! is_string($html)) {
            return null;
        }

        $html = trim($html);
        if ($html === '') {
            return null;
        }

        if (! str_contains($html, 'data:image/')) {
            return $html;
        }

        $prev = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $images = $dom->getElementsByTagName('img');
        $imageNodes = [];
        foreach ($images as $img) {
            $imageNodes[] = $img;
        }

        foreach ($imageNodes as $img) {
            $src = (string) $img->getAttribute('src');
            if (! str_starts_with($src, 'data:image/')) {
                continue;
            }

            if (! preg_match('/^data:image\/([a-zA-Z0-9+]+);base64,(.+)$/', $src, $m)) {
                continue;
            }

            $ext = strtolower($m[1]);
            if ($ext === 'jpeg') {
                $ext = 'jpg';
            }
            if (! in_array($ext, ['jpg', 'png', 'gif', 'webp'], true)) {
                $ext = 'png';
            }

            $binary = base64_decode($m[2], true);
            if ($binary === false) {
                continue;
            }

            $path = 'email-custom/'.$event->id.'/'.Str::random(40).'.'.$ext;
            Storage::disk('public')->put($path, $binary);

            $img->setAttribute('src', asset('storage/'.$path));

            $style = trim((string) $img->getAttribute('style'));
            $extraStyle = 'max-width:100%;height:auto;';
            $img->setAttribute('style', ($style !== '' ? rtrim($style, ';').';' : '').$extraStyle);
        }

        return $dom->saveHTML();
    }

    public function remindPending(Request $request, Event $event, $transaction)
    {
        $this->authorizeEvent($event);

        $tx = Transaction::where('id', $transaction)
            ->where('event_id', $event->id)
            ->first();

        if (! $tx) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan untuk event ini.',
            ], 404);
        }

        if ($tx->payment_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak dalam status pending.',
            ], 422);
        }

        SendPendingPaymentReminder::dispatch($tx);

        return response()->json([
            'success' => true,
            'message' => 'Reminder berhasil dijadwalkan.',
        ]);
    }

    public function bulkDelete(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $request->validate([
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'exists:participants,id'
        ]);

        // Ensure participants belong to this event
        $count = \App\Models\Participant::whereIn('id', $request->participant_ids)
            ->whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            })
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Berhasil menghapus {$count} peserta."
        ]);
    }

    public function remindPendingBulk(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $query = Transaction::where('event_id', $event->id)
            ->where('payment_status', 'pending');

        if ($request->has('participant_ids') && is_array($request->participant_ids)) {
             $query->whereHas('participants', function($q) use ($request) {
                $q->whereIn('id', $request->participant_ids);
            });
        } else {
            $query->where('created_at', '<', now()->subDay());
        }

        $transactions = $query->where(function ($q) {
                $q->whereNull('pending_reminder_last_sent_at')
                  ->orWhere('pending_reminder_last_sent_at', '<', now()->subHours(24));
            })
            ->get();

        $count = 0;
        foreach ($transactions as $transaction) {
            SendPendingPaymentReminder::dispatch($transaction);
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menjadwalkan {$count} reminder pembayaran."
        ]);
    }

    public function sendCustomWaReminderBulk(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $request->validate([
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'integer|exists:participants,id',
            'message' => 'required|string|max:1000',
        ]);

        $participants = \App\Models\Participant::whereIn('id', $request->participant_ids)
            ->whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            })
            ->get();

        $count = 0;
        foreach ($participants as $participant) {
            \App\Jobs\SendCustomWhatsAppJob::dispatch($participant->id, $request->message);
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil mengirim {$count} pesan reminder WhatsApp ke antrean."
        ]);
    }
}

class SafeTCPDF extends \TCPDF
{
    public function Error($msg)
    {
        throw new \Exception('TCPDF ERROR: ' . $msg);
    }
}
