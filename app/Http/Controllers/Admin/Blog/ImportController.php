<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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

        $url = rtrim($request->wordpress_url, '/');
        $endpoint = $url.'/wp-json/wp/v2/posts?_embed&per_page=10';

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

                // Extract Featured Image
                $featuredImage = null;
                if (isset($post['_embedded']['wp:featuredmedia'][0]['source_url'])) {
                    // In a real scenario, you might want to download this image to local storage
                    // For now, we'll just use the external URL or null if you prefer
                    // $featuredImage = $post['_embedded']['wp:featuredmedia'][0]['source_url'];

                    // Option 2: Download image (simplified)
                    /*
                    $imageUrl = $post['_embedded']['wp:featuredmedia'][0]['source_url'];
                    $imageContent = Http::get($imageUrl)->body();
                    $imageName = 'blog/imported/' . Str::random(10) . '.jpg';
                    Storage::disk('public')->put($imageName, $imageContent);
                    $featuredImage = $imageName;
                    */
                }

                // Create Article
                $article = Article::create([
                    'user_id' => auth()->id(), // Assign to current admin
                    'title' => $post['title']['rendered'],
                    'slug' => $post['slug'],
                    'content' => $post['content']['rendered'],
                    'excerpt' => strip_tags($post['excerpt']['rendered']),
                    'status' => 'published',
                    'published_at' => \Carbon\Carbon::parse($post['date']),
                    'meta_title' => $post['title']['rendered'], // Simplified
                    // 'featured_image' => $featuredImage,
                ]);

                // Handle Categories (Simplified: Create if not exists)
                // Note: WP API returns category IDs, not names directly in 'categories' field without _embed context or separate fetch
                // For better category mapping, we'd need to fetch categories from WP first or use _embedded['wp:term']

                if (isset($post['_embedded']['wp:term'][0])) {
                    foreach ($post['_embedded']['wp:term'][0] as $term) {
                        $category = BlogCategory::firstOrCreate(
                            ['slug' => $term['slug']],
                            ['name' => $term['name']]
                        );
                        // Assign first category found as main category
                        if (! $article->category_id) {
                            $article->update(['category_id' => $category->id]);
                        }
                    }
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

            return back()->with('success', "Successfully imported {$importedCount} posts.");

        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred during import: '.$e->getMessage());
        }
    }
}
