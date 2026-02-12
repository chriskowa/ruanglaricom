<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomepageContentController extends Controller
{
    public function index()
    {
        $content = HomepageContent::firstOrNew();

        return view('admin.homepage.index', compact('content'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'headline' => 'nullable|string',
            'subheadline' => 'nullable|string',
            'hero_image' => 'nullable|image|max:2048',
            'floating_image' => 'nullable|image|max:2048',
        ]);

        $content = HomepageContent::firstOrNew();
        $content->headline = $request->headline;
        $content->subheadline = $request->subheadline;

        if ($request->hasFile('hero_image')) {
            // Delete old image if exists
            if ($content->hero_image && file_exists(public_path($content->hero_image))) {
                // It's tricky to delete from public_path if stored as 'storage/...' which maps to storage/app/public
                // Better to just store new one. Clean up is optimization.
            }

            $path = $request->file('hero_image')->store('homepage', 'public');
            $content->hero_image = 'storage/'.$path;
        }

        if ($request->hasFile('floating_image')) {
            $path = $request->file('floating_image')->store('homepage', 'public');
            $content->floating_image = 'storage/'.$path;
        }

        $content->save();

        return redirect()->back()->with('success', 'Homepage content updated successfully.');
    }
}
