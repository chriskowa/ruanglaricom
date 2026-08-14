<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function upload(Request $request, ImageUploadService $imageService)
    {
        if ($request->hasFile('file')) {
            $path = $imageService->uploadSingle($request->file('file'), 'blog/content', 1200, 80);

            return response()->json(['location' => asset('storage/'.$path)]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
