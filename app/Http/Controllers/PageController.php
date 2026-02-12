<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)->where('status', 'published')->firstOrFail();

        if ($page->hardcoded) {
            // Check if view exists, otherwise default to generic
            if (view()->exists('pages.'.$page->hardcoded)) {
                return view('pages.'.$page->hardcoded, compact('page'));
            }
        }

        return view('pages.show', compact('page'));
    }
}
