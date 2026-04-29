<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Race;
use App\Models\RaceCertificate;
use App\Models\RaceSession;
use App\Models\RaceSessionLap;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Str;

class RaceController extends Controller
{
    public function index(Request $request)
    {
        $query = Race::query()
            ->with(['creator:id,name,email'])
            ->withCount(['participants', 'sessions'])
            ->orderByDesc('id');

        if (! Auth::user()?->isAdmin()) {
            $query->where('created_by', Auth::id());
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->get('q'));
            $query->where('name', 'like', "%{$q}%");
        }

        if ($request->filled('created_by')) {
            if (Auth::user()?->isAdmin()) {
                $query->where('created_by', $request->integer('created_by'));
            }
        }

        $races = $query->paginate(20)->withQueryString();

        return view('admin.races.index', compact('races'));
    }

    public function create()
    {
        $users = collect();
        if (Auth::user()?->isAdmin()) {
            $users = User::query()
                ->select(['id', 'name', 'email'])
                ->latest()
                ->limit(50)
                ->get();
        }

        return view('admin.races.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:100',
            'created_by' => 'nullable|integer|exists:users,id',
            'slug' => 'nullable|string|min:3|max:120|unique:races,slug',
            'is_published' => 'nullable|boolean',
            'description' => 'nullable|string|max:5000',
            'location_name' => 'nullable|string|max:255',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date',
            'prize_info' => 'nullable|string|max:8000',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
            'banner' => 'nullable|file|mimes:png,jpg,jpeg|max:4096',
            'gallery' => 'nullable|array',
            'gallery.*' => 'file|mimes:png,jpg,jpeg|max:4096',
        ]);

        $race = DB::transaction(function () use ($validated, $request) {
            $race = new Race;
            $race->name = trim($validated['name']);
            $race->created_by = Auth::user()?->isAdmin()
                ? ($validated['created_by'] ?? Auth::id())
                : Auth::id();
            $race->logo_path = null;
            $race->slug = $this->resolveUniqueSlug($validated['slug'] ?? null, $race->name, null);
            $race->is_published = (bool) ($validated['is_published'] ?? false);
            $race->published_at = $race->is_published ? now() : null;
            $race->description = $validated['description'] ?? null;
            $race->location_name = $validated['location_name'] ?? null;
            $race->start_at = $validated['start_at'] ?? null;
            $race->end_at = $validated['end_at'] ?? null;
            $race->prize_info = $validated['prize_info'] ?? null;
            $race->banner_path = null;
            $race->gallery_paths = null;
            $race->save();

            if ($request->hasFile('logo')) {
                $race->logo_path = $this->storeLogo($request->file('logo'));
                $race->save();
            }

            if ($request->hasFile('banner')) {
                $race->banner_path = $this->storeBanner($request->file('banner'));
                $race->save();
            }

            if ($request->hasFile('gallery')) {
                $galleryPaths = [];
                foreach ((array) $request->file('gallery') as $file) {
                    if (! $file) continue;
                    $galleryPaths[] = $this->storeGalleryImage($file);
                }
                $race->gallery_paths = $galleryPaths;
                $race->save();
            }

            return $race;
        });

        return redirect()
            ->route('admin.races.show', $race)
            ->with('success', 'Race berhasil dibuat.');
    }

    public function show(Race $race)
    {
        $this->ensureCanManageRace($race);
        $race->load(['creator:id,name,email']);
        $race->loadCount(['participants', 'sessions']);

        $sessions = $race->sessions()
            ->select(['id', 'race_id', 'slug', 'category', 'distance_km', 'quota', 'bib_start', 'bib_prefix', 'started_at', 'ended_at', 'created_at'])
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $participants = $race->participants()
            ->select(['id', 'race_id', 'bib_number', 'name', 'predicted_time_ms', 'result_time_ms', 'finished_at', 'created_at'])
            ->orderByRaw('CAST(bib_number AS UNSIGNED) ASC')
            ->orderBy('bib_number')
            ->paginate(50)
            ->withQueryString();

        return view('admin.races.show', compact('race', 'sessions', 'participants'));
    }

    public function edit(Race $race)
    {
        $this->ensureCanManageRace($race);

        $users = collect();
        if (Auth::user()?->isAdmin()) {
            $users = User::query()
                ->select(['id', 'name', 'email'])
                ->latest()
                ->limit(50)
                ->get();
        }

        $race->load(['creator:id,name,email']);

        return view('admin.races.edit', compact('race', 'users'));
    }

    public function update(Request $request, Race $race)
    {
        $this->ensureCanManageRace($race);
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:100',
            'created_by' => 'nullable|integer|exists:users,id',
            'slug' => 'nullable|string|min:3|max:120|unique:races,slug,'.$race->id,
            'is_published' => 'nullable|boolean',
            'description' => 'nullable|string|max:5000',
            'location_name' => 'nullable|string|max:255',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date',
            'prize_info' => 'nullable|string|max:8000',
            'remove_logo' => 'nullable|boolean',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
            'remove_banner' => 'nullable|boolean',
            'banner' => 'nullable|file|mimes:png,jpg,jpeg|max:4096',
            'remove_gallery' => 'nullable|array',
            'remove_gallery.*' => 'string',
            'gallery' => 'nullable|array',
            'gallery.*' => 'file|mimes:png,jpg,jpeg|max:4096',
        ]);

        DB::transaction(function () use ($validated, $request, $race) {
            $race->name = trim($validated['name']);
            if (Auth::user()?->isAdmin() && array_key_exists('created_by', $validated) && $validated['created_by']) {
                $race->created_by = $validated['created_by'];
            }

            $race->slug = $this->resolveUniqueSlug($validated['slug'] ?? null, $race->name, $race->id);

            $newPublished = (bool) ($validated['is_published'] ?? false);
            if ($newPublished && ! $race->is_published) {
                $race->published_at = now();
            }
            if (! $newPublished) {
                $race->published_at = null;
            }
            $race->is_published = $newPublished;

            $race->description = $validated['description'] ?? null;
            $race->location_name = $validated['location_name'] ?? null;
            $race->start_at = $validated['start_at'] ?? null;
            $race->end_at = $validated['end_at'] ?? null;
            $race->prize_info = $validated['prize_info'] ?? null;

            $removeLogo = (bool) ($validated['remove_logo'] ?? false);
            if ($removeLogo && $race->logo_path) {
                Storage::disk('public')->delete($race->logo_path);
                $race->logo_path = null;
            }

            if ($request->hasFile('logo')) {
                if ($race->logo_path) {
                    Storage::disk('public')->delete($race->logo_path);
                }
                $race->logo_path = $this->storeLogo($request->file('logo'));
            }

            $removeBanner = (bool) ($validated['remove_banner'] ?? false);
            if ($removeBanner && $race->banner_path) {
                Storage::disk('public')->delete($race->banner_path);
                $race->banner_path = null;
            }

            if ($request->hasFile('banner')) {
                if ($race->banner_path) {
                    Storage::disk('public')->delete($race->banner_path);
                }
                $race->banner_path = $this->storeBanner($request->file('banner'));
            }

            $currentGallery = collect($race->gallery_paths ?: []);
            $removeGallery = collect($validated['remove_gallery'] ?? [])
                ->filter(fn ($p) => is_string($p) && trim($p) !== '')
                ->values();

            if ($removeGallery->isNotEmpty()) {
                Storage::disk('public')->delete($removeGallery->all());
                $currentGallery = $currentGallery->reject(fn ($p) => $removeGallery->contains($p))->values();
            }

            if ($request->hasFile('gallery')) {
                foreach ((array) $request->file('gallery') as $file) {
                    if (! $file) continue;
                    $currentGallery->push($this->storeGalleryImage($file));
                }
            }

            $race->gallery_paths = $currentGallery->values()->all();

            $race->save();
        });

        return redirect()
            ->route('admin.races.show', $race)
            ->with('success', 'Race berhasil diupdate.');
    }

    public function destroy(Race $race)
    {
        $this->ensureCanManageRace($race);
        $race->delete();

        return redirect()
            ->route('admin.races.index')
            ->with('success', 'Race berhasil dihapus.');
    }

    public function storeSession(Request $request, Race $race)
    {
        $this->ensureCanManageRace($race);

        $validated = $request->validate([
            'category' => 'required|string|min:1|max:100',
            'distance_km' => 'nullable|numeric|min:0.1|max:999.999',
            'quota' => 'nullable|integer|min:1|max:200000',
            'bib_start' => 'nullable|integer|min:1|max:9999999',
            'bib_prefix' => 'nullable|string|max:12',
        ]);

        $slug = null;
        for ($i = 0; $i < 5; $i++) {
            $candidate = Str::lower(Str::random(10));
            if (! RaceSession::query()->where('slug', $candidate)->exists()) {
                $slug = $candidate;
                break;
            }
        }

        RaceSession::create([
            'race_id' => $race->id,
            'slug' => $slug,
            'category' => trim((string) $validated['category']),
            'distance_km' => array_key_exists('distance_km', $validated) ? (float) $validated['distance_km'] : null,
            'quota' => $validated['quota'] ?? null,
            'bib_start' => $validated['bib_start'] ?? null,
            'bib_prefix' => $validated['bib_prefix'] ?? null,
            'started_at' => null,
            'ended_at' => null,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Kategori berhasil dibuat.');
    }

    public function startSession(Race $race, RaceSession $session)
    {
        $this->ensureCanManageRace($race);
        $this->ensureSessionBelongsToRace($race, $session);

        $session->update([
            'started_at' => $session->started_at ?: now(),
            'ended_at' => null,
        ]);

        return back()->with('success', 'Kategori dimulai.');
    }

    public function finishSession(Race $race, RaceSession $session)
    {
        $this->ensureCanManageRace($race);
        $this->ensureSessionBelongsToRace($race, $session);

        if (! $session->started_at) {
            $session->started_at = now();
        }
        if (! $session->slug) {
            for ($i = 0; $i < 5; $i++) {
                $candidate = Str::lower(Str::random(10));
                if (! RaceSession::query()->where('slug', $candidate)->exists()) {
                    $session->slug = $candidate;
                    break;
                }
            }
        }
        $session->ended_at = $session->ended_at ?: now();
        $session->save();

        return back()->with('success', 'Kategori diselesaikan.');
    }

    public function resetSession(Race $race, RaceSession $session)
    {
        $this->ensureCanManageRace($race);
        $this->ensureSessionBelongsToRace($race, $session);

        DB::transaction(function () use ($race, $session) {
            RaceCertificate::query()->where('race_id', $race->id)->where('race_session_id', $session->id)->delete();
            RaceSessionLap::query()->where('race_id', $race->id)->where('race_session_id', $session->id)->delete();
        }, 3);

        return back()->with('success', 'Leaderboard kategori direset.');
    }

    public function destroySession(Race $race, RaceSession $session)
    {
        $this->ensureCanManageRace($race);
        $this->ensureSessionBelongsToRace($race, $session);

        $session->delete();

        return back()->with('success', 'Kategori dihapus.');
    }

    private function ensureCanManageRace(Race $race): void
    {
        $u = Auth::user();
        if (! $u) abort(403);
        if ($u->isAdmin()) return;
        if ((int) $race->created_by !== (int) $u->id) abort(403);
    }

    private function ensureSessionBelongsToRace(Race $race, RaceSession $session): void
    {
        if ((int) $session->race_id !== (int) $race->id) {
            abort(404);
        }
    }

    private function resolveUniqueSlug(?string $slug, string $name, ?int $ignoreId): ?string
    {
        $base = trim((string) ($slug ?: ''));
        $base = $base !== '' ? Str::slug($base) : Str::slug($name);
        if ($base === '') {
            return null;
        }

        $candidate = $base;
        for ($i = 0; $i < 50; $i++) {
            $q = Race::query()->where('slug', $candidate);
            if ($ignoreId) $q->where('id', '!=', $ignoreId);
            if (! $q->exists()) {
                return $candidate;
            }
            $candidate = $base.'-'.($i + 2);
        }

        return $base.'-'.time();
    }

    private function storeLogo($file): string
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->read($file);

        if ($image->width() < 200 || $image->height() < 200) {
            throw ValidationException::withMessages([
                'logo' => 'Resolusi logo minimal 200x200 pixel.',
            ]);
        }

        $folder = 'race-logos';
        if (! Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        $filename = uniqid().'_'.time().'.png';
        $path = $folder.'/'.$filename;
        $fullPath = Storage::disk('public')->path($path);

        $image->toPng()->save($fullPath);

        return $path;
    }

    private function storeBanner($file): string
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->read($file);

        if ($image->width() < 900 || $image->height() < 400) {
            throw ValidationException::withMessages([
                'banner' => 'Resolusi banner minimal 900x400 pixel.',
            ]);
        }

        $folder = 'race-banners';
        if (! Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        $filename = uniqid().'_'.time().'.jpg';
        $path = $folder.'/'.$filename;
        $fullPath = Storage::disk('public')->path($path);

        $image->toJpeg(82)->save($fullPath);

        return $path;
    }

    private function storeGalleryImage($file): string
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->read($file);

        if ($image->width() < 300 || $image->height() < 300) {
            throw ValidationException::withMessages([
                'gallery' => 'Resolusi gallery minimal 300x300 pixel.',
            ]);
        }

        $folder = 'race-galleries';
        if (! Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        $filename = uniqid().'_'.time().'.jpg';
        $path = $folder.'/'.$filename;
        $fullPath = Storage::disk('public')->path($path);

        $image->toJpeg(82)->save($fullPath);

        return $path;
    }
}
