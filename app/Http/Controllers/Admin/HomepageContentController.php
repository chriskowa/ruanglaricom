<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HomepageContentController extends Controller
{
    public function index()
    {
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE homepage_contents MODIFY floating_image TEXT NULL, MODIFY hero_image TEXT NULL");
        } catch (\Throwable $e) {
            // Silently ignore if already modified or not supported by DB engine
        }

        $content = HomepageContent::firstOrNew();

        // Decode current slides for the form
        $currentSlides = json_decode($content->hero_image, true);
        if (!is_array($currentSlides)) {
            $currentSlides = !empty($content->hero_image) ? [$content->hero_image, '', ''] : ['', '', ''];
        }
        while (count($currentSlides) < 3) {
            $currentSlides[] = '';
        }

        $defaultSlides = [
            'https://ruanglari.com/storage/blog/media/92e97762-78b7-48e4-8b94-176b4e7fdf95.webp',
            'https://ruanglari.com/storage/blog/media/305fd042-17e8-4e89-86d7-8666d4e2229f.webp',
            'https://ruanglari.com/storage/blog/media/21bb785d-d104-4129-99ce-d84ae98afd3a.webp',
        ];

        // Format existing athlete photos for Dropzone manager
        $rawAthletes = $content->raw_athlete_images;
        $existingAthleteImages = [];
        foreach ($rawAthletes as $idx => $rawPath) {
            $url = str_starts_with($rawPath, 'http') ? $rawPath : asset($rawPath);
            $existingAthleteImages[] = [
                'id'       => $idx + 1,
                'path'     => $rawPath,
                'url'      => $url,
                'filename' => basename($rawPath),
            ];
        }

        return view('admin.homepage.index', compact('content', 'currentSlides', 'defaultSlides', 'existingAthleteImages'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'headline'                  => 'nullable|string',
            'subheadline'               => 'nullable|string',
            'hero_slide_1'              => 'nullable|image|max:5120',
            'hero_slide_2'              => 'nullable|image|max:5120',
            'hero_slide_3'              => 'nullable|image|max:5120',
            'hero_slide_1_url'          => 'nullable|string|max:500',
            'hero_slide_2_url'          => 'nullable|string|max:500',
            'hero_slide_3_url'          => 'nullable|string|max:500',
            'athlete_images.*'          => 'nullable|image|max:5120',
            'existing_athlete_images'   => 'nullable|array',
            'existing_athlete_images.*' => 'nullable|string',
            'new_athlete_url'           => 'nullable|string|max:500',
            'floating_image'            => 'nullable|image|max:5120',
            'floating_image_url'        => 'nullable|string|max:500',
        ]);

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE homepage_contents MODIFY floating_image TEXT NULL, MODIFY hero_image TEXT NULL");
        } catch (\Throwable $e) {
            // Silently ignore
        }

        $content = HomepageContent::firstOrNew();
        $content->headline = $request->headline;
        $content->subheadline = $request->subheadline;

        // Current slides array
        $existingSlides = json_decode($content->hero_image, true);
        if (!is_array($existingSlides)) {
            $existingSlides = !empty($content->hero_image) ? [$content->hero_image, '', ''] : ['', '', ''];
        }
        while (count($existingSlides) < 3) {
            $existingSlides[] = '';
        }

        // Process slide 1, 2, 3
        for ($i = 1; $i <= 3; $i++) {
            $fileKey = "hero_slide_{$i}";
            $urlKey  = "hero_slide_{$i}_url";
            $idx     = $i - 1;

            if ($request->hasFile($fileKey)) {
                $path = $request->file($fileKey)->store('homepage', 'public');
                $existingSlides[$idx] = 'storage/' . $path;
            } elseif ($request->filled($urlKey)) {
                $existingSlides[$idx] = trim($request->input($urlKey));
            }
        }

        // Save slides array as JSON
        $hasCustomSlides = false;
        foreach ($existingSlides as $s) {
            if (!empty($s)) {
                $hasCustomSlides = true;
                break;
            }
        }

        if ($hasCustomSlides) {
            $content->hero_image = json_encode($existingSlides);
        }

        // ==========================================
        // Athlete Images Multi-Photo Dropzone
        // ==========================================
        $finalAthleteImages = [];

        // 1. Maintain existing photos (preserving reorder & deletions)
        if ($request->has('existing_athlete_images') && is_array($request->existing_athlete_images)) {
            foreach ($request->existing_athlete_images as $path) {
                $trimmed = trim($path);
                if (!empty($trimmed)) {
                    $finalAthleteImages[] = $trimmed;
                }
            }
        }

        // 2. Add newly uploaded athlete photos from Dropzone
        if ($request->hasFile('athlete_images')) {
            foreach ($request->file('athlete_images') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('homepage', 'public');
                    $finalAthleteImages[] = 'storage/' . $path;
                }
            }
        }

        // 3. Add single new athlete url if provided
        if ($request->filled('new_athlete_url')) {
            $url = trim($request->input('new_athlete_url'));
            if (!empty($url)) {
                $finalAthleteImages[] = $url;
            }
        }

        // 4. Backward compatibility fallback if legacy fields were used
        if (empty($finalAthleteImages)) {
            if ($request->hasFile('floating_image')) {
                $path = $request->file('floating_image')->store('homepage', 'public');
                $finalAthleteImages[] = 'storage/' . $path;
            } elseif ($request->filled('floating_image_url')) {
                $finalAthleteImages[] = trim($request->input('floating_image_url'));
            }
        }

        if (!empty($finalAthleteImages)) {
            $content->floating_image = json_encode(array_values($finalAthleteImages));
        } else {
            // Set null so default athlete is used
            $content->floating_image = null;
        }

        $content->save();

        // Clear cache so frontend reflects updates immediately
        Cache::forget('home.content');

        return redirect()->back()->with('success', 'Homepage Hero & Athlete visual updated successfully.');
    }
}
