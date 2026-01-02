<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\City;
use App\Models\Province;
use App\Models\Pacer;

class ProfileController extends Controller
{
    private function processImage($file, $folder = 'avatars', $quality = 75)
    {
        $manager = new ImageManager(new Driver());
        
        // Generate unique filename dengan timestamp
        $filename = uniqid() . '_' . time() . '.webp';
        $path = $folder . '/' . $filename;
        
        // Process image: resize jika terlalu besar, compress, dan convert ke WebP
        $image = $manager->read($file);
        
        // Resize jika dimensi lebih besar dari 1920px (untuk banner) atau 800px (untuk profile)
        $maxWidth = $folder === 'banners' ? 1920 : 800;
        if ($image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }
        
        // Convert ke WebP dengan quality 75% dan dapatkan encoded data
        $webpImage = $image->toWebp($quality);
        
        // Pastikan directory ada
        $directory = Storage::disk('public')->path($folder);
        if (!is_dir($directory)) {
            Storage::disk('public')->makeDirectory($folder);
        }
        
        // Simpan ke storage menggunakan save() method
        $fullPath = Storage::disk('public')->path($path);
        $webpImage->save($fullPath);
        
        return $path;
    }

    private function deleteImage($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function show()
    {
        $user = Auth::user();
        $user->load('city.province', 'wallet');
        $user->loadCount('followers');
        $pacer = Pacer::where('user_id', $user->id)->first();
        
        $cities = City::with('province')->orderBy('name')->get();
        $provinces = Province::orderBy('name')->get();
        
        return view('profile.show', compact('user', 'cities', 'provinces', 'pacer'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'date_of_birth' => 'nullable|date',
                'address' => 'nullable|string|max:500',
            'city_id' => 'nullable|exists:cities,id',
            'gender' => 'nullable|in:male,female',
            'weight' => 'nullable|numeric|min:20|max:300',
            'height' => 'nullable|integer|min:50|max:300',
            'strava_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'profile_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'password' => 'nullable|string|min:8|confirmed',
            'bank_name' => 'nullable|string|max:120',
            'bank_account_name' => 'nullable|string|max:120',
            'bank_account_number' => 'nullable|string|max:50',
            'pb_5k' => 'nullable|string|max:20',
            'pb_10k' => 'nullable|string|max:20',
            'pb_hm' => 'nullable|string|max:20',
            'pb_fm' => 'nullable|string|max:20',
            'pacer_nickname' => 'nullable|string|max:100',
            'pacer_category' => 'nullable|string|max:50',
            'pacer_pace' => 'nullable|string|max:20',
            'pacer_whatsapp' => 'nullable|string|max:20',
            'pacer_bio' => 'nullable|string',
            'pacer_tags' => 'nullable|string',
            'pacer_race_portfolio' => 'nullable|string',
            'is_pacer' => 'nullable|boolean',
        ]);

        // Update basic info
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
                $user->date_of_birth = $validated['date_of_birth'] ?? null;
                $user->address = $validated['address'] ?? null;
                $user->city_id = $validated['city_id'] ?? null;
        $user->gender = $validated['gender'] ?? null;
        $user->weight = $validated['weight'] ?? null;
        $user->height = $validated['height'] ?? null;
        $user->strava_url = $validated['strava_url'] ?? null;
        $user->instagram_url = $validated['instagram_url'] ?? null;
        $user->facebook_url = $validated['facebook_url'] ?? null;
        $user->tiktok_url = $validated['tiktok_url'] ?? null;
        $user->bank_name = $validated['bank_name'] ?? null;
        $user->bank_account_name = $validated['bank_account_name'] ?? null;
        $user->bank_account_number = $validated['bank_account_number'] ?? null;
        $user->pb_5k = $validated['pb_5k'] ?? null;
        $user->pb_10k = $validated['pb_10k'] ?? null;
        $user->pb_hm = $validated['pb_hm'] ?? null;
        $user->pb_fm = $validated['pb_fm'] ?? null;
        if (isset($validated['is_pacer'])) {
            $user->is_pacer = $validated['is_pacer'];
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            $this->deleteImage($user->avatar);
            
            $avatarPath = $this->processImage($request->file('avatar'), 'avatars', 75);
            $user->avatar = $avatarPath;
        }

        // Handle profile images upload (max 3 images)
        if ($request->hasFile('profile_images')) {
            $profileImages = $user->profile_images ?? [];
            
            // Delete old images if replacing
            foreach ($profileImages as $oldImage) {
                $this->deleteImage($oldImage);
            }
            
            $newImages = [];
            $uploadedFiles = $request->file('profile_images');
            
            // Limit to 3 images
            $filesToProcess = array_slice($uploadedFiles, 0, 3);
            
            foreach ($filesToProcess as $file) {
                $imagePath = $this->processImage($file, 'profile_images', 75);
                $newImages[] = $imagePath;
            }
            
            $user->profile_images = $newImages;
        }

        // Handle banner upload
        if ($request->hasFile('banner')) {
            // Delete old banner if exists
            $this->deleteImage($user->banner);
            
            $bannerPath = $this->processImage($request->file('banner'), 'banners', 75);
            $user->banner = $bannerPath;
        }

        // Update password if provided
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $pacer = Pacer::where('user_id', $user->id)->first();
        if ($pacer) {
            $pacer->nickname = $validated['pacer_nickname'] ?? $pacer->nickname;
            $pacer->category = $validated['pacer_category'] ?? $pacer->category;
            $pacer->pace = $validated['pacer_pace'] ?? $pacer->pace;
            $pacer->whatsapp = $validated['pacer_whatsapp'] ?? $pacer->whatsapp;
            $pacer->bio = $validated['pacer_bio'] ?? $pacer->bio;
            $pacer->tags = isset($validated['pacer_tags']) && $validated['pacer_tags'] !== null
                ? array_values(array_filter(array_map('trim', preg_split('/[,;]+/', $validated['pacer_tags']))))
                : $pacer->tags;
            $pacer->race_portfolio = isset($validated['pacer_race_portfolio']) && $validated['pacer_race_portfolio'] !== null
                ? array_values(array_filter(array_map('trim', preg_split('/[,;]+/', $validated['pacer_race_portfolio']))))
                : $pacer->race_portfolio;
            $pacer->save();
        }

        return redirect()->route('profile.show')->with('success', 'Profile berhasil diperbarui!');
    }
}
