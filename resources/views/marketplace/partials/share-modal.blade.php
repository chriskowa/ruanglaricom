<!-- Share Modal Component -->
<div id="mp-share-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 backdrop-blur-sm hidden" onclick="if(event.target===this) closeShareModal();">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-sm w-full mx-4 shadow-2xl relative">
        <button type="button" onclick="closeShareModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white text-xl font-bold w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center transition">&times;</button>
        
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-2xl bg-neon/10 border border-neon/30 flex items-center justify-center text-neon shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-5.368m0 5.368l5.832 2.916m-5.832-7.076l5.832-2.916m0 0a3 3 0 100-5.368 3 3 0 000 5.368zm0 7.076a3 3 0 100-5.368 3 3 0 000 5.368z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-bold text-white">Bagikan Produk</h3>
                <p id="mp-share-title" class="text-xs text-slate-400 truncate">Produk Marketplace</p>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-2.5 my-5">
            <!-- WhatsApp -->
            <a id="mp-share-wa" href="#" target="_blank" class="flex flex-col items-center gap-1.5 p-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20 transition group">
                <i class="fa-brands fa-whatsapp text-2xl group-hover:scale-110 transition-transform"></i>
                <span class="text-[10px] font-bold">WhatsApp</span>
            </a>
            <!-- Facebook -->
            <a id="mp-share-fb" href="#" target="_blank" class="flex flex-col items-center gap-1.5 p-3 rounded-2xl bg-blue-500/10 border border-blue-500/30 text-blue-400 hover:bg-blue-500/20 transition group">
                <i class="fa-brands fa-facebook text-2xl group-hover:scale-110 transition-transform"></i>
                <span class="text-[10px] font-bold">Facebook</span>
            </a>
            <!-- X / Twitter -->
            <a id="mp-share-tw" href="#" target="_blank" class="flex flex-col items-center gap-1.5 p-3 rounded-2xl bg-slate-800 border border-slate-700 text-slate-200 hover:bg-slate-700 transition group">
                <i class="fa-brands fa-x-twitter text-2xl group-hover:scale-110 transition-transform"></i>
                <span class="text-[10px] font-bold">Twitter</span>
            </a>
            <!-- Copy Link -->
            <button type="button" onclick="copyShareLink()" class="flex flex-col items-center gap-1.5 p-3 rounded-2xl bg-neon/10 border border-neon/30 text-neon hover:bg-neon/20 transition group">
                <i class="fa-solid fa-link text-2xl group-hover:scale-110 transition-transform"></i>
                <span class="text-[10px] font-bold">Salin</span>
            </button>
        </div>

        <div class="relative mt-2">
            <input id="mp-share-url-input" type="text" readonly class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 pr-16 text-xs text-slate-300 font-mono focus:outline-none select-all">
            <button type="button" onclick="copyShareLink()" class="absolute right-1.5 top-1/2 -translate-y-1/2 px-3 py-1 rounded-lg bg-neon text-dark text-[10px] font-black hover:bg-white transition-colors">
                <span id="mp-copy-btn-text">Salin</span>
            </button>
        </div>
    </div>
</div>

<script>
if (typeof window.openShareModal !== 'function') {
    window.openShareModal = function(title, url) {
        if (navigator.share && /mobile|android|iphone/i.test(navigator.userAgent)) {
            navigator.share({
                title: title,
                url: url
            }).catch(function() {});
            return;
        }
        
        var modal = document.getElementById('mp-share-modal');
        if (!modal) return;
        document.getElementById('mp-share-title').textContent = title;
        document.getElementById('mp-share-url-input').value = url;
        document.getElementById('mp-share-wa').href = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(title + ' ' + url);
        document.getElementById('mp-share-fb').href = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url);
        document.getElementById('mp-share-tw').href = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(title) + '&url=' + encodeURIComponent(url);
        modal.classList.remove('hidden');
    };
}

if (typeof window.closeShareModal !== 'function') {
    window.closeShareModal = function() {
        var modal = document.getElementById('mp-share-modal');
        if (modal) modal.classList.add('hidden');
    };
}

if (typeof window.copyShareLink !== 'function') {
    window.copyShareLink = function() {
        var input = document.getElementById('mp-share-url-input');
        if (!input) return;
        input.select();
        input.setSelectionRange(0, 99999);
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(input.value);
        } else {
            document.execCommand('copy');
        }
        var btnText = document.getElementById('mp-copy-btn-text');
        if (btnText) {
            btnText.textContent = 'Tersalin!';
            setTimeout(function() {
                btnText.textContent = 'Salin';
            }, 2000);
        }
    };
}
</script>
