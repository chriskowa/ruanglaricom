<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StrengthExercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StrengthExerciseController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureTableExists();

        $query = StrengthExercise::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('equipment', 'like', "%{$search}%")
                  ->orWhere('target_muscles', 'like', "%{$search}%");
            });
        }

        $exercises = $query->orderBy('category')->orderBy('name')->paginate(15)->withQueryString();
        $totalCount = StrengthExercise::count();

        return view('admin.strength_exercises.index', compact('exercises', 'totalCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:legs_lower_body,core,full_body,upper_body',
            'equipment' => 'nullable|string|max:255',
            'default_sets' => 'nullable|string|max:50',
            'default_reps' => 'nullable|string|max:50',
            'instructions' => 'nullable|string',
            'target_muscles' => 'nullable|string|max:255',
            'media_type' => 'required|in:image,gif,video,url',
            'media_file' => 'nullable|file|mimes:gif,jpg,jpeg,png,webp,mp4,webm|max:15360',
            'media_url' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $mediaUrl = $validated['media_url'] ?? null;

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $path = $file->store('strength', 'public');
            $mediaUrl = Storage::url($path);
        }

        StrengthExercise::create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'equipment' => $validated['equipment'] ?? 'Bodyweight',
            'default_sets' => $validated['default_sets'] ?? '3',
            'default_reps' => $validated['default_reps'] ?? '10-12 reps',
            'instructions' => $validated['instructions'] ?? null,
            'target_muscles' => $validated['target_muscles'] ?? null,
            'media_type' => $validated['media_type'],
            'media_url' => $mediaUrl,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.strength-exercises.index')->with('success', 'Gerakan strength training berhasil ditambahkan.');
    }

    public function update(Request $request, StrengthExercise $strengthExercise)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:legs_lower_body,core,full_body,upper_body',
            'equipment' => 'nullable|string|max:255',
            'default_sets' => 'nullable|string|max:50',
            'default_reps' => 'nullable|string|max:50',
            'instructions' => 'nullable|string',
            'target_muscles' => 'nullable|string|max:255',
            'media_type' => 'required|in:image,gif,video,url',
            'media_file' => 'nullable|file|mimes:gif,jpg,jpeg,png,webp,mp4,webm|max:15360',
            'media_url' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $path = $file->store('strength', 'public');
            $validated['media_url'] = Storage::url($path);
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        unset($validated['media_file']);

        $strengthExercise->update($validated);

        return redirect()->route('admin.strength-exercises.index')->with('success', 'Gerakan strength training berhasil diperbarui.');
    }

    public function destroy(StrengthExercise $strengthExercise)
    {
        $strengthExercise->delete();
        return redirect()->route('admin.strength-exercises.index')->with('success', 'Gerakan berhasil dihapus.');
    }

    public function seedDefaults()
    {
        $defaults = StrengthExercise::getDefaultLibrary();
        $inserted = 0;

        foreach ($defaults as $cat => $items) {
            foreach ($items as $item) {
                StrengthExercise::updateOrCreate(
                    ['name' => $item['name']],
                    [
                        'category' => $cat,
                        'equipment' => $item['equipment'] ?? 'Bodyweight',
                        'default_sets' => $item['sets'] ?? '3',
                        'default_reps' => $item['reps'] ?? '10-12 reps',
                        'instructions' => $item['instructions'] ?? null,
                        'target_muscles' => $item['target_muscles'] ?? null,
                        'media_type' => $item['media_type'] ?? 'gif',
                        'media_url' => $item['media_url'] ?? null,
                        'is_active' => true,
                    ]
                );
                $inserted++;
            }
        }

        return redirect()->route('admin.strength-exercises.index')->with('success', "Master data awal ($inserted gerakan) berhasil dimuat.");
    }

    private function ensureTableExists(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('strength_exercises')) {
            \Illuminate\Support\Facades\Schema::create('strength_exercises', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('category');
                $table->string('equipment')->nullable();
                $table->string('default_sets')->default('3');
                $table->string('default_reps')->default('10-12 reps');
                $table->enum('media_type', ['image', 'gif', 'video', 'url'])->default('gif');
                $table->string('media_url')->nullable();
                $table->text('instructions')->nullable();
                $table->string('target_muscles')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // Auto-seed default 21 exercises if table is empty
        if (StrengthExercise::count() === 0) {
            $defaults = StrengthExercise::getDefaultLibrary();
            foreach ($defaults as $cat => $items) {
                foreach ($items as $item) {
                    StrengthExercise::create([
                        'name' => $item['name'],
                        'category' => $cat,
                        'equipment' => $item['equipment'] ?? 'Bodyweight',
                        'default_sets' => $item['sets'] ?? '3',
                        'default_reps' => $item['reps'] ?? '10-12 reps',
                        'instructions' => $item['instructions'] ?? null,
                        'target_muscles' => $item['target_muscles'] ?? null,
                        'media_type' => $item['media_type'] ?? 'url',
                        'media_url' => $item['media_url'] ?? null,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
