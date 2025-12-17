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
        
        $cities = City::with('province')->orderBy('name')->get();
        $provinces = Province::orderBy('name')->get();
        
        return view('profile.show', compact('user', 'cities', 'provinces'));
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
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'profile_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Update basic info
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
                $user->date_of_birth = $validated['date_of_birth'] ?? null;
                $user->address = $validated['address'] ?? null;
                $user->city_id = $validated['city_id'] ?? null;
                $user->gender = $validated['gender'] ?? null;

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

        return redirect()->route('profile.show')->with('success', 'Profile berhasil diperbarui!');
    }
}
