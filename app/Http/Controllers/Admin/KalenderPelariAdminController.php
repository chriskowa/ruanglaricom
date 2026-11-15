<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KalenderPelari\AthleteTemplate;
use App\Models\KalenderPelari\CalendarProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KalenderPelariAdminController extends Controller
{
    /* ==========================================================
     * 1. PROYEK KALENDER PENGGUNA (PRINT & ORDER MANAGEMENT)
     * ========================================================== */

    public function projectsIndex(Request $request): View
    {
        $query = CalendarProject::with(['user', 'pages'])->latest();

        // Search filter
        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('anonymous_uuid', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('name', 'like', "%{$q}%")
                           ->orWhere('email', 'like', "%{$q}%");
                    });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Format filter
        if ($request->filled('format_type')) {
            $query->where('format_type', $request->input('format_type'));
        }

        // Year filter
        if ($request->filled('year')) {
            $query->where('year', (int) $request->input('year'));
        }

        $projects = $query->paginate(15)->withQueryString();

        // Quick KPI counters
        $stats = [
            'total' => CalendarProject::count(),
            'production_active' => CalendarProject::where('status', CalendarProject::STATUS_PRODUCTION_ACTIVE)->count(),
            'saved' => CalendarProject::where('status', CalendarProject::STATUS_DRAFT_SAVED)->count(),
            'trial' => CalendarProject::where('status', CalendarProject::STATUS_DRAFT_TRIAL)->count(),
        ];

        return view('admin.kalender-pelari.projects.index', compact('projects', 'stats'));
    }

    public function projectsShow(CalendarProject $project): View
    {
        $project->load(['user', 'pages.elements']);
        return view('admin.kalender-pelari.projects.show', compact('project'));
    }

    public function projectsUpdateStatus(Request $request, CalendarProject $project): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:DRAFT_TRIAL,DRAFT_SAVED,PENDING_CHECKOUT,PRODUCTION_ACTIVE,ARCHIVED,EXPIRED_TRIAL',
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $project->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $project->admin_notes,
        ]);

        return back()->with('success', "Status proyek #{$project->id} berhasil diperbarui.");
    }

    public function projectsDestroy(CalendarProject $project): RedirectResponse
    {
        $id = $project->id;
        $name = $project->name;
        $project->delete();

        return redirect()->route('admin.kalender-pelari.projects.index')
            ->with('success', "Proyek '{$name}' (#{$id}) berhasil dihapus.");
    }

    /* ==========================================================
     * 2. TEMPLATE IDOLA / ATLET LARI (DYNAMIC RUNNING IDOLS)
     * ========================================================== */

    public function templatesIndex(): View
    {
        $templates = AthleteTemplate::orderBy('sort_order', 'asc')
            ->latest()
            ->paginate(12);

        $totalAthletes = AthleteTemplate::count();
        $activeAthletes = AthleteTemplate::where('is_active', true)->count();

        return view('admin.kalender-pelari.templates.index', compact('templates', 'totalAthletes', 'activeAthletes'));
    }

    public function templatesCreate(): View
    {
        $template = new AthleteTemplate([
            'accent_color' => '#E63946',
            'paper_color' => 'white',
            'default_font' => 'Inter Tight',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return view('admin.kalender-pelari.templates.form', compact('template'));
    }

    public function templatesStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'category' => 'nullable|string|max:60',
            'bio' => 'nullable|string|max:50000',
            'accent_color' => 'nullable|string|max:20',
            'paper_color' => 'nullable|string|max:20',
            'default_font' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'cover_file' => 'nullable|image|max:10240',
            'month_photos' => 'nullable|array',
            'month_photos.*' => 'nullable|image|max:10240',
        ]);

        $slug = Str::slug($validated['name']);
        $base = $slug;
        $counter = 1;
        while (AthleteTemplate::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        $coverPath = null;
        if ($request->hasFile('cover_file')) {
            $coverPath = $request->file('cover_file')->store("kalender-pelari/templates/{$slug}", 'public');
        }

        $photos = array_fill(0, 13, null);
        if ($coverPath) {
            $photos[0] = $coverPath;
        }

        if ($request->hasFile('month_photos')) {
            foreach ($request->file('month_photos') as $monthIndex => $file) {
                if ($file && $monthIndex >= 0 && $monthIndex <= 12) {
                    $photos[$monthIndex] = $file->store("kalender-pelari/templates/{$slug}", 'public');
                }
            }
        }

        $template = AthleteTemplate::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'category' => $validated['category'] ?? 'Nasional',
            'bio' => $validated['bio'] ?? null,
            'cover_image' => $coverPath ?? ($photos[0] ?? null),
            'photos' => $photos,
            'accent_color' => $validated['accent_color'] ?? '#E63946',
            'paper_color' => $validated['paper_color'] ?? 'white',
            'default_font' => $validated['default_font'] ?? 'Inter Tight',
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.kalender-pelari.templates.index')
            ->with('success', "Template atlet '{$template->name}' berhasil dibuat.");
    }

    public function templatesEdit(AthleteTemplate $template): View
    {
        return view('admin.kalender-pelari.templates.form', compact('template'));
    }

    public function templatesUpdate(Request $request, AthleteTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'category' => 'nullable|string|max:60',
            'bio' => 'nullable|string|max:50000',
            'accent_color' => 'nullable|string|max:20',
            'paper_color' => 'nullable|string|max:20',
            'default_font' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'cover_file' => 'nullable|image|max:10240',
            'month_photos' => 'nullable|array',
            'month_photos.*' => 'nullable|image|max:10240',
        ]);

        $photos = is_array($template->photos) ? $template->photos : array_fill(0, 13, null);

        if ($request->hasFile('cover_file')) {
            $coverPath = $request->file('cover_file')->store("kalender-pelari/templates/{$template->slug}", 'public');
            $template->cover_image = $coverPath;
            $photos[0] = $coverPath;
        }

        if ($request->hasFile('month_photos')) {
            foreach ($request->file('month_photos') as $monthIndex => $file) {
                if ($file && $monthIndex >= 0 && $monthIndex <= 12) {
                    $photos[$monthIndex] = $file->store("kalender-pelari/templates/{$template->slug}", 'public');
                }
            }
        }

        $template->update([
            'name' => $validated['name'],
            'category' => $validated['category'] ?? $template->category,
            'bio' => $validated['bio'] ?? $template->bio,
            'cover_image' => $template->cover_image,
            'photos' => $photos,
            'accent_color' => $validated['accent_color'] ?? $template->accent_color,
            'paper_color' => $validated['paper_color'] ?? $template->paper_color,
            'default_font' => $validated['default_font'] ?? $template->default_font,
            'sort_order' => (int) ($validated['sort_order'] ?? $template->sort_order),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.kalender-pelari.templates.index')
            ->with('success', "Template atlet '{$template->name}' berhasil diperbarui.");
    }

    public function templatesDestroy(AthleteTemplate $template): RedirectResponse
    {
        $name = $template->name;
        $template->delete();

        return redirect()->route('admin.kalender-pelari.templates.index')
            ->with('success', "Template atlet '{$name}' berhasil dihapus.");
    }

    /* ==========================================================
     * 3. API PUBLIK UNTUK STUDIO CALENDAR
     * ========================================================== */

    public function apiActiveTemplates()
    {
        $templates = AthleteTemplate::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(function (AthleteTemplate $t) {
                return [
                    'id' => $t->id,
                    'name' => $t->name,
                    'slug' => $t->slug,
                    'category' => $t->category,
                    'bio' => $t->bio,
                    'cover_url' => $t->cover_url,
                    'photo_urls' => $t->photo_urls,
                    'accent_color' => $t->accent_color,
                    'paper_color' => $t->paper_color,
                    'default_font' => $t->default_font,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }
}

