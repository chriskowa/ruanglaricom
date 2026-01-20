@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Blog Articles')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-end gap-4 relative z-10">
        <div>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                BLOG ARTICLES
            </h1>
            <p class="text-slate-400 mt-1">Manage your blog posts and content.</p>
        </div>
        
        <div class="flex gap-3">
            <a href="{{ route('admin.blog.import') }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-600 text-white hover:bg-slate-700 transition-all font-bold text-sm flex items-center gap-2">
                <i class="fab fa-wordpress"></i> Import WP
            </a>
            <a href="{{ route('admin.blog.categories.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-600 text-white hover:bg-slate-700 transition-all font-bold text-sm">
                Manage Categories
            </a>
            <a href="{{ route('admin.blog.articles.create') }}" class="px-4 py-2 rounded-xl bg-neon text-dark hover:bg-neon/90 transition-all font-bold text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                New Article
            </a>
        </div>
    </div>

    <!-- Articles Table -->
    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden relative z-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-700/50 bg-slate-800/30 text-xs uppercase tracking-wider text-slate-400 font-bold">
                        <th class="px-6 py-4">Title</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Published</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($articles as $article)
                    <tr class="hover:bg-slate-700/20 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($article->featured_image)
                                    <img src="{{ asset('storage/' . $article->featured_image) }}" class="w-10 h-10 rounded-lg object-cover bg-slate-700">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-slate-700 flex items-center justify-center text-slate-500">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-bold text-white group-hover:text-neon transition-colors">{{ $article->title }}</div>
                                    <div class="text-xs text-slate-500">By {{ $article->user->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-300">
                            {{ $article->category ? $article->category->name : 'Uncategorized' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $article->status === 'published' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 
                                  ($article->status === 'draft' ? 'bg-slate-500/10 text-slate-400 border border-slate-500/20' : 
                                  'bg-red-500/10 text-red-400 border border-red-500/20') }}">
                                {{ ucfirst($article->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-400 text-sm">
                            {{ $article->published_at ? $article->published_at->format('M d, Y H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.blog.articles.edit', $article) }}" class="p-2 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-blue-400 transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </a>
                                <form action="{{ route('admin.blog.articles.destroy', $article) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-red-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 mb-3 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
                                <p class="text-lg font-medium">No articles found</p>
                                <p class="text-sm">Start writing your first blog post!</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-700/50">
            {{ $articles->links() }}
        </div>
    </div>
</div>
@endsection
