<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::with('category', 'user')->latest()->paginate(10);
        return view('admin.blog.articles.index', compact('articles'));
    }

    public function create()
    {
        $categories = BlogCategory::all();
        $tags = BlogTag::all();
        return view('admin.blog.articles.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug',
            'category_id' => 'nullable|exists:blog_categories,id',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published,archived',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
            'new_tags' => 'nullable|string', // Comma separated new tags
            
            // SEO
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|url',
        ]);

        $validated['user_id'] = auth()->id();
        
        if ($request->filled('slug')) {
            $validated['slug'] = Str::slug($request->slug);
        } else {
            $validated['slug'] = Str::slug($request->title);
        }

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('blog/featured', 'public');
            $validated['featured_image'] = $path;
        }

        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        $article = Article::create($validated);

        // Handle Tags
        $tagIds = $validated['tags'] ?? [];
        
        // Handle new tags
        if ($request->filled('new_tags')) {
            $newTagNames = explode(',', $request->new_tags);
            foreach ($newTagNames as $tagName) {
                $tagName = trim($tagName);
                if ($tagName) {
                    $tag = BlogTag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );
                    $tagIds[] = $tag->id;
                }
            }
        }

        $article->tags()->sync(array_unique($tagIds));

        return redirect()->route('admin.blog.articles.index')->with('success', 'Article created successfully.');
    }

    public function edit(Article $article)
    {
        $categories = BlogCategory::all();
        $tags = BlogTag::all();
        $articleTags = $article->tags->pluck('id')->toArray();
        return view('admin.blog.articles.edit', compact('article', 'categories', 'tags', 'articleTags'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug,' . $article->id,
            'category_id' => 'nullable|exists:blog_categories,id',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published,archived',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
            'new_tags' => 'nullable|string',
            
            // SEO
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|url',
        ]);

        if ($request->filled('slug')) {
            $validated['slug'] = Str::slug($request->slug);
        } else {
            $validated['slug'] = Str::slug($request->title);
        }

        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($article->featured_image && Storage::disk('public')->exists($article->featured_image)) {
                Storage::disk('public')->delete($article->featured_image);
            }
            $path = $request->file('featured_image')->store('blog/featured', 'public');
            $validated['featured_image'] = $path;
        }

        if ($validated['status'] === 'published' && $article->status !== 'published') {
            $validated['published_at'] = now();
        }

        $article->update($validated);

        // Handle Tags
        $tagIds = $validated['tags'] ?? [];
        
        if ($request->filled('new_tags')) {
            $newTagNames = explode(',', $request->new_tags);
            foreach ($newTagNames as $tagName) {
                $tagName = trim($tagName);
                if ($tagName) {
                    $tag = BlogTag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );
                    $tagIds[] = $tag->id;
                }
            }
        }

        $article->tags()->sync(array_unique($tagIds));

        return redirect()->route('admin.blog.articles.index')->with('success', 'Article updated successfully.');
    }

    public function destroy(Article $article)
    {
        if ($article->featured_image && Storage::disk('public')->exists($article->featured_image)) {
            Storage::disk('public')->delete($article->featured_image);
        }
        $article->tags()->detach();
        $article->delete();

        return redirect()->route('admin.blog.articles.index')->with('success', 'Article deleted successfully.');
    }
}
