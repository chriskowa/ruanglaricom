<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function show($slug)
    {
        $article = Article::where('slug', $slug)->published()->firstOrFail();

        // Increment views count
        $article->increment('views_count');
        
        // Fetch related articles
        $relatedArticles = Article::where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->published()
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('blog.show', compact('article', 'relatedArticles'));
    }
}