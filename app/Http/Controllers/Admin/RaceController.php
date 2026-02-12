<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Race;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class RaceController extends Controller
{
    public function index(Request $request)
    {
        $query = Race::query()
            ->with(['creator:id,name,email'])
            ->withCount(['participants', 'sessions'])
            ->orderByDesc('id');

        if ($request->filled('q')) {
            $q = trim((string) $request->get('q'));
            $query->where('name', 'like', "%{$q}%");
        }

        if ($request->filled('created_by')) {
            $query->where('created_by', $request->integer('created_by'));
        }

        $races = $query->paginate(20)->withQueryString();

        return view('admin.races.index', compact('races'));
    }

    public function create()
    {
        $users = User::query()
            ->select(['id', 'name', 'email'])
            ->latest()
            ->limit(50)
            ->get();

        return view('admin.races.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:100',
            'created_by' => 'nullable|integer|exists:users,id',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
        ]);

        $race = DB::transaction(function () use ($validated, $request) {
            $race = new Race;
            $race->name = trim($validated['name']);
            $race->created_by = $validated['created_by'] ?? Auth::id();
            $race->logo_path = null;
            $race->save();

            if ($request->hasFile('logo')) {
                $race->logo_path = $this->storeLogo($request->file('logo'));
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
        $race->load(['creator:id,name,email']);
        $race->loadCount(['participants', 'sessions']);

        $sessions = $race->sessions()
            ->select(['id', 'race_id', 'slug', 'category', 'distance_km', 'started_at', 'ended_at', 'created_at'])
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
        $users = User::query()
            ->select(['id', 'name', 'email'])
            ->latest()
            ->limit(50)
            ->get();

        $race->load(['creator:id,name,email']);

        return view('admin.races.edit', compact('race', 'users'));
    }

    public function update(Request $request, Race $race)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:100',
            'created_by' => 'nullable|integer|exists:users,id',
            'remove_logo' => 'nullable|boolean',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
        ]);

        DB::transaction(function () use ($validated, $request, $race) {
            $race->name = trim($validated['name']);
            $race->created_by = $validated['created_by'] ?? $race->created_by;

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

            $race->save();
        });

        return redirect()
            ->route('admin.races.show', $race)
            ->with('success', 'Race berhasil diupdate.');
    }

    public function destroy(Race $race)
    {
        $race->delete();

        return redirect()
            ->route('admin.races.index')
            ->with('success', 'Race berhasil dihapus.');
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
}
