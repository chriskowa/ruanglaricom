<?php

namespace App\Http\Controllers\Admin;

use App\Models\Page;
use App\Models\PageTemplate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::with('template')->latest()->paginate(10);
        $templates = PageTemplate::active()->get();
        
        return view('admin.pages.index', compact('pages', 'templates'));
    }

    public function create()
    {
        $templates = PageTemplate::active()->get();
        return view('admin.pages.create', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'template_id' => 'nullable|exists:page_templates,id',
            'status' => 'required|in:draft,published,archived',
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $page = Page::create($validated);

        // Handle template-specific data
        if ($request->template_id && $template = PageTemplate::find($request->template_id)) {
            $templateData = [];
            foreach ($template->sections ?? [] as $section) {
                $templateData[$section['key']] = $request->input("template_data.{$section['key']}");
            }
            $page->update(['template_data' => $templateData]);
        }

        return redirect()->route('admin.pages.edit', $page)
            ->with('success', 'Page created successfully');
    }

    public function edit(Page $page)
    {
        $templates = PageTemplate::active()->get();
        return view('admin.pages.edit', compact('page', 'templates'));
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'template_id' => 'nullable|exists:page_templates,id',
            'status' => 'required|in:draft,published,archived',
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $page->update($validated);

        // Update template-specific data
        if ($page->template_id && $template = $page->template) {
            $templateData = [];
            foreach ($template->sections ?? [] as $section) {
                $templateData[$section['key']] = $request->input("template_data.{$section['key']}");
            }
            $page->update(['template_data' => $templateData]);
        }

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page updated successfully');
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return redirect()->route('admin.pages.index')
            ->with('success', 'Page deleted successfully');
    }
}