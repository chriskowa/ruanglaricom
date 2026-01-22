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
        
        return view('blog.show', compact('article'));
    }
}