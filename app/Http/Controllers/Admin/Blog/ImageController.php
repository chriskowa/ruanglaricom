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
            $ext = strtolower($file->getClientOriginalExtension());
            $mime = strtolower((string) ($file->getMimeType() ?? ''));
            $isSvg = $ext === 'svg' || str_contains($mime, 'svg');

            if ($isSvg) {
                $path = $file->store('blog/media', 'public');
                $mimeType = 'image/svg+xml';
                $size = Storage::disk('public')->size($path);
            } else {
                $path = $imageService->uploadSingle($file, 'blog/media', 1200, 80);
                $mimeType = 'image/webp';
                $size = Storage::disk('public')->exists($path) ? Storage::disk('public')->size($path) : $file->getSize();
            }

            $media = BlogMedia::create([
                'user_id' => auth()->id(),
                'filename' => $originalName,
                'path' => $path,
                'disk' => 'public',
                'mime_type' => $mimeType,
                'size' => $size,
                'alt_text' => pathinfo($originalName, PATHINFO_FILENAME),
            ]);

            return response()->json(['location' => $media->url]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
