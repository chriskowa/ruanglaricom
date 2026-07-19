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
            <button type="button" onclick="openImportModal()" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-600 text-white hover:bg-slate-700 transition-all font-bold text-sm flex items-center gap-2">
                <i class="fab fa-wordpress"></i> Import WP
            </button>
            <a href="{{ route('admin.blog.categories.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-600 text-white hover:bg-slate-700 transition-all font-bold text-sm">
                Manage Categories
            </a>
            <a href="{{ route('admin.blog.ai-topics.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-600 text-white hover:bg-slate-700 transition-all font-bold text-sm">
                Auto Blog Topics
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
                        <th class="px-6 py-4 text-center">Featured</th>
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
                                    <img src="{{ \Illuminate\Support\Str::startsWith($article->featured_image, ['http://', 'https://']) ? $article->featured_image : asset('storage/' . $article->featured_image) }}" class="w-10 h-10 rounded-lg object-cover bg-slate-700">
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
                        <td class="px-6 py-4 text-center">
                            <button type="button" onclick="toggleFeatured({{ $article->id }})" class="p-2 rounded-lg hover:bg-slate-700 transition-colors" title="Toggle Featured">
                                <svg id="icon-featured-{{ $article->id }}" class="w-5 h-5 {{ $article->is_featured ? 'text-yellow-400' : 'text-slate-500' }}" fill="{{ $article->is_featured ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </button>
                        </td>
                        <td class="px-6 py-4 text-slate-400 text-sm">
                            {{ $article->published_at ? $article->published_at->format('M d, Y H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ $article->canonical_url ?? url('/blog/' . $article->slug) }}" target="_blank" class="p-2 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-green-400 transition-colors" title="View Article">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
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
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
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
    <!-- Import WP Modal -->
    <div id="wp-import-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/80 backdrop-blur-sm p-4 transition-all duration-300">
        <div class="bg-slate-900/90 border border-slate-700/80 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl relative ring-1 ring-white/10">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                <h3 class="text-lg font-black text-white flex items-center gap-2">
                    <i class="fab fa-wordpress text-[#21759b]"></i>
                    IMPORT ARTIKEL WORDPRESS
                </h3>
                <button type="button" onclick="closeImportModal()" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form action="{{ route('admin.blog.import.store') }}" method="POST" onsubmit="submitImportForm(event)">
                @csrf
                <div class="p-6 space-y-6">
                    <div class="bg-blue-950/30 border border-blue-800/30 p-4 rounded-xl flex gap-3 text-slate-300 text-sm">
                        <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            Masukkan tautan JSON REST API posts WordPress untuk mengimpor post sebagai <strong>Draft</strong>.
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="wordpress_url" class="block text-xs font-bold uppercase tracking-wider text-slate-400">URL JSON WordPress</label>
                        <input type="url" id="wordpress_url" name="wordpress_url" required 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-200 text-sm focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all"
                               placeholder="https://runnersconnect.net/wp-json/wp/v2/posts"
                               value="https://runnersconnect.net/wp-json/wp/v2/posts">
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-slate-800 bg-slate-900/50 flex justify-end gap-3">
                    <button type="button" onclick="closeImportModal()" 
                            class="px-4 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white transition-all font-bold text-sm">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-import"
                            class="px-5 py-2.5 rounded-xl bg-neon text-dark hover:bg-neon/90 hover:scale-[1.02] transition-all font-bold text-sm flex items-center gap-2">
                        <span>Mulai Import</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleFeatured(id) {
    const icon = document.getElementById('icon-featured-' + id);
    if (!icon) return;

    const wasFeatured = icon.classList.contains('text-yellow-400');

    if (wasFeatured) {
        icon.classList.remove('text-yellow-400');
        icon.classList.add('text-slate-500');
        icon.setAttribute('fill', 'none');
    } else {
        icon.classList.remove('text-slate-500');
        icon.classList.add('text-yellow-400');
        icon.setAttribute('fill', 'currentColor');
    }

    fetch(`/admin/blog/articles/${id}/toggle-featured`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(r => r.ok ? r.json() : Promise.reject(r))
    .then(data => {
        if (!data || !data.success) {
            throw new Error('toggle failed');
        }
    })
    .catch(() => {
        if (wasFeatured) {
            icon.classList.remove('text-slate-500');
            icon.classList.add('text-yellow-400');
            icon.setAttribute('fill', 'currentColor');
        } else {
            icon.classList.remove('text-yellow-400');
            icon.classList.add('text-slate-500');
            icon.setAttribute('fill', 'none');
        }
        alert('Gagal mengubah status featured.');
    });
}

const importModal = document.getElementById('wp-import-modal');
const importInput = document.getElementById('wordpress_url');
const submitBtn = document.getElementById('btn-submit-import');

function openImportModal() {
    if (!importModal) return;
    importModal.classList.remove('hidden');
    importModal.classList.add('flex');
    if (importInput) {
        importInput.focus();
    }
}

function closeImportModal() {
    if (!importModal) return;
    importModal.classList.remove('flex');
    importModal.classList.add('hidden');
}

function submitImportForm(event) {
    if (!submitBtn) return;
    submitBtn.disabled = true;
    submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
    submitBtn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-dark" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Mengimpor...</span>
    `;
}

// Close modal on escape key or clicking outside
window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeImportModal();
    }
});

if (importModal) {
    importModal.addEventListener('click', (e) => {
        if (e.target === importModal) {
            closeImportModal();
        }
    });
}
</script>
@endpush
