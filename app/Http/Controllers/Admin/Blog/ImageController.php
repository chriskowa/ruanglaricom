<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Models\BlogMedia;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function upload(Request $request, ImageUploadService $imageService)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();

            $path = $imageService->uploadSingle($file, 'blog/media', 1200, 80);
            $size = Storage::disk('public')->exists($path) ? Storage::disk('public')->size($path) : $file->getSize();

            $media = BlogMedia::create([
                'user_id' => auth()->id(),
                'filename' => $originalName,
                'path' => $path,
                'disk' => 'public',
                'mime_type' => 'image/webp',
                'size' => $size,
                'alt_text' => pathinfo($originalName, PATHINFO_FILENAME),
            ]);

            return response()->json(['location' => $media->url]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
