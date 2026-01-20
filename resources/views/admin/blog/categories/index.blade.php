@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Blog Categories')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-end gap-4 relative z-10">
        <div>
            <a href="{{ route('admin.blog.articles.index') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-flex items-center gap-1 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Back to Articles
            </a>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                BLOG CATEGORIES
            </h1>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 h-fit">
            <h3 class="text-lg font-bold text-white mb-4">Add New Category</h3>
            <form action="{{ route('admin.blog.categories.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">Name</label>
                        <input type="text" name="name" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="Category Name">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">Slug</label>
                        <input type="text" name="slug" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="auto-generated">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">Description</label>
                        <textarea name="description" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors"></textarea>
                    </div>
                    
                    <div class="border-t border-slate-700 pt-4 mt-4">
                        <h4 class="text-xs font-bold text-slate-400 uppercase mb-2">SEO</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Meta Title</label>
                                <input type="text" name="meta_title" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-neon transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Meta Description</label>
                                <textarea name="meta_description" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-neon transition-colors"></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-lg shadow-neon/20">
                        Add Category
                    </button>
                </div>
            </form>
        </div>

        <!-- List -->
        <div class="lg:col-span-2 bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-700/50 bg-slate-800/30 text-xs uppercase tracking-wider text-slate-400 font-bold">
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Slug</th>
                            <th class="px-6 py-4">Count</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($categories as $category)
                        <tr class="hover:bg-slate-700/20 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-white">{{ $category->name }}</div>
                                <div class="text-xs text-slate-500">{{ Str::limit($category->description, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-400 font-mono text-xs">
                                {{ $category->slug }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-slate-700 text-slate-300 text-xs font-bold px-2 py-1 rounded-full">{{ $category->articles_count }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('admin.blog.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-red-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                No categories found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-700/50">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
