<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\BlogMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImportController extends Controller
{
    public function index()
    {
        return view('admin.blog.import.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'wordpress_url' => 'required|url',
        ]);

        $url = trim($request->wordpress_url);

        // Handle direct JSON API posts endpoint vs base WordPress site URL
        if (str_contains($url, 'wp-json/wp/v2/posts')) {
            if (!str_contains($url, '_embed')) {
                $url .= (str_contains($url, '?') ? '&' : '?') . '_embed';
            }
            if (!str_contains($url, 'per_page')) {
                $url .= '&per_page=10';
            }
            $endpoint = $url;
        } else {
            $url = rtrim($url, '/');
            $endpoint = $url . '/wp-json/wp/v2/posts?_embed&per_page=10';
        }

        try {
            $response = Http::get($endpoint);

            if ($response->failed()) {
                return back()->with('error', 'Failed to fetch posts from WordPress URL. Please check the URL and try again.');
            }

            $posts = $response->json();
            $importedCount = 0;

            foreach ($posts as $post) {
                // Check if post already exists by slug
                if (Article::where('slug', $post['slug'])->exists()) {
                    continue;
                }

                // Extract and download Featured Image
                $featuredImage = null;
                if (isset($post['_embedded']['wp:featuredmedia'][0]['source_url'])) {
                    try {
                        $imageUrl = $post['_embedded']['wp:featuredmedia'][0]['source_url'];
                        $imageResponse = Http::get($imageUrl);
                        if ($imageResponse->successful()) {
                            $imageContent = $imageResponse->body();
                            
                            // Decode and convert to WebP format
                            try {
                                $manager = new ImageManager(new Driver());
                                $image = $manager->read($imageContent);
                                
                                // Resize if width exceeds 1920px
                                if ($image->width() > 1920) {
                                    $image->scale(width: 1920);
                                }
                                
                                $webpContent = $image->toWebp(85);
                                $imageName = 'blog/featured/' . Str::random(10) . '_' . time() . '.webp';
                                Storage::disk('public')->put($imageName, $webpContent);
                                $featuredImage = $imageName;
                                
                                // Register in blog_media table for Media Library integration
                                BlogMedia::create([
                                    'user_id' => auth()->id(),
                                    'filename' => basename($imageName),
                                    'path' => $imageName,
                                    'mime_type' => 'image/webp',
                                    'size' => strlen($webpContent),
                                ]);
                            } catch (\Throwable $imgEx) {
                                // Fallback raw save if GD/Intervention fails
                                $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                                $imageName = 'blog/featured/' . Str::random(10) . '_' . time() . '.' . $extension;
                                Storage::disk('public')->put($imageName, $imageContent);
                                $featuredImage = $imageName;
                            }
                        }
                    } catch (\Throwable $e) {
                        // Fail silently on image downloading issues
                    }
                }

                // Create Article (as draft)
                $article = Article::create([
                    'user_id' => auth()->id(), // Assign to current admin
                    'title' => $post['title']['rendered'],
                    'slug' => $post['slug'],
                    'content' => $post['content']['rendered'],
                    'excerpt' => strip_tags($post['excerpt']['rendered']),
                    'status' => 'draft',
                    'published_at' => null,
                    'meta_title' => $post['title']['rendered'],
                    'featured_image' => $featuredImage,
                ]);

                // Handle Categories
                $categoryIds = [];
                if (isset($post['_embedded']['wp:term'][0])) {
                    foreach ($post['_embedded']['wp:term'][0] as $term) {
                        $category = BlogCategory::firstOrCreate(
                            ['slug' => $term['slug']],
                            ['name' => $term['name']]
                        );
                        $categoryIds[] = $category->id;
                        
                        // Assign first category found as main category
                        if (! $article->category_id) {
                            $article->update(['category_id' => $category->id]);
                        }
                    }
                }
                if (!empty($categoryIds)) {
                    $article->categories()->sync($categoryIds);
                }

                // Handle Tags
                if (isset($post['_embedded']['wp:term'][1])) {
                    $tagIds = [];
                    foreach ($post['_embedded']['wp:term'][1] as $term) {
                        $tag = BlogTag::firstOrCreate(
                            ['slug' => $term['slug']],
                            ['name' => $term['name']]
                        );
                        $tagIds[] = $tag->id;
                    }
                    $article->tags()->sync($tagIds);
                }

                $importedCount++;
            }

            return back()->with('success', "Successfully imported {$importedCount} posts as drafts.");

        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred during import: '.$e->getMessage());
        }
    }
}
