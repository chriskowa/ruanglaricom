@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Import Articles')

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
                IMPORT ARTICLES
            </h1>
            <p class="text-slate-400 mt-1">Import posts from an existing WordPress site.</p>
        </div>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-8">
            <div class="mb-6 flex items-center gap-4 text-slate-300 bg-slate-800/50 p-4 rounded-xl border border-slate-700">
                <div class="w-12 h-12 flex-shrink-0 bg-blue-600/20 text-blue-400 rounded-full flex items-center justify-center">
                    <i class="fab fa-wordpress text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-white">WordPress REST API</h3>
                    <p class="text-sm">Enter your WordPress site URL (e.g., https://myblog.com). We will fetch the latest posts via the public API.</p>
                </div>
            </div>

            <form action="{{ route('admin.blog.import.store') }}" method="POST">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">WordPress URL</label>
                        <input type="url" name="wordpress_url" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-neon transition-colors" placeholder="https://your-wordpress-site.com">
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-lg shadow-neon/20 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Start Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
