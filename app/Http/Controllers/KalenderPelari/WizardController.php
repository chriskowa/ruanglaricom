<?php

namespace App\Http\Controllers\KalenderPelari;

use App\Http\Controllers\Controller;
use App\Models\KalenderPelari\CalendarProject;
use App\Services\KalenderPelari\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class WizardController extends Controller
{
    private const CALENDAR_TYPES = [
        'MY_RUNNING_YEAR' => [
            'label' => 'Tahunan Lari Saya',
            'tagline' => 'Tanggal, ringkasan lari, dan ruang target bulanan.',
            'accent' => 'text-neon',
            'icon' => 'fa-chart-line',
        ],
        'TRAINING_PLANNER' => [
            'label' => 'Pelatihan Target Race',
            'tagline' => 'Tanggal, target bulanan, dan catatan fokus latihan.',
            'accent' => 'text-orange-400',
            'icon' => 'fa-route',
        ],
        'RACE_SEASON' => [
            'label' => 'Musim Balapan',
            'tagline' => 'Tanggal dan ruang mencatat race setiap bulan.',
            'accent' => 'text-sky-400',
            'icon' => 'fa-flag-checkered',
        ],
        'PHOTO_MEMORIES' => [
            'label' => 'Foto & Memori',
            'tagline' => 'Ruang foto dan cerita di setiap halaman bulanan.',
            'accent' => 'text-pink-400',
            'icon' => 'fa-images',
        ],
        'BLANK_FROM_SCRATCH' => [
            'label' => 'Kosong (Full Kustom)',
            'tagline' => 'Halaman kosong untuk menyusun elemen sendiri.',
            'accent' => 'text-slate-300',
            'icon' => 'fa-pen-ruler',
        ],
    ];

    private const FORMATS = [
        'A4_LANDSCAPE' => ['label' => 'A4 Landscape', 'w_mm' => 297, 'h_mm' => 210, 'desc' => '297 × 210 mm · Dinding'],
        'A3_LANDSCAPE' => ['label' => 'A3 Landscape', 'w_mm' => 420, 'h_mm' => 297, 'desc' => '420 × 297 mm · Poster Besar'],
        'DESK_CALENDAR' => ['label' => 'Desk Calendar', 'w_mm' => 210, 'h_mm' => 148, 'desc' => '210 × 148 mm · Meja · Standing'],
        'SQUARE' => ['label' => 'Square Premium', 'w_mm' => 250, 'h_mm' => 250, 'desc' => '250 × 250 mm · Premium · Hardcover'],
    ];

    private const TEMPLATES_MINIMAL = [
        'MINIMAL_RUNNER' => [
            'name' => 'Minimal Runner',
            'tagline' => 'Bersih, fokus data, tipografi tegas',
            'family' => 'MINIMAL_RUNNER',
            'price_idr' => 0,
        ],
        'RACE_SEASON' => [
            'name' => 'Race Season',
            'tagline' => 'Halaman gelap dengan aksen pada grid tanggal.',
            'family' => 'RACE_SEASON',
            'price_idr' => 0,
        ],
        'CLEAN_GRID' => [
            'name' => 'Clean Grid',
            'tagline' => 'Grid lebar dengan ruang catatan di bawah.',
            'family' => 'CLEAN_GRID',
            'price_idr' => 0,
        ],
    ];

    public function __construct(
        private readonly ProjectService $projects,
    ) {}

    public function index(Request $request)
    {
        $type = $request->old('type', $request->query('type'));
        $year = $request->old('year', $request->query('year', (int) now()->format('Y') + 1));
        $format = $request->old('format', $request->query('format', 'A4_LANDSCAPE'));
        $template = $request->old('template', $request->query('template', 'MINIMAL_RUNNER'));

        $years = [];
        $current = (int) now()->format('Y');
        for ($y = $current; $y <= $current + 10; $y++) {
            $years[$y] = "Tahun {$y}";
        }

        return view('kalender-pelari.wizard', [
            'step' => (int) $request->query('step', 1),
            'calendarTypes' => self::CALENDAR_TYPES,
            'formats' => self::FORMATS,
            'templates' => self::TEMPLATES_MINIMAL,
            'years' => $years,
            'selected' => [
                'type' => $type,
                'year' => $year,
                'format' => $format,
                'template' => $template,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $valid = $request->validate([
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(self::CALENDAR_TYPES))],
            'year' => ['required', 'integer', 'min:2024', 'max:2035'],
            'format_type' => ['required', 'string', 'in:'.implode(',', array_keys(self::FORMATS))],
            'template_family' => ['nullable', 'string', 'in:'.implode(',', array_keys(self::TEMPLATES_MINIMAL))],
            'start_week_on' => ['nullable', 'string', 'in:MONDAY,SUNDAY'],
            'project_name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = $request->user();
        if ($user) {
            $freeLimit = (int) config('kalender-pelari.free_project_limit', 3);
            $currentCount = CalendarProject::where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->count();
            if ($currentCount >= $freeLimit) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'error' => "Maksimal {$freeLimit} proyek free per user. Hapus proyek lama atau upgrade tier.",
                    ]);
            }
        }

        $typeKey = $valid['type'];
        $name = $valid['project_name']
            ?? sprintf(
                '%s %s',
                Arr::get(self::CALENDAR_TYPES, "{$typeKey}.label", 'Kalender Lariku'),
                $valid['year']
            );

        $project = $this->projects->createTrial([
            'name' => $name,
            'year' => (int) $valid['year'],
            'format_type' => $valid['format_type'],
            'start_week_on' => $valid['start_week_on'] ?? 'MONDAY',
            'calendar_type' => $typeKey,
            'template_family' => $valid['template_family'] ?? 'MINIMAL_RUNNER',
        ], $user);

        return redirect()->to($project->routeEditor(), 302);
    }
}
