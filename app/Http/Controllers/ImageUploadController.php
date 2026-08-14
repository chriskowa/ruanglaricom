<?php

namespace App\Http\Controllers;

use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    protected ImageUploadService $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Handle single or multi-size WebP image upload via HTTP request.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,bmp|max:10240', // Max 10MB
            'folder' => 'nullable|string|max:50',
            'single' => 'nullable|boolean',
        ]);

        $file = $request->file('image');
        $folder = $request->input('folder', 'uploads');
        $isSingle = $request->boolean('single');

        try {
            if ($isSingle) {
                $path = $this->imageService->uploadSingle($file, $folder);
                return response()->json([
                    'success' => true,
                    'path' => $path,
                    'url' => Storage::disk('public')->url($path),
                ]);
            }

            $paths = $this->imageService->upload($file, $folder);
            $urls = array_map(fn($p) => Storage::disk('public')->url($p), $paths);

            return response()->json([
                'success' => true,
                'paths' => $paths,
                'urls' => $urls,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses upload gambar: ' . $e->getMessage(),
            ], 500);
        }
    }
}
