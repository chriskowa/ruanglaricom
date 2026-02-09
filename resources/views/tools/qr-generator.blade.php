@extends('layouts.pacerhub')

@section('title', 'QR Code Generator - Ruang Lari Tools')

@section('content')
<div class="min-h-screen pt-24 pb-20 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    <div class="relative z-10 max-w-4xl mx-auto text-center mb-12">
        <h1 class="text-3xl md:text-5xl font-black text-white italic tracking-tighter mb-4">
            QR CODE <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">GENERATOR</span>
        </h1>
        <p class="text-slate-400 text-lg">
            Buat QR Code kustom untuk link, teks, atau event Anda dengan mudah.
        </p>
    </div>

    <!-- Generator Card -->
    <div class="max-w-4xl mx-auto relative z-10">
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 rounded-3xl p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- Controls -->
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Content / URL</label>
                    <input type="text" id="qrText" value="https://ruanglari.com" 
                        class="w-full bg-slate-900 border border-slate-600 rounded-xl px-4 py-3 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none transition-colors"
                        placeholder="Enter text or URL">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">Size</label>
                        <select id="qrSize" class="w-full bg-slate-900 border border-slate-600 rounded-xl px-4 py-3 text-white focus:border-purple-500 focus:outline-none">
                            <option value="200">Small (200px)</option>
                            <option value="300" selected>Medium (300px)</option>
                            <option value="500">Large (500px)</option>
                            <option value="1000">Extra Large (1000px)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">Error Correction</label>
                        <select id="qrLevel" class="w-full bg-slate-900 border border-slate-600 rounded-xl px-4 py-3 text-white focus:border-purple-500 focus:outline-none">
                            <option value="L">Low (7%)</option>
                            <option value="M">Medium (15%)</option>
                            <option value="Q">Quartile (25%)</option>
                            <option value="H" selected>High (30%)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Background Color</label>
                    <div class="flex gap-3">
                        <button onclick="setBg('#ffffff')" class="w-10 h-10 rounded-full bg-white border-2 border-transparent hover:scale-110 transition-transform focus:border-purple-500"></button>
                        <button onclick="setBg('#f8fafc')" class="w-10 h-10 rounded-full bg-slate-50 border-2 border-transparent hover:scale-110 transition-transform focus:border-purple-500"></button>
                        <button onclick="setBg('transparent')" class="w-10 h-10 rounded-full bg-slate-800 border-2 border-slate-600 flex items-center justify-center hover:scale-110 transition-transform focus:border-purple-500 group">
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                        <input type="color" id="customBg" value="#ffffff" class="w-10 h-10 rounded-full p-0 border-0 overflow-hidden cursor-pointer" onchange="setBg(this.value)">
                    </div>
                </div>

                <div class="pt-4">
                    <button onclick="generateQR()" class="w-full py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold shadow-lg shadow-purple-500/25 transition-all transform hover:-translate-y-0.5">
                        Generate QR Code
                    </button>
                </div>
            </div>

            <!-- Preview -->
            <div class="bg-slate-900/50 rounded-2xl p-6 flex flex-col items-center justify-center border border-slate-700">
                <div id="qrcode-container" class="bg-white p-4 rounded-xl mb-6 shadow-2xl">
                    <div id="qrcode"></div>
                </div>
                
                <button onclick="downloadQR()" class="flex items-center gap-2 px-6 py-2 rounded-full bg-slate-700 hover:bg-slate-600 text-white font-bold transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Download PNG
                </button>
            </div>
        </div>
    </div>

    <!-- Decorative Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[20%] left-[-10%] w-[40%] h-[40%] bg-purple-600/10 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-pink-600/10 rounded-full blur-[100px]"></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    let qrcode = null;
    let currentBg = "#ffffff";

    function setBg(color) {
        currentBg = color;
        if(color !== 'transparent' && color.startsWith('#')) {
            document.getElementById('customBg').value = color;
        }
        generateQR();
    }

    function generateQR() {
        const text = document.getElementById('qrText').value || 'https://ruanglari.com';
        const size = parseInt(document.getElementById('qrSize').value);
        const level = document.getElementById('qrLevel').value;
        
        const container = document.getElementById('qrcode');
        container.innerHTML = '';
        
        // Update container background
        const wrapper = document.getElementById('qrcode-container');
        if (currentBg === 'transparent') {
            wrapper.style.backgroundColor = 'transparent';
            wrapper.classList.remove('bg-white');
            wrapper.style.backgroundImage = 'linear-gradient(45deg, #ccc 25%, transparent 25%), linear-gradient(-45deg, #ccc 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #ccc 75%), linear-gradient(-45deg, transparent 75%, #ccc 75%)';
            wrapper.style.backgroundSize = '20px 20px';
            wrapper.style.backgroundPosition = '0 0, 0 10px, 10px -10px, -10px 0px';
        } else {
            wrapper.style.backgroundColor = currentBg;
            wrapper.style.backgroundImage = 'none';
        }

        try {
            qrcode = new QRCode(container, {
                text: text,
                width: size,
                height: size,
                colorDark : "#000000",
                colorLight : currentBg === 'transparent' ? "rgba(0,0,0,0)" : currentBg,
                correctLevel : QRCode.CorrectLevel[level]
            });
            
            // Adjust canvas style for preview
            setTimeout(() => {
                const canvas = container.querySelector('canvas');
                const img = container.querySelector('img');
                if(canvas) {
                    canvas.style.width = '100%';
                    canvas.style.height = 'auto';
                    canvas.style.maxWidth = '300px';
                }
                if(img) {
                    img.style.width = '100%';
                    img.style.height = 'auto';
                    img.style.maxWidth = '300px';
                }
            }, 50);

        } catch (e) {
            console.error(e);
        }
    }

    function downloadQR() {
        const container = document.getElementById('qrcode');
        const img = container.querySelector('img');
        
        if (img && img.src) {
            const link = document.createElement('a');
            link.download = 'qrcode-' + Date.now() + '.png';
            link.href = img.src;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            // Fallback for canvas if img not ready
            const canvas = container.querySelector('canvas');
            if(canvas) {
                const link = document.createElement('a');
                link.download = 'qrcode-' + Date.now() + '.png';
                link.href = canvas.toDataURL("image/png");
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
    }

    // Initialize
    window.addEventListener('load', generateQR);
    
    // Live update (optional, maybe debounce it)
    let timeout = null;
    document.getElementById('qrText').addEventListener('input', () => {
        clearTimeout(timeout);
        timeout = setTimeout(generateQR, 500);
    });
</script>
@endpush
