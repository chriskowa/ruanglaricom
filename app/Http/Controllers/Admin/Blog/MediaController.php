<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Models\BlogMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        // Handle Cloudinary Tab
        if ($request->has('source') && $request->source === 'cloudinary') {
            return $this->cloudinaryIndex($request);
        }

        // Local Media Logic
        $query = BlogMedia::latest();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('filename', 'like', "%{$search}%")
                  ->orWhere('alt_text', 'like', "%{$search}%");
        }

        if ($request->has('type')) {
            if ($request->type === 'image') {
                $query->where('mime_type', 'like', 'image/%');
            }
        }

        $media = $query->paginate(24);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.blog.media.partials.grid', compact('media'))->render(),
                'pagination' => (string) $media->links()
            ]);
        }

        return view('admin.blog.media.index', compact('media'));
    }

    private function cloudinaryIndex(Request $request)
    {
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $apiKey = env('CLOUDINARY_API_KEY');
        $apiSecret = env('CLOUDINARY_API_SECRET');

        if (!$cloudName || !$apiKey || !$apiSecret) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Cloudinary credentials not configured. Please set CLOUDINARY_CLOUD_NAME in .env'], 400);
            }
            // Pass error to view
            return view('admin.blog.media.index', [
                'media' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 24),
                'cloudinaryError' => 'Cloudinary credentials missing (Cloud Name)'
            ]);
        }

        // List resources from Cloudinary Admin API
        // GET /resources/image
        $nextCursor = $request->cursor;
        $url = "https://api.cloudinary.com/v1_1/{$cloudName}/resources/image";
        
        try {
            $response = Http::withBasicAuth($apiKey, $apiSecret)
                ->get($url, [
                    'max_results' => 24,
                    'next_cursor' => $nextCursor,
                    // 'prefix' => $request->search // Search by prefix if needed
                ]);

            if ($response->failed()) {
                throw new \Exception('Cloudinary API Error: ' . $response->body());
            }

            $data = $response->json();
            $resources = $data['resources'] ?? [];
            $nextCursor = $data['next_cursor'] ?? null;

            // Transform to match local media structure partially or use separate view
        // We'll wrap them in objects to look like BlogMedia for the grid
        $mediaItems = collect($resources)->map(function ($res) {
            return (object) [
                'id' => $res['public_id'], // Use public_id as ID
                'filename' => $res['public_id'] . '.' . $res['format'],
                'url' => $res['secure_url'],
                'mime_type' => 'image/' . $res['format'],
                'is_cloudinary' => true
            ];
        });

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.blog.media.partials.grid_cloudinary', ['media' => $mediaItems])->render(),
                'next_cursor' => $nextCursor
            ]);
        }

        return view('admin.blog.media.index', [
            'media' => $mediaItems, // Use standard variable name
            'nextCursor' => $nextCursor
        ]);
    } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return view('admin.blog.media.index', [
                'media' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 24),
                'cloudinaryError' => $e->getMessage(),
                'activeTab' => 'cloudinary'
            ]);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $path = $file->store('blog/media', 'public');
        
        $media = BlogMedia::create([
            'user_id' => auth()->id(),
            'filename' => $filename,
            'path' => $path,
            'disk' => 'public',
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt_text' => pathinfo($filename, PATHINFO_FILENAME),
        ]);

        return response()->json([
            'success' => true,
            'media' => $media,
            'url' => $media->url
        ]);
    }

    public function destroy(BlogMedia $media)
    {
        if (Storage::disk($media->disk)->exists($media->path)) {
            Storage::disk($media->disk)->delete($media->path);
        }
        
        $media->delete();

        return response()->json(['success' => true]);
    }
}
