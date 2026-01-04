<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class FeedController extends Controller
{
    public function index()
    {
        // Get posts from users that the current user follows, plus own posts
        $followingIds = Auth::user()->following()->pluck('following_id')->toArray();
        $followingIds[] = Auth::id();

        $posts = Post::whereIn('user_id', $followingIds)
            ->with(['user', 'likes', 'comments.user'])
            ->latest()
            ->paginate(10);

        return view('feed.index', [
            'posts' => $posts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB per image
        ]);

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $images[] = $this->processImage($file, 'posts');
            }
        }

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'images' => $images,
        ]);

        // Create notification for followers
        $followers = Auth::user()->followers;
        foreach ($followers as $follower) {
            Notification::create([
                'user_id' => $follower->id,
                'type' => 'post',
                'title' => 'Post Baru',
                'message' => Auth::user()->name.' membuat post baru',
                'reference_type' => 'Post',
                'reference_id' => $post->id,
            ]);
        }

        return back()->with('success', 'Post berhasil dibuat');
    }

    public function like(Post $post)
    {
        $like = PostLike::firstOrCreate([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
        ]);

        if ($like->wasRecentlyCreated) {
            $post->increment('likes_count');

            // Create notification if not own post
            if ($post->user_id !== Auth::id()) {
                Notification::create([
                    'user_id' => $post->user_id,
                    'type' => 'like',
                    'title' => 'Post Disukai',
                    'message' => Auth::user()->name.' menyukai post Anda',
                    'reference_type' => 'Post',
                    'reference_id' => $post->id,
                ]);
            }
        }

        return response()->json([
            'liked' => true,
            'likes_count' => $post->fresh()->likes_count,
        ]);
    }

    public function unlike(Post $post)
    {
        PostLike::where('post_id', $post->id)
            ->where('user_id', Auth::id())
            ->delete();

        $post->decrement('likes_count');

        return response()->json([
            'liked' => false,
            'likes_count' => $post->fresh()->likes_count,
        ]);
    }

    public function comment(Request $request, Post $post)
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:post_comments,id',
        ]);

        $comment = PostComment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        $post->increment('comments_count');

        // Create notification if not own post
        if ($post->user_id !== Auth::id()) {
            Notification::create([
                'user_id' => $post->user_id,
                'type' => 'comment',
                'title' => 'Komentar Baru',
                'message' => Auth::user()->name.' mengomentari post Anda',
                'reference_type' => 'Post',
                'reference_id' => $post->id,
            ]);
        }

        return back()->with('success', 'Komentar berhasil ditambahkan');
    }

    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        // Delete images
        if ($post->images) {
            foreach ($post->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $post->delete();

        return back()->with('success', 'Post berhasil dihapus');
    }

    private function processImage($file, $folder = 'posts', $quality = 75)
    {
        $manager = new ImageManager(new Driver);

        $filename = uniqid().'_'.time().'.webp';
        $path = $folder.'/'.$filename;

        // Process image: resize jika terlalu besar, compress, dan convert ke WebP
        $image = $manager->read($file);

        // Resize jika dimensi lebih besar dari 1920px (untuk feed images)
        if ($image->width() > 1920) {
            $image->scale(width: 1920);
        }

        // Convert ke WebP dengan quality 75% dan dapatkan encoded data
        $webpImage = $image->toWebp($quality);

        // Pastikan directory ada
        $directory = Storage::disk('public')->path($folder);
        if (! is_dir($directory)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        // Simpan ke storage menggunakan save() method
        $fullPath = Storage::disk('public')->path($path);
        $webpImage->save($fullPath);

        return $path;
    }
}
