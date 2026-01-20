<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('blog/content', 'public');
            return response()->json(['location' => asset('storage/' . $path)]);
        }
        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
