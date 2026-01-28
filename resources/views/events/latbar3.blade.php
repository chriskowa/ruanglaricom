<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <script>
        // Nominatim CORS Proxy Interceptor
        (function() {
            var originalFetch = window.fetch;
            window.fetch = function(url, options) {
                if (typeof url === 'string' && url.includes('nominatim.openstreetmap.org')) {
                    var proxyUrl = '/image-proxy?url=' + encodeURIComponent(url);
                    return originalFetch(proxyUrl, options);
                }
                return originalFetch(url, options);
            };
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $event->name }} - Registrasi Latbar</title>
    <meta name="description" content="{{ $event->location_name ? $event->name.' di '.$event->location_name.' | Latihan terstruktur, slot terbatas.' : $event->name.' | Latihan terstruktur, slot terbatas.' }}">
    <meta name="keywords" content="ruang lari, latbar, komunitas lari, event lari, {{ strtolower($event->name) }}, {{ strtolower($event->location_name) }}">
    <link rel="canonical" href="{{ route('events.show', $event->slug) }}">
    <meta name="theme-color" content="#0a0a0a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $event->name }} - Registrasi Latbar">
    <meta property="og:description" content="{{ $event->location_name ? $event->name.' di '.$event->location_name.' | Latihan terstruktur, slot terbatas.' : $event->name.' | Latihan terstruktur, slot terbatas.' }}">
    <meta property="og:url" content="{{ route('events.show', $event->slug) }}">
    <meta property="og:image" content="{{ $event->getHeroImageUrl() ?? asset('images/ruanglari_green.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $event->name }} - Registrasi Latbar">
    <meta name="twitter:description" content="{{ $event->location_name ? $event->name.' di '.$event->location_name.' | Latihan terstruktur, slot terbatas.' : $event->name.' | Latihan terstruktur, slot terbatas.' }}">
    <meta name="twitter:image" content="{{ $event->getHeroImageUrl() ?? asset('images/ruanglari_green.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/green/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/green/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/green/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('images/green/site.webmanifest') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://app.midtrans.com/snap/snap.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Roboto', 'sans-serif'],
                        display: ['Bebas Neue', 'sans-serif'], // Font Headline Sporty
                    },
                    colors: {
                        sport: {
                            volt: '#CCFF00',   /* Hijau Stabilo / Nike Volt */
                            blue: '#0044FF',   /* Electric Blue */
                            dark: '#0a0a0a',
                            surface: '#121212'
                        }
                    },
                    backgroundImage: {
                        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                    }
                }
            }
        }
    </script>
    <style>
        :root{
            --dark:#0a0a0a;
            --card:#111315;
            --input:#0f1112;
            --neon:#ccff00;
            --accent:#7dd3fc;
        }
        [v-cloak]{display:none;}
        .glass-dark{background:rgba(18,18,18,0.8);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.08)}
        body{background:var(--dark);color:#fff;font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, \"Apple Color Emoji\", \"Segoe UI Emoji\"}
        .font-display{font-weight:800;letter-spacing:0.02em}
        .text-sport-volt{color:var(--neon)}
        .bg-sport-volt{background-color:var(--neon)}
        
        /* Modern Inputs */
        .form-input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .form-input {
            width: 100%;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
            padding: 1rem 1rem;
            border-radius: 0.75rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.95rem;
            outline: none;
        }
        .form-input:focus {
            background: rgba(255,255,255,0.05);
            border-color: var(--neon);
            box-shadow: 0 0 0 4px rgba(204, 255, 0, 0.1);
            transform: translateY(-2px);
        }
        .form-label {
            position: absolute;
            left: 1rem;
            top: 1rem;
            color: #9ca3af;
            font-size: 0.95rem;
            pointer-events: none;
            transition: all 0.3s ease;
        }
        .form-input:focus ~ .form-label,
        .form-input:not(:placeholder-shown) ~ .form-label {
            transform: translateY(-1.4rem) translateX(-0.2rem) scale(0.85);
            color: var(--neon);
            font-weight: 600;
            background: var(--dark);
            padding: 0 0.4rem;
        }

        /* Payment Method Cards */
        .payment-card {
            position: relative;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.03);
            border-radius: 1rem;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .payment-card:hover {
            border-color: rgba(204, 255, 0, 0.5);
            background: rgba(255,255,255,0.05);
        }
        .payment-card.active {
            border-color: var(--neon);
            background: rgba(204, 255, 0, 0.05);
            box-shadow: 0 4px 20px rgba(204, 255, 0, 0.1);
        }
        .payment-card .check-circle {
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 50%;
            border: 2px solid #6b7280;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .payment-card.active .check-circle {
            border-color: var(--neon);
            background: var(--neon);
        }
        .payment-card.active .check-circle::after {
            content: '';
            width: 0.5rem;
            height: 0.5rem;
            background: #000;
            border-radius: 50%;
        }

        /* Animations */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.3); }
    </style>
    <style>*, ::before, ::after{--tw-border-spacing-x:0;--tw-border-spacing-y:0;--tw-translate-x:0;--tw-translate-y:0;--tw-rotate:0;--tw-skew-x:0;--tw-skew-y:0;--tw-scale-x:1;--tw-scale-y:1;--tw-pan-x: ;--tw-pan-y: ;--tw-pinch-zoom: ;--tw-scroll-snap-strictness:proximity;--tw-gradient-from-position: ;--tw-gradient-via-position: ;--tw-gradient-to-position: ;--tw-ordinal: ;--tw-slashed-zero: ;--tw-numeric-figure: ;--tw-numeric-spacing: ;--tw-numeric-fraction: ;--tw-ring-inset: ;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-color:rgb(59 130 246 / 0.5);--tw-ring-offset-shadow:0 0 #0000;--tw-ring-shadow:0 0 #0000;--tw-shadow:0 0 #0000;--tw-shadow-colored:0 0 #0000;--tw-blur: ;--tw-brightness: ;--tw-contrast: ;--tw-grayscale: ;--tw-hue-rotate: ;--tw-invert: ;--tw-saturate: ;--tw-sepia: ;--tw-drop-shadow: ;--tw-backdrop-blur: ;--tw-backdrop-brightness: ;--tw-backdrop-contrast: ;--tw-backdrop-grayscale: ;--tw-backdrop-hue-rotate: ;--tw-backdrop-invert: ;--tw-backdrop-opacity: ;--tw-backdrop-saturate: ;--tw-backdrop-sepia: ;--tw-contain-size: ;--tw-contain-layout: ;--tw-contain-paint: ;--tw-contain-style: }::backdrop{--tw-border-spacing-x:0;--tw-border-spacing-y:0;--tw-translate-x:0;--tw-translate-y:0;--tw-rotate:0;--tw-skew-x:0;--tw-skew-y:0;--tw-scale-x:1;--tw-scale-y:1;--tw-pan-x: ;--tw-pan-y: ;--tw-pinch-zoom: ;--tw-scroll-snap-strictness:proximity;--tw-gradient-from-position: ;--tw-gradient-via-position: ;--tw-gradient-to-position: ;--tw-ordinal: ;--tw-slashed-zero: ;--tw-numeric-figure: ;--tw-numeric-spacing: ;--tw-numeric-fraction: ;--tw-ring-inset: ;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-color:rgb(59 130 246 / 0.5);--tw-ring-offset-shadow:0 0 #0000;--tw-ring-shadow:0 0 #0000;--tw-shadow:0 0 #0000;--tw-shadow-colored:0 0 #0000;--tw-blur: ;--tw-brightness: ;--tw-contrast: ;--tw-grayscale: ;--tw-hue-rotate: ;--tw-invert: ;--tw-saturate: ;--tw-sepia: ;--tw-drop-shadow: ;--tw-backdrop-blur: ;--tw-backdrop-brightness: ;--tw-backdrop-contrast: ;--tw-backdrop-grayscale: ;--tw-backdrop-hue-rotate: ;--tw-backdrop-invert: ;--tw-backdrop-opacity: ;--tw-backdrop-saturate: ;--tw-backdrop-sepia: ;--tw-contain-size: ;--tw-contain-layout: ;--tw-contain-paint: ;--tw-contain-style: }/* ! tailwindcss v3.4.17 | MIT License | https://tailwindcss.com */*,::after,::before{box-sizing:border-box;border-width:0;border-style:solid;border-color:#e5e7eb}::after,::before{--tw-content:''}:host,html{line-height:1.5;-webkit-text-size-adjust:100%;-moz-tab-size:4;tab-size:4;font-family:Inter, sans-serif;font-feature-settings:normal;font-variation-settings:normal;-webkit-tap-highlight-color:transparent}body{margin:0;line-height:inherit}hr{height:0;color:inherit;border-top-width:1px}abbr:where([title]){-webkit-text-decoration:underline dotted;text-decoration:underline dotted}h1,h2,h3,h4,h5,h6{font-size:inherit;font-weight:inherit}a{color:inherit;text-decoration:inherit}b,strong{font-weight:bolder}code,kbd,pre,samp{font-family:JetBrains Mono, monospace;font-feature-settings:normal;font-variation-settings:normal;font-size:1em}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sub{bottom:-.25em}sup{top:-.5em}table{text-indent:0;border-color:inherit;border-collapse:collapse}button,input,optgroup,select,textarea{font-family:inherit;font-feature-settings:inherit;font-variation-settings:inherit;font-size:100%;font-weight:inherit;line-height:inherit;letter-spacing:inherit;color:inherit;margin:0;padding:0}button,select{text-transform:none}button,input:where([type=button]),input:where([type=reset]),input:where([type=submit]){-webkit-appearance:button;background-color:transparent;background-image:none}:-moz-focusring{outline:auto}:-moz-ui-invalid{box-shadow:none}progress{vertical-align:baseline}::-webkit-inner-spin-button,::-webkit-outer-spin-button{height:auto}[type=search]{-webkit-appearance:textfield;outline-offset:-2px}::-webkit-search-decoration{-webkit-appearance:none}::-webkit-file-upload-button{-webkit-appearance:button;font:inherit}summary{display:list-item}blockquote,dd,dl,figure,h1,h2,h3,h4,h5,h6,hr,p,pre{margin:0}fieldset{margin:0;padding:0}legend{padding:0}menu,ol,ul{list-style:none;margin:0;padding:0}dialog{padding:0}textarea{resize:vertical}input::placeholder,textarea::placeholder{opacity:1;color:#9ca3af}[role=button],button{cursor:pointer}:disabled{cursor:default}audio,canvas,embed,iframe,img,object,svg,video{display:block;vertical-align:middle}img,video{max-width:100%;height:auto}[hidden]:where(:not([hidden=until-found])){display:none}.pointer-events-none{pointer-events:none}.fixed{position:fixed}.absolute{position:absolute}.relative{position:relative}.inset-0{inset:0px}.-right-10{right:-2.5rem}.-right-6{right:-1.5rem}.-top-10{top:-2.5rem}.bottom-0{bottom:0px}.bottom-4{bottom:1rem}.bottom-8{bottom:2rem}.bottom-\[-10\%\]{bottom:-10%}.left-0{left:0px}.left-1\/2{left:50%}.left-4{left:1rem}.left-8{left:2rem}.left-\[-10\%\]{left:-10%}.right-0{right:0px}.right-\[-10\%\]{right:-10%}.top-0{top:0px}.top-1\/2{top:50%}.top-20{top:5rem}.top-4{top:1rem}.top-\[-10\%\]{top:-10%}.-right-1{right:-0.25rem}.-top-1{top:-0.25rem}.bottom-20{bottom:5rem}.bottom-5{bottom:1.25rem}.right-6{right:1.5rem}.z-0{z-index:0}.z-10{z-index:10}.z-20{z-index:20}.z-30{z-index:30}.z-40{z-index:40}.z-50{z-index:50}.order-1{order:1}.order-2{order:2}.col-span-1{grid-column:span 1 / span 1}.col-span-3{grid-column:span 3 / span 3}.mx-auto{margin-left:auto;margin-right:auto}.mb-1{margin-bottom:0.25rem}.mb-10{margin-bottom:2.5rem}.mb-12{margin-bottom:3rem}.mb-16{margin-bottom:4rem}.mb-2{margin-bottom:0.5rem}.mb-20{margin-bottom:5rem}.mb-3{margin-bottom:0.75rem}.mb-4{margin-bottom:1rem}.mb-6{margin-bottom:1.5rem}.mb-8{margin-bottom:2rem}.mb-auto{margin-bottom:auto}.ml-1{margin-left:0.25rem}.mt-1{margin-top:0.25rem}.mt-12{margin-top:3rem}.mt-2{margin-top:0.5rem}.mt-auto{margin-top:auto}.line-clamp-1{overflow:hidden;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:1}.line-clamp-2{overflow:hidden;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2}.block{display:block}.inline-block{display:inline-block}.flex{display:flex}.inline-flex{display:inline-flex}.grid{display:grid}.hidden{display:none}.aspect-video{aspect-ratio:16 / 9}.h-10{height:2.5rem}.h-12{height:3rem}.h-2{height:0.5rem}.h-20{height:5rem}.h-24{height:6rem}.h-4{height:1rem}.h-5{height:1.25rem}.h-6{height:1.5rem}.h-64{height:16rem}.h-8{height:2rem}.h-96{height:24rem}.h-\[500px\]{height:500px}.h-\[600px\]{height:600px}.h-full{height:100%}.h-px{height:1px}.h-14{height:3.5rem}.max-h-\[80vh\]{max-height:80vh}.max-h-64{max-height:16rem}.max-h-80{max-height:20rem}.min-h-screen{min-height:100vh}.w-10{width:2.5rem}.w-12{width:3rem}.w-2{width:0.5rem}.w-24{width:6rem}.w-4{width:1rem}.w-48{width:12rem}.w-5{width:1.25rem}.w-6{width:1.5rem}.w-64{width:16rem}.w-96{width:24rem}.w-\[500px\]{width:500px}.w-\[600px\]{width:600px}.w-auto{width:auto}.w-full{width:100%}.w-14{width:3.5rem}.w-8{width:2rem}.w-80{width:20rem}.max-w-2xl{max-width:42rem}.max-w-5xl{max-width:64rem}.max-w-7xl{max-width:80rem}.max-w-lg{max-width:32rem}.flex-1{flex:1 1 0%}.flex-shrink-0{flex-shrink:0}.flex-grow{flex-grow:1}.origin-top-left{transform-origin:top left}.-translate-x-1\/2{--tw-translate-x:-50%;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.-translate-y-1\/2{--tw-translate-y:-50%;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.translate-x-1\/2{--tw-translate-x:50%;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.translate-y-1\/2{--tw-translate-y:50%;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.rotate-12{--tw-rotate:12deg;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.rotate-3{--tw-rotate:3deg;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.-rotate-45{--tw-rotate:-45deg;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.-skew-y-2{--tw-skew-y:-2deg;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.scale-105{--tw-scale-x:1.05;--tw-scale-y:1.05;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.scale-110{--tw-scale-x:1.1;--tw-scale-y:1.1;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.transform{transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}@keyframes ping{75%, 100%{transform:scale(2);opacity:0}}.animate-ping{animation:ping 1s cubic-bezier(0, 0, 0.2, 1) infinite}@keyframes pulse{50%{opacity:.5}}.animate-pulse{animation:pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite}@keyframes pulse{50%{opacity:.5}}.animate-pulse-slow{animation:pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite}@keyframes spin{to{transform:rotate(360deg)}}.animate-spin{animation:spin 1s linear infinite}.select-none{-webkit-user-select:none;user-select:none}.grid-cols-1{grid-template-columns:repeat(1, minmax(0, 1fr))}.grid-cols-2{grid-template-columns:repeat(2, minmax(0, 1fr))}.flex-col{flex-direction:column}.flex-wrap{flex-wrap:wrap}.items-end{align-items:flex-end}.items-center{align-items:center}.items-baseline{align-items:baseline}.justify-center{justify-content:center}.justify-between{justify-content:space-between}.gap-1{gap:0.25rem}.gap-12{gap:3rem}.gap-2{gap:0.5rem}.gap-3{gap:0.75rem}.gap-4{gap:1rem}.gap-6{gap:1.5rem}.gap-8{gap:2rem}.-space-x-4 > :not([hidden]) ~ :not([hidden]){--tw-space-x-reverse:0;margin-right:calc(-1rem * var(--tw-space-x-reverse));margin-left:calc(-1rem * calc(1 - var(--tw-space-x-reverse)))}.space-y-1 > :not([hidden]) ~ :not([hidden]){--tw-space-y-reverse:0;margin-top:calc(0.25rem * calc(1 - var(--tw-space-y-reverse)));margin-bottom:calc(0.25rem * var(--tw-space-y-reverse))}.space-y-3 > :not([hidden]) ~ :not([hidden]){--tw-space-y-reverse:0;margin-top:calc(0.75rem * calc(1 - var(--tw-space-y-reverse)));margin-bottom:calc(0.75rem * var(--tw-space-y-reverse))}.space-y-4 > :not([hidden]) ~ :not([hidden]){--tw-space-y-reverse:0;margin-top:calc(1rem * calc(1 - var(--tw-space-y-reverse)));margin-bottom:calc(1rem * var(--tw-space-y-reverse))}.divide-y > :not([hidden]) ~ :not([hidden]){--tw-divide-y-reverse:0;border-top-width:calc(1px * calc(1 - var(--tw-divide-y-reverse)));border-bottom-width:calc(1px * var(--tw-divide-y-reverse))}.divide-slate-800 > :not([hidden]) ~ :not([hidden]){--tw-divide-opacity:1;border-color:rgb(30 41 59 / var(--tw-divide-opacity, 1))}.overflow-hidden{overflow:hidden}.overflow-y-auto{overflow-y:auto}.overflow-x-hidden{overflow-x:hidden}.rounded{border-radius:0.25rem}.rounded-2xl{border-radius:1rem}.rounded-3xl{border-radius:1.5rem}.rounded-\[2\.5rem\]{border-radius:2.5rem}.rounded-\[3rem\]{border-radius:3rem}.rounded-full{border-radius:9999px}.rounded-lg{border-radius:0.5rem}.rounded-xl{border-radius:0.75rem}.rounded-b-2xl{border-bottom-right-radius:1rem;border-bottom-left-radius:1rem}.border{border-width:1px}.border-4{border-width:4px}.border-8{border-width:8px}.border-y{border-top-width:1px;border-bottom-width:1px}.border-b{border-bottom-width:1px}.border-l{border-left-width:1px}.border-t{border-top-width:1px}.border-r{border-right-width:1px}.border-dark{--tw-border-opacity:1;border-color:rgb(15 23 42 / var(--tw-border-opacity, 1))}.border-neon{--tw-border-opacity:1;border-color:rgb(204 255 0 / var(--tw-border-opacity, 1))}.border-neon\/20{border-color:rgb(204 255 0 / 0.2)}.border-slate-600{--tw-border-opacity:1;border-color:rgb(71 85 105 / var(--tw-border-opacity, 1))}.border-slate-700{--tw-border-opacity:1;border-color:rgb(51 65 85 / var(--tw-border-opacity, 1))}.border-slate-800{--tw-border-opacity:1;border-color:rgb(30 41 59 / var(--tw-border-opacity, 1))}.border-slate-800\/50{border-color:rgb(30 41 59 / 0.5)}.border-slate-900{--tw-border-opacity:1;border-color:rgb(15 23 42 / var(--tw-border-opacity, 1))}.border-transparent{border-color:transparent}.border-white\/10{border-color:rgb(255 255 255 / 0.1)}.border-white\/50{border-color:rgb(255 255 255 / 0.5)}.border-t-neon{--tw-border-opacity:1;border-top-color:rgb(204 255 0 / var(--tw-border-opacity, 1))}.bg-dark{--tw-bg-opacity:1;background-color:rgb(15 23 42 / var(--tw-bg-opacity, 1))}.bg-\[\#fc4c02\]{--tw-bg-opacity:1;background-color:rgb(252 76 2 / var(--tw-bg-opacity, 1))}.bg-black\/10{background-color:rgb(0 0 0 / 0.1)}.bg-blue-500{--tw-bg-opacity:1;background-color:rgb(59 130 246 / var(--tw-bg-opacity, 1))}.bg-blue-500\/20{background-color:rgb(59 130 246 / 0.2)}.bg-blue-600\/10{background-color:rgb(37 99 235 / 0.1)}.bg-blue-600\/5{background-color:rgb(37 99 235 / 0.05)}.bg-card{--tw-bg-opacity:1;background-color:rgb(30 41 59 / var(--tw-bg-opacity, 1))}.bg-dark\/80{background-color:rgb(15 23 42 / 0.8)}.bg-dark\/90{background-color:rgb(15 23 42 / 0.9)}.bg-neon{--tw-bg-opacity:1;background-color:rgb(204 255 0 / var(--tw-bg-opacity, 1))}.bg-neon\/10{background-color:rgb(204 255 0 / 0.1)}.bg-neon\/20{background-color:rgb(204 255 0 / 0.2)}.bg-neon\/5{background-color:rgb(204 255 0 / 0.05)}.bg-purple-500\/20{background-color:rgb(168 85 247 / 0.2)}.bg-slate-800{--tw-bg-opacity:1;background-color:rgb(30 41 59 / var(--tw-bg-opacity, 1))}.bg-slate-800\/50{background-color:rgb(30 41 59 / 0.5)}.bg-slate-900{--tw-bg-opacity:1;background-color:rgb(15 23 42 / var(--tw-bg-opacity, 1))}.bg-slate-900\/30{background-color:rgb(15 23 42 / 0.3)}.bg-slate-900\/50{background-color:rgb(15 23 42 / 0.5)}.bg-slate-900\/60{background-color:rgb(15 23 42 / 0.6)}.bg-slate-900\/95{background-color:rgb(15 23 42 / 0.95)}.bg-slate-950{--tw-bg-opacity:1;background-color:rgb(2 6 23 / var(--tw-bg-opacity, 1))}.bg-white{--tw-bg-opacity:1;background-color:rgb(255 255 255 / var(--tw-bg-opacity, 1))}.bg-white\/20{background-color:rgb(255 255 255 / 0.2)}.bg-red-500{--tw-bg-opacity:1;background-color:rgb(239 68 68 / var(--tw-bg-opacity, 1))}.bg-slate-700{--tw-bg-opacity:1;background-color:rgb(51 65 85 / var(--tw-bg-opacity, 1))}.bg-black\/20{background-color:rgb(0 0 0 / 0.2)}.bg-\[radial-gradient\(ellipse_at_top\2c _var\(--tw-gradient-stops\)\)\]{background-image:radial-gradient(ellipse at top, var(--tw-gradient-stops))}.bg-gradient-to-br{background-image:linear-gradient(to bottom right, var(--tw-gradient-stops))}.bg-gradient-to-r{background-image:linear-gradient(to right, var(--tw-gradient-stops))}.bg-gradient-to-t{background-image:linear-gradient(to top, var(--tw-gradient-stops))}.from-dark{--tw-gradient-from:#0f172a var(--tw-gradient-from-position);--tw-gradient-to:rgb(15 23 42 / 0) var(--tw-gradient-to-position);--tw-gradient-stops:var(--tw-gradient-from), var(--tw-gradient-to)}.from-dark\/80{--tw-gradient-from:rgb(15 23 42 / 0.8) var(--tw-gradient-from-position);--tw-gradient-to:rgb(15 23 42 / 0) var(--tw-gradient-to-position);--tw-gradient-stops:var(--tw-gradient-from), var(--tw-gradient-to)}.from-neon{--tw-gradient-from:#ccff00 var(--tw-gradient-from-position);--tw-gradient-to:rgb(204 255 0 / 0) var(--tw-gradient-to-position);--tw-gradient-stops:var(--tw-gradient-from), var(--tw-gradient-to)}.from-slate-800{--tw-gradient-from:#1e293b var(--tw-gradient-from-position);--tw-gradient-to:rgb(30 41 59 / 0) var(--tw-gradient-to-position);--tw-gradient-stops:var(--tw-gradient-from), var(--tw-gradient-to)}.from-transparent{--tw-gradient-from:transparent var(--tw-gradient-from-position);--tw-gradient-to:rgb(0 0 0 / 0) var(--tw-gradient-to-position);--tw-gradient-stops:var(--tw-gradient-from), var(--tw-gradient-to)}.from-white{--tw-gradient-from:#fff var(--tw-gradient-from-position);--tw-gradient-to:rgb(255 255 255 / 0) var(--tw-gradient-to-position);--tw-gradient-stops:var(--tw-gradient-from), var(--tw-gradient-to)}.from-neon\/5{--tw-gradient-from:rgb(204 255 0 / 0.05) var(--tw-gradient-from-position);--tw-gradient-to:rgb(204 255 0 / 0) var(--tw-gradient-to-position);--tw-gradient-stops:var(--tw-gradient-from), var(--tw-gradient-to)}.via-dark{--tw-gradient-to:rgb(15 23 42 / 0)  var(--tw-gradient-to-position);--tw-gradient-stops:var(--tw-gradient-from), #0f172a var(--tw-gradient-via-position), var(--tw-gradient-to)}.via-green-400{--tw-gradient-to:rgb(74 222 128 / 0)  var(--tw-gradient-to-position);--tw-gradient-stops:var(--tw-gradient-from), #4ade80 var(--tw-gradient-via-position), var(--tw-gradient-to)}.via-lime-500{--tw-gradient-to:rgb(132 204 22 / 0)  var(--tw-gradient-to-position);--tw-gradient-stops:var(--tw-gradient-from), #84cc16 var(--tw-gradient-via-position), var(--tw-gradient-to)}.via-neon\/50{--tw-gradient-to:rgb(204 255 0 / 0)  var(--tw-gradient-to-position);--tw-gradient-stops:var(--tw-gradient-from), rgb(204 255 0 / 0.5) var(--tw-gradient-via-position), var(--tw-gradient-to)}.via-transparent{--tw-gradient-to:rgb(0 0 0 / 0)  var(--tw-gradient-to-position);--tw-gradient-stops:var(--tw-gradient-from), transparent var(--tw-gradient-via-position), var(--tw-gradient-to)}.to-black{--tw-gradient-to:#000 var(--tw-gradient-to-position)}.to-emerald-500{--tw-gradient-to:#10b981 var(--tw-gradient-to-position)}.to-emerald-600{--tw-gradient-to:#059669 var(--tw-gradient-to-position)}.to-green-400{--tw-gradient-to:#4ade80 var(--tw-gradient-to-position)}.to-slate-500{--tw-gradient-to:#64748b var(--tw-gradient-to-position)}.to-transparent{--tw-gradient-to:transparent var(--tw-gradient-to-position)}.bg-clip-text{-webkit-background-clip:text;background-clip:text}.object-cover{object-fit:cover}.object-top{object-position:top}.p-1{padding:0.25rem}.p-2{padding:0.5rem}.p-3{padding:0.75rem}.p-4{padding:1rem}.p-5{padding:1.25rem}.p-6{padding:1.5rem}.p-8{padding:2rem}.p-0{padding:0px}.px-3{padding-left:0.75rem;padding-right:0.75rem}.px-4{padding-left:1rem;padding-right:1rem}.px-8{padding-left:2rem;padding-right:2rem}.py-1{padding-top:0.25rem;padding-bottom:0.25rem}.py-1\.5{padding-top:0.375rem;padding-bottom:0.375rem}.py-10{padding-top:2.5rem;padding-bottom:2.5rem}.py-2{padding-top:0.5rem;padding-bottom:0.5rem}.py-20{padding-top:5rem;padding-bottom:5rem}.py-24{padding-top:6rem;padding-bottom:6rem}.py-3{padding-top:0.75rem;padding-bottom:0.75rem}.py-32{padding-top:8rem;padding-bottom:8rem}.py-4{padding-top:1rem;padding-bottom:1rem}.px-2{padding-left:0.5rem;padding-right:0.5rem}.pb-1{padding-bottom:0.25rem}.pb-10{padding-bottom:2.5rem}.pl-1{padding-left:0.25rem}.pl-2{padding-left:0.5rem}.pl-4{padding-left:1rem}.pr-2{padding-right:0.5rem}.pr-8{padding-right:2rem}.pt-16{padding-top:4rem}.pt-2{padding-top:0.5rem}.pt-20{padding-top:5rem}.pt-8{padding-top:2rem}.pt-4{padding-top:1rem}.text-left{text-align:left}.text-center{text-align:center}.text-right{text-align:right}.font-sans{font-family:Inter, sans-serif}.font-mono{font-family:JetBrains Mono, monospace}.text-2xl{font-size:1.5rem;line-height:2rem}.text-3xl{font-size:1.875rem;line-height:2.25rem}.text-4xl{font-size:2.25rem;line-height:2.5rem}.text-5xl{font-size:3rem;line-height:1}.text-\[10px\]{font-size:10px}.text-\[200px\]{font-size:200px}.text-lg{font-size:1.125rem;line-height:1.75rem}.text-sm{font-size:0.875rem;line-height:1.25rem}.text-xl{font-size:1.25rem;line-height:1.75rem}.text-xs{font-size:0.75rem;line-height:1rem}.font-black{font-weight:900}.font-bold{font-weight:700}.font-light{font-weight:300}.font-medium{font-weight:500}.font-normal{font-weight:400}.uppercase{text-transform:uppercase}.italic{font-style:italic}.leading-none{line-height:1}.leading-relaxed{line-height:1.625}.leading-tight{line-height:1.25}.tracking-\[0\.2em\]{letter-spacing:0.2em}.tracking-tight{letter-spacing:-0.025em}.tracking-tighter{letter-spacing:-0.05em}.tracking-wider{letter-spacing:0.05em}.tracking-widest{letter-spacing:0.1em}.text-white{--tw-text-opacity:1;color:rgb(255 255 255 / var(--tw-text-opacity, 1))}.text-\[\#fc4c02\]{--tw-text-opacity:1;color:rgb(252 76 2 / var(--tw-text-opacity, 1))}.text-blue-400{--tw-text-opacity:1;color:rgb(96 165 250 / var(--tw-text-opacity, 1))}.text-dark{--tw-text-opacity:1;color:rgb(15 23 42 / var(--tw-text-opacity, 1))}.text-dark\/80{color:rgb(15 23 42 / 0.8)}.text-green-600{--tw-text-opacity:1;color:rgb(22 163 74 / var(--tw-text-opacity, 1))}.text-neon{--tw-text-opacity:1;color:rgb(204 255 0 / var(--tw-text-opacity, 1))}.text-primary{--tw-text-opacity:1;color:rgb(204 255 0 / var(--tw-text-opacity, 1))}.text-slate-200{--tw-text-opacity:1;color:rgb(226 232 240 / var(--tw-text-opacity, 1))}.text-slate-300{--tw-text-opacity:1;color:rgb(203 213 225 / var(--tw-text-opacity, 1))}.text-slate-400{--tw-text-opacity:1;color:rgb(148 163 184 / var(--tw-text-opacity, 1))}.text-slate-500{--tw-text-opacity:1;color:rgb(100 116 139 / var(--tw-text-opacity, 1))}.text-slate-600{--tw-text-opacity:1;color:rgb(71 85 105 / var(--tw-text-opacity, 1))}.text-slate-700{--tw-text-opacity:1;color:rgb(51 65 85 / var(--tw-text-opacity, 1))}.text-slate-800\/30{color:rgb(30 41 59 / 0.3)}.text-slate-900{--tw-text-opacity:1;color:rgb(15 23 42 / var(--tw-text-opacity, 1))}.text-transparent{color:transparent}.text-yellow-400{--tw-text-opacity:1;color:rgb(250 204 21 / var(--tw-text-opacity, 1))}.text-green-400{--tw-text-opacity:1;color:rgb(74 222 128 / var(--tw-text-opacity, 1))}.antialiased{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.placeholder-slate-500::placeholder{--tw-placeholder-opacity:1;color:rgb(100 116 139 / var(--tw-placeholder-opacity, 1))}.placeholder-slate-600::placeholder{--tw-placeholder-opacity:1;color:rgb(71 85 105 / var(--tw-placeholder-opacity, 1))}.opacity-0{opacity:0}.opacity-50{opacity:0.5}.opacity-75{opacity:0.75}.opacity-80{opacity:0.8}.opacity-90{opacity:0.9}.shadow-2xl{--tw-shadow:0 25px 50px -12px rgb(0 0 0 / 0.25);--tw-shadow-colored:0 25px 50px -12px var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)}.shadow-\[0_0_30px_rgba\(204\2c 255\2c 0\2c 0\.3\)\]{--tw-shadow:0 0 30px rgba(204,255,0,0.3);--tw-shadow-colored:0 0 30px var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)}.shadow-\[0_0_40px_rgba\(204\2c 255\2c 0\2c 0\.6\)\]{--tw-shadow:0 0 40px rgba(204,255,0,0.6);--tw-shadow-colored:0 0 40px var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)}.shadow-\[0_20px_100px_-20px_rgba\(204\2c 255\2c 0\2c 0\.3\)\]{--tw-shadow:0 20px 100px -20px rgba(204,255,0,0.3);--tw-shadow-colored:0 20px 100px -20px var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)}.shadow-lg{--tw-shadow:0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);--tw-shadow-colored:0 10px 15px -3px var(--tw-shadow-color), 0 4px 6px -4px var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)}.shadow-orange-500\/20{--tw-shadow-color:rgb(249 115 22 / 0.2);--tw-shadow:var(--tw-shadow-colored)}.shadow-neon\/30{--tw-shadow-color:rgb(204 255 0 / 0.3);--tw-shadow:var(--tw-shadow-colored)}.outline-none{outline:2px solid transparent;outline-offset:2px}.blur-3xl{--tw-blur:blur(64px);filter:var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)}.blur-\[100px\]{--tw-blur:blur(100px);filter:var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)}.blur-\[120px\]{--tw-blur:blur(120px);filter:var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)}.blur-\[150px\]{--tw-blur:blur(150px);filter:var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)}.blur-xl{--tw-blur:blur(24px);filter:var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)}.grayscale{--tw-grayscale:grayscale(100%);filter:var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)}.invert{--tw-invert:invert(100%);filter:var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)}.backdrop-blur-md{--tw-backdrop-blur:blur(12px);-webkit-backdrop-filter:var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia);backdrop-filter:var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia)}.backdrop-blur-sm{--tw-backdrop-blur:blur(4px);-webkit-backdrop-filter:var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia);backdrop-filter:var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia)}.backdrop-blur-xl{--tw-backdrop-blur:blur(24px);-webkit-backdrop-filter:var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia);backdrop-filter:var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia)}.transition{transition-property:color, background-color, border-color, fill, stroke, opacity, box-shadow, transform, filter, -webkit-text-decoration-color, -webkit-backdrop-filter;transition-property:color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;transition-property:color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter, -webkit-text-decoration-color, -webkit-backdrop-filter;transition-timing-function:cubic-bezier(0.4, 0, 0.2, 1);transition-duration:150ms}.transition-all{transition-property:all;transition-timing-function:cubic-bezier(0.4, 0, 0.2, 1);transition-duration:150ms}.transition-colors{transition-property:color, background-color, border-color, fill, stroke, -webkit-text-decoration-color;transition-property:color, background-color, border-color, text-decoration-color, fill, stroke;transition-property:color, background-color, border-color, text-decoration-color, fill, stroke, -webkit-text-decoration-color;transition-timing-function:cubic-bezier(0.4, 0, 0.2, 1);transition-duration:150ms}.transition-transform{transition-property:transform;transition-timing-function:cubic-bezier(0.4, 0, 0.2, 1);transition-duration:150ms}.duration-1000{transition-duration:1000ms}.duration-300{transition-duration:300ms}.duration-500{transition-duration:500ms}.duration-700{transition-duration:700ms}.hover\:-translate-y-0\.5:hover{--tw-translate-y:-0.125rem;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.hover\:-translate-y-2:hover{--tw-translate-y:-0.5rem;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.hover\:translate-x-1:hover{--tw-translate-x:0.25rem;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.hover\:rotate-0:hover{--tw-rotate:0deg;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.hover\:scale-105:hover{--tw-scale-x:1.05;--tw-scale-y:1.05;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.hover\:scale-110:hover{--tw-scale-x:1.1;--tw-scale-y:1.1;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.hover\:scale-\[1\.02\]:hover{--tw-scale-x:1.02;--tw-scale-y:1.02;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.hover\:border-blue-500:hover{--tw-border-opacity:1;border-color:rgb(59 130 246 / var(--tw-border-opacity, 1))}.hover\:border-neon:hover{--tw-border-opacity:1;border-color:rgb(204 255 0 / var(--tw-border-opacity, 1))}.hover\:border-slate-500:hover{--tw-border-opacity:1;border-color:rgb(100 116 139 / var(--tw-border-opacity, 1))}.hover\:border-white:hover{--tw-border-opacity:1;border-color:rgb(255 255 255 / var(--tw-border-opacity, 1))}.hover\:border-white\/30:hover{border-color:rgb(255 255 255 / 0.3)}.hover\:border-neon\/50:hover{border-color:rgb(204 255 0 / 0.5)}.hover\:border-slate-600:hover{--tw-border-opacity:1;border-color:rgb(71 85 105 / var(--tw-border-opacity, 1))}.hover\:bg-\[\#e34402\]:hover{--tw-bg-opacity:1;background-color:rgb(227 68 2 / var(--tw-bg-opacity, 1))}.hover\:bg-blue-500:hover{--tw-bg-opacity:1;background-color:rgb(59 130 246 / var(--tw-bg-opacity, 1))}.hover\:bg-lime-400:hover{--tw-bg-opacity:1;background-color:rgb(163 230 53 / var(--tw-bg-opacity, 1))}.hover\:bg-neon:hover{--tw-bg-opacity:1;background-color:rgb(204 255 0 / var(--tw-bg-opacity, 1))}.hover\:bg-slate-800:hover{--tw-bg-opacity:1;background-color:rgb(30 41 59 / var(--tw-bg-opacity, 1))}.hover\:bg-white:hover{--tw-bg-opacity:1;background-color:rgb(255 255 255 / var(--tw-bg-opacity, 1))}.hover\:text-dark:hover{--tw-text-opacity:1;color:rgb(15 23 42 / var(--tw-text-opacity, 1))}.hover\:text-neon:hover{--tw-text-opacity:1;color:rgb(204 255 0 / var(--tw-text-opacity, 1))}.hover\:text-slate-900:hover{--tw-text-opacity:1;color:rgb(15 23 42 / var(--tw-text-opacity, 1))}.hover\:text-white:hover{--tw-text-opacity:1;color:rgb(255 255 255 / var(--tw-text-opacity, 1))}.hover\:underline:hover{-webkit-text-decoration-line:underline;text-decoration-line:underline}.hover\:shadow-lg:hover{--tw-shadow:0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);--tw-shadow-colored:0 10px 15px -3px var(--tw-shadow-color), 0 4px 6px -4px var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)}.hover\:shadow-neon\/20:hover{--tw-shadow-color:rgb(204 255 0 / 0.2);--tw-shadow:var(--tw-shadow-colored)}.hover\:grayscale-0:hover{--tw-grayscale:grayscale(0);filter:var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)}.focus\:border-neon:focus{--tw-border-opacity:1;border-color:rgb(204 255 0 / var(--tw-border-opacity, 1))}.focus\:outline-none:focus{outline:2px solid transparent;outline-offset:2px}.focus\:ring-1:focus{--tw-ring-offset-shadow:var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);--tw-ring-shadow:var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);box-shadow:var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000)}.focus\:ring-neon:focus{--tw-ring-opacity:1;--tw-ring-color:rgb(204 255 0 / var(--tw-ring-opacity, 1))}.active\:scale-95:active{--tw-scale-x:.95;--tw-scale-y:.95;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.group:hover .group-hover\:translate-x-1{--tw-translate-x:0.25rem;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.group:hover .group-hover\:rotate-0{--tw-rotate:0deg;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.group:hover .group-hover\:scale-110{--tw-scale-x:1.1;--tw-scale-y:1.1;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}@keyframes bounce{0%, 100%{transform:translateY(-25%);animation-timing-function:cubic-bezier(0.8,0,1,1)}50%{transform:none;animation-timing-function:cubic-bezier(0,0,0.2,1)}}.group:hover .group-hover\:animate-bounce{animation:bounce 1s infinite}.group:hover .group-hover\:border-neon\/30{border-color:rgb(204 255 0 / 0.3)}.group:hover .group-hover\:bg-neon{--tw-bg-opacity:1;background-color:rgb(204 255 0 / var(--tw-bg-opacity, 1))}.group:hover .group-hover\:bg-transparent{background-color:transparent}.group:hover .group-hover\:text-dark{--tw-text-opacity:1;color:rgb(15 23 42 / var(--tw-text-opacity, 1))}.group:hover .group-hover\:text-neon{--tw-text-opacity:1;color:rgb(204 255 0 / var(--tw-text-opacity, 1))}.group:hover .group-hover\:text-white{--tw-text-opacity:1;color:rgb(255 255 255 / var(--tw-text-opacity, 1))}.group:hover .group-hover\:opacity-100{opacity:1}.group:hover .group-hover\:grayscale-0{--tw-grayscale:grayscale(0);filter:var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)}@media (min-width: 360px){.xs\:gap-1{gap:0.25rem}.xs\:text-xl{font-size:1.25rem;line-height:1.75rem}}@media (min-width: 640px){.sm\:flex-row{flex-direction:row}.sm\:px-6{padding-left:1.5rem;padding-right:1.5rem}}@media (min-width: 768px){.md\:-right-12{right:-3rem}.md\:order-1{order:1}.md\:order-2{order:2}.md\:col-span-1{grid-column:span 1 / span 1}.md\:mx-0{margin-left:0px;margin-right:0px}.md\:block{display:block}.md\:flex{display:flex}.md\:hidden{display:none}.md\:h-10{height:2.5rem}.md\:h-32{height:8rem}.md\:h-8{height:2rem}.md\:w-32{width:8rem}.md\:w-96{width:24rem}.md\:w-16{width:4rem}.md\:-translate-y-4{--tw-translate-y:-1rem;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.md\:grid-cols-2{grid-template-columns:repeat(2, minmax(0, 1fr))}.md\:grid-cols-3{grid-template-columns:repeat(3, minmax(0, 1fr))}.md\:grid-cols-4{grid-template-columns:repeat(4, minmax(0, 1fr))}.md\:flex-row{flex-direction:row}.md\:justify-start{justify-content:flex-start}.md\:justify-between{justify-content:space-between}.md\:gap-12{gap:3rem}.md\:gap-20{gap:5rem}.md\:p-20{padding:5rem}.md\:pt-0{padding-top:0px}.md\:text-left{text-align:left}.md\:text-2xl{font-size:1.5rem;line-height:2rem}.md\:text-5xl{font-size:3rem;line-height:1}.md\:text-6xl{font-size:3.75rem;line-height:1}.md\:text-7xl{font-size:4.5rem;line-height:1}.md\:text-sm{font-size:0.875rem;line-height:1.25rem}.md\:text-xl{font-size:1.25rem;line-height:1.75rem}.md\:text-4xl{font-size:2.25rem;line-height:2.5rem}}@media (min-width: 1024px){.lg\:col-span-2{grid-column:span 2 / span 2}.lg\:grid-cols-2{grid-template-columns:repeat(2, minmax(0, 1fr))}.lg\:px-8{padding-left:2rem;padding-right:2rem}.lg\:text-8xl{font-size:6rem;line-height:1}}</style>
</head>
<body>
    @include('layouts.components.pacerhub-nav')
    <div id="ph-sidebar-backdrop" class="fixed inset-0 bg-black/40 z-40 hidden"></div>
    @include('layouts.components.pacerhub-sidebar')
    <div id="app" class="relative min-h-screen flex flex-col pt-20" v-cloak>
        <header class="relative h-[90vh] lg:h-[90vh] flex items-center justify-center overflow-hidden">
            <img src="{{ $event->getHeroImageUrl() ?? 'https://images.unsplash.com/photo-1532444458054-01a7dd3e9fca?q=80&w=1600&auto=format&fit=crop' }}"
                 alt="{{ $event->name }}"
                 class="absolute inset-0 w-full h-full object-cover"
                 loading="eager"
                 fetchpriority="high">
            <div class="absolute inset-0 bg-gradient-to-t from-[var(--dark)] via-[var(--dark)]/70 to-transparent"></div>
            <div class="relative z-10 text-center px-4 w-full max-w-5xl mx-auto mt-12 pb-20 mb:pt-[120px]">
                <div class="inline-flex items-center space-x-2 bg-sport-volt text-black px-3 py-1 font-bold text-sm tracking-widest uppercase mb-4 transform -skew-x-12">
                    <span>Next Session</span>
                </div>
               
                <h1 class="text-6xl md:text-8xl font-display uppercase tracking-wider mb-2 leading-none text-white">
                    {{ strtoupper($event->name) }} <span class="text-sport-volt">Track Series</span>
                </h1>
                <p class="text-gray-300 text-lg md:text-xl font-light mb-8 max-w-2xl mx-auto">
                    Dorong batas kemampuanmu. Bergabunglah dengan sesi latihan di stadion kebanggaan Malang.
                </p>
               
                <div class="flex flex-wrap justify-center gap-6 mb-8">
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 px-4 py-2 min-w-[80px]">
                        <span class="block text-3xl font-display text-sport-volt">@{{ countdown.days }}</span>
                        <span class="text-[10px] uppercase tracking-widest text-gray-400">Hari</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 px-4 py-2 min-w-[80px]">
                        <span class="block text-3xl font-display text-sport-volt">@{{ countdown.hours }}</span>
                        <span class="text-[10px] uppercase tracking-widest text-gray-400">Jam</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 px-4 py-2 min-w-[80px]">
                        <span class="block text-3xl font-display text-sport-volt">@{{ countdown.minutes }}</span>
                        <span class="text-[10px] uppercase tracking-widest text-gray-400">Menit</span>
                    </div>
                </div>
                <button v-on:click="scrollToForm" class="bg-sport-volt hover:bg-white text-black font-bold py-3 px-8 text-sm md:text-base uppercase tracking-wider transition-all rounded-lg shadow-[0_0_20px_rgba(204,255,0,0.4)]">
                    Daftar Sekarang
                </button>
            </div>
        </header>
        <main id="registration-area" class="flex-grow max-w-7xl mx-auto px-4 md:px-8 -mt-20 pt-20 relative z-20 pb-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8">
                <div class="lg:col-span-7">
                    <div class="glass-dark p-6 md:p-8 shadow-2xl rounded-2xl animate-fade-in">
                        <div class="flex items-center justify-between mb-8 border-b border-white/10 pb-4">
                            <div>
                                <h2 class="text-3xl font-display uppercase text-white tracking-wide">Formulir Pendaftaran</h2>
                                <p class="text-xs text-gray-400 mt-1 font-mono">ISI DATA DIRI ANDA DENGAN BENAR</p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center border border-white/10">
                                <i class="fas fa-running text-sport-volt animate-pulse"></i>
                            </div>
                        </div>
                        <form v-on:submit.prevent="processPayment" class="space-y-6">
                            
                            <!-- Personal Info -->
                            <div class="space-y-2">
                                <div class="form-input-group animate-fade-in delay-100">
                                    <input type="text" id="name" v-model="form.name" required class="form-input" placeholder=" ">
                                    <label for="name" class="form-label">NAMA LENGKAP</label>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="form-input-group animate-fade-in delay-200">
                                        <input type="tel" id="phone" v-model="form.phone" required class="form-input" placeholder=" ">
                                        <label for="phone" class="form-label">WHATSAPP</label>
                                    </div>
                                    <div class="form-input-group animate-fade-in delay-200">
                                        <input type="email" id="email" v-model="form.email" required class="form-input" placeholder=" ">
                                        <label for="email" class="form-label">EMAIL</label>
                                    </div>
                                </div>
                                <div class="form-input-group animate-fade-in delay-250 pb-5">
                                    <select id="gender" v-model="form.gender" required class="form-input">
                                        <option value="male">Laki-laki</option>
                                        <option value="female">Perempuan</option>
                                    </select>
                                    <label for="gender" class="form-label">GENDER</label>
                                </div>
                                
                                <div class="form-input-group animate-fade-in delay-300">
                                    <input type="number" id="ticket" v-model="form.ticket_quantity" min="1" required class="form-input" placeholder=" ">
                                    <label for="ticket" class="form-label">JUMLAH TIKET</label>
                                </div>
                            </div>

                            <!-- Addons Section -->
                            <div v-if="availableAddons.length > 0" class="animate-fade-in delay-300">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="w-1 h-4 bg-sport-volt rounded-full"></div>
                                    <h3 class="text-sm font-bold text-gray-300 uppercase tracking-wider">Add-ons (Optional)</h3>
                                </div>
                                <div class="grid grid-cols-1 gap-3">
                                    <div v-for="(addon, index) in availableAddons" :key="index"
                                         class="relative group bg-white/5 border border-white/10 rounded-xl p-4 cursor-pointer hover:bg-white/10 transition-all duration-300"
                                         :class="{'border-sport-volt bg-sport-volt/5': isAddonSelected(addon)}"
                                         @click="toggleAddon(addon)">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-4">
                                                <div class="w-6 h-6 rounded border flex items-center justify-center transition-all duration-300"
                                                     :class="isAddonSelected(addon) ? 'bg-sport-volt border-sport-volt' : 'border-gray-500 group-hover:border-sport-volt'">
                                                    <i class="fas fa-check text-black text-xs transform scale-0 transition-transform duration-200"
                                                       :class="{'scale-100': isAddonSelected(addon)}"></i>
                                                </div>
                                                <div>
                                                    <span class="block text-sm font-bold text-white group-hover:text-sport-volt transition-colors">@{{ addon.name }}</span>
                                                    <span class="text-xs text-gray-500">Tambahan opsional</span>
                                                </div>
                                            </div>
                                            <span class="text-sm font-display text-sport-volt bg-sport-volt/10 px-3 py-1 rounded-lg border border-sport-volt/20">
                                                @{{ formatCurrency(addon.price) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="animate-fade-in delay-300">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="w-1 h-4 bg-sport-volt rounded-full"></div>
                                    <h3 class="text-sm font-bold text-gray-300 uppercase tracking-wider">Metode Pembayaran</h3>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="payment-card" :class="{'active': form.payment_method === 'midtrans'}" @click="form.payment_method = 'midtrans'">
                                        <div class="flex items-center justify-between mb-2">
                                            <i class="fas fa-credit-card text-xl text-gray-400" :class="{'text-sport-volt': form.payment_method === 'midtrans'}"></i>
                                            <div class="check-circle"></div>
                                        </div>
                                        <div class="font-bold text-sm text-white">Online Payment</div>
                                        <div class="text-[10px] text-gray-500 mt-1">QRIS, E-Wallet, Virtual Account</div>
                                    </div>
                                    
                                    <div class="payment-card" :class="{'active': form.payment_method === 'cod'}" @click="form.payment_method = 'cod'">
                                        <div class="flex items-center justify-between mb-2">
                                            <i class="fas fa-hand-holding-usd text-xl text-gray-400" :class="{'text-sport-volt': form.payment_method === 'cod'}"></i>
                                            <div class="check-circle"></div>
                                        </div>
                                        <div class="font-bold text-sm text-white">Bayar di Lokasi</div>
                                        <div class="text-[10px] text-gray-500 mt-1">Cash On Delivery (COD)</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total & Action -->
                            <div class="border-t border-white/10 pt-6 mt-6 animate-fade-in delay-300">
                                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                                    <div class="text-center md:text-left w-full md:w-auto">
                                        <span class="block text-gray-500 text-[10px] font-mono uppercase tracking-widest mb-1">Total Pembayaran</span>
                                        <div class="font-display text-4xl text-white tracking-wide">
                                            @{{ formattedTotal }}
                                        </div>
                                    </div>
                                    @if(env('RECAPTCHA_SITE_KEY'))
                                    <div class="w-full md:w-auto">
                                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                                    </div>
                                    @endif
                                    <button type="submit" :disabled="isLoading" 
                                            class="group relative w-full md:w-auto bg-white hover:bg-sport-volt text-black font-black py-4 px-10 text-base uppercase tracking-widest transition-all duration-300 rounded-xl overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed transform hover:-translate-y-1 hover:shadow-[0_10px_30px_rgba(204,255,0,0.3)]">
                                        <div class="relative z-10 flex items-center justify-center gap-3">
                                            <span v-if="isLoading">Processing...</span>
                                            <span v-else>Bayar Sekarang</span>
                                            <i v-if="!isLoading" class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                                        </div>
                                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="lg:col-span-5">
                    <div class="glass-dark p-6 md:p-8 h-full relative rounded-2xl">
                        <div class="flex items-center justify-between mb-5 md:mb-6">
                            <div>
                                <h3 class="text-lg md:text-xl font-display uppercase text-white">Daftar Peserta</h3>
                                <p class="text-xs text-gray-400">Bergabung dengan @{{ participants.length }} pelari lainnya</p>
                            </div>
                            <div class="bg-gray-800 p-2 rounded-lg">
                                <svg class="w-6 h-6 text-sport-volt" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                        </div>
                        @php
                            $codCount = \App\Models\Participant::whereHas('transaction', function($q) use ($event) { $q->where('event_id', $event->id)->where('payment_status','cod'); })
                                ->when($event->registration_open_at, function($q) use ($event) { $q->where('created_at', '>=', $event->registration_open_at); })
                                ->count();
                            $paidCount = \App\Models\Participant::whereHas('transaction', function($q) use ($event) { $q->where('event_id', $event->id)->where('payment_status','paid'); })
                                ->when($event->registration_open_at, function($q) use ($event) { $q->where('created_at', '>=', $event->registration_open_at); })
                                ->count();
                            $codNames = \App\Models\Participant::whereHas('transaction', function($q) use ($event) { $q->where('event_id', $event->id)->where('payment_status','cod'); })
                                ->when($event->registration_open_at, function($q) use ($event) { $q->where('created_at', '>=', $event->registration_open_at); })
                                ->orderBy('created_at','desc')->limit(10)->get(['name']);
                            $paidNames = \App\Models\Participant::whereHas('transaction', function($q) use ($event) { $q->where('event_id', $event->id)->where('payment_status','paid'); })
                                ->when($event->registration_open_at, function($q) use ($event) { $q->where('created_at', '>=', $event->registration_open_at); })
                                ->orderBy('created_at','desc')->limit(10)->get(['name']);
                        @endphp
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <div class="p-3 bg-white/5 border border-white/10 rounded-xl">
                                <div class="text-xs text-gray-400 uppercase">COD Terdaftar</div>
                                <div class="text-2xl font-display text-sport-volt">{{ $codCount ?? 0 }}</div>
                                <ul class="mt-2 text-xs text-gray-400 space-y-1">
                                    @foreach(($codNames ?? []) as $n)
                                        <li>{{ $n->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="p-3 bg-white/5 border border-white/10 rounded-xl">
                                <div class="text-xs text-gray-400 uppercase">Paid</div>
                                <div class="text-2xl font-display text-sport-volt">{{ $paidCount ?? 0 }}</div>
                                <ul class="mt-2 text-xs text-gray-400 space-y-1">
                                    @foreach(($paidNames ?? []) as $n)
                                        <li>{{ $n->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <div class="overflow-y-auto max-h-[500px] space-y-3 pr-1">
                            <div v-for="(p, index) in participants" :key="p.id || index" class="flex items-center p-3 bg-white/5 border border-white/5 rounded-xl hover:bg-white/10 transition">
                                <div class="w-10 h-10 flex-shrink-0 bg-gray-800 text-sport-volt font-bold font-display text-lg flex items-center justify-center border border-gray-600 rounded-lg">
                                    @{{ getInitials(p.name) }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-200">@{{ p.name }}</div>
                                    <div class="text-[10px] text-gray-500 uppercase tracking-wider">Ready to Run</div>
                                </div>
                                @php($canManage = auth()->check() && auth()->user()->isEventOrganizer() && $event->user_id === auth()->id())
                                @if($canManage)
                                <div class="ml-auto">
                                    <button @click="deleteParticipant(p.id)" class="text-xs px-3 py-1 rounded bg-red-600 text-white hover:bg-red-500">Delete</button>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($hasPaidParticipants ?? false)
                @include('events.partials.participants-table')
            @endif
        </main>
        @include('layouts.components.pacerhub-footer')
    </div>

    <script type="text/javascript" src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
    const { createApp, ref, computed, onMounted } = Vue;
    const app = createApp({
        setup() {
            const form = ref({ name: '', email: '', phone: '', ticket_quantity: 1, addons: [], gender: 'male', emergency_contact_name: '', emergency_contact_number: '' });
            const isLoading = ref(false);
            const prices = { base: 15000 };
            
            // Defensives for array initialization
            const participantsRaw = @json($participants->map(fn($p) => ['id' => $p->id, 'name' => $p->name]));
            const participants = ref(Array.isArray(participantsRaw) ? participantsRaw : []);
            
            const categoriesRaw = @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]));
            const categories = Array.isArray(categoriesRaw) ? categoriesRaw : [];
            
            const addonsRaw = @json($event->addons ?? []);
            const availableAddons = ref(Array.isArray(addonsRaw) ? addonsRaw : []);
            
            const formatCurrency = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
            const isAddonSelected = (addon) => Array.isArray(form.value.addons) && form.value.addons.some(a => a.name === addon.name);
            const toggleAddon = (addon) => {
                if (!Array.isArray(form.value.addons)) form.value.addons = [];
                const index = form.value.addons.findIndex(a => a.name === addon.name);
                if (index === -1) form.value.addons.push(addon);
                else form.value.addons.splice(index, 1);
            };
            const formattedTotal = computed(() => {
                const addonsList = Array.isArray(form.value.addons) ? form.value.addons : [];
                const addonsTotal = addonsList.reduce((sum, addon) => sum + (parseInt(addon.price) || 0), 0);
                const total = (prices.base * (form.value.ticket_quantity || 1)) + (addonsTotal * (form.value.ticket_quantity || 1));
                return formatCurrency(total);
            });
            const scrollToForm = () => document.getElementById('registration-area').scrollIntoView({ behavior: 'smooth' });
            const getInitials = (name) => name ? name.split(' ').map(n => n[0]).slice(0,2).join('').toUpperCase() : '??';
            const defaultCategoryId = categories.length > 0 ? categories[0].id : null;
            const countdown = ref({ days: 0, hours: 0, minutes: 0 });
            const startIso = "{{ optional($event->start_at)->format('c') }}";
            const startDate = startIso ? new Date(startIso) : null;
            const canManage = {{ (auth()->check() && auth()->user()->isEventOrganizer() && $event->user_id === auth()->id()) ? 'true' : 'false' }};
            const deleteBaseUrl = "{{ route('eo.events.participants.destroy', [$event, 0]) }}";
            const tick = () => {
                if (!startDate) return;
                const now = new Date();
                const dist = startDate.getTime() - now.getTime();
                if (dist <= 0) { countdown.value = { days: 0, hours: 0, minutes: 0 }; return; }
                countdown.value.days = Math.floor(dist / (1000 * 60 * 60 * 24));
                countdown.value.hours = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                countdown.value.minutes = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60));
            };
            const deleteParticipant = async (id) => {
                if (!canManage) return;
                if (!id) return;
                if (!confirm('Hapus peserta ini?')) return;
                const csrf = document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content');
                try {
                    const lastSlash = deleteBaseUrl.lastIndexOf('/');
                    const base = lastSlash >= 0 ? deleteBaseUrl.slice(0, lastSlash) : deleteBaseUrl;
                    const url = base + '/' + id;
                    const res = await fetch(url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' } });
                    const data = await res.json();
                    if (data && data.success) {
                        const idx = participants.value.findIndex(p => String(p.id) === String(id));
                        if (idx >= 0) participants.value.splice(idx, 1);
                    } else {
                        alert((data && data.message) || 'Gagal menghapus peserta');
                    }
                } catch (e) {
                    alert('Terjadi kesalahan');
                }
            };
            const processPayment = async () => {
                isLoading.value = true;
                try {
                    const participantsList = [];
                    for (let i = 0; i < form.value.ticket_quantity; i++) {
                        participantsList.push({
                            name: form.value.name,
                            gender: form.value.gender || 'male',
                            email: form.value.email,
                            phone: form.value.phone,
                            id_card: form.value.phone,
                            category_id: defaultCategoryId,
                            emergency_contact_name: form.value.emergency_contact_name || form.value.name,
                            emergency_contact_number: form.value.emergency_contact_number || form.value.phone,
                        });
                    }

                    let recaptchaToken = '';
                    @if(env('RECAPTCHA_SITE_KEY'))
                    if (window.grecaptcha && typeof grecaptcha.getResponse === 'function') {
                        recaptchaToken = grecaptcha.getResponse();
                    }
                    @endif
                    @if(env('RECAPTCHA_SECRET_KEY'))
                    if (!recaptchaToken) {
                        alert('Silakan verifikasi reCAPTCHA terlebih dahulu.');
                        isLoading.value = false;
                        return;
                    }
                    @endif
                    const payload = {
                        pic_name: form.value.name,
                        pic_email: form.value.email,
                        pic_phone: form.value.phone,
                        payment_method: form.value.payment_method || 'midtrans',
                        addons: form.value.addons,
                        participants: participantsList,
                        'g-recaptcha-response': recaptchaToken
                    };
                    const res = await fetch("{{ route('events.register.store', $event->slug) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        alert(data.message || 'Registrasi gagal');
                        return;
                    }
                    if ((form.value.payment_method || 'midtrans') === 'midtrans' && data.snap_token) {
                        window.snap.pay(data.snap_token, {
                            onSuccess: function(result){
                                alert("Pembayaran berhasil!");
                                window.location.reload();
                            },
                            onPending: function(result){
                                alert("Menunggu pembayaran!");
                                window.location.reload();
                            },
                            onError: function(result){
                                alert("Pembayaran gagal!");
                                window.location.reload();
                            },
                            onClose: function(){
                                alert('Anda menutup popup tanpa menyelesaikan pembayaran');
                            }
                        });
                    } else {
                        alert('Registrasi COD berhasil. Silakan lakukan pembayaran di lokasi.');
                        window.location.reload();
                    }
                } catch (e) {
                    alert('Terjadi kesalahan: ' + e.message);
                } finally {
                    isLoading.value = false;
                    if (window.grecaptcha && typeof grecaptcha.reset === 'function') {
                        grecaptcha.reset();
                    }
                }
            };
            onMounted(() => {
                form.value.payment_method = 'midtrans';
                tick();
                setInterval(tick, 1000);
            });
            return { form, isLoading, formattedTotal, processPayment, participants, scrollToForm, getInitials, countdown, deleteParticipant, availableAddons, formatCurrency, isAddonSelected, toggleAddon };
        }
    });
    
    if (typeof ParticipantsTableComponent !== 'undefined') {
        app.component('participants-table', ParticipantsTableComponent);
    }

    app.mount('#app');

    // Nominatim CORS Proxy Interceptor (Fetch & XHR)
    (function() {
        // 1. Fetch Interceptor
        var originalFetch = window.fetch;
        window.fetch = function(url, options) {
            if (typeof url === 'string' && url.includes('nominatim.openstreetmap.org')) {
                var proxyUrl = '/image-proxy?url=' + encodeURIComponent(url);
                return originalFetch(proxyUrl, options);
            }
            return originalFetch(url, options);
        };

        // 2. XHR Interceptor
        var originalOpen = XMLHttpRequest.prototype.open;
        XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
            if (typeof url === 'string' && url.includes('nominatim.openstreetmap.org')) {
                url = '/image-proxy?url=' + encodeURIComponent(url);
            }
            return originalOpen.apply(this, arguments);
        };
    })();
    </script>
    <script>
    (function(){
        var btn = document.getElementById('ph-sidebar-toggle');
        var sidebar = document.getElementById('ph-sidebar');
        var backdrop = document.getElementById('ph-sidebar-backdrop');
        function openSidebar(){
            if(!sidebar) return;
            sidebar.classList.remove('-translate-x-full');
            if(backdrop){ backdrop.classList.remove('hidden'); }
        }
        function closeSidebar(){
            if(!sidebar) return;
            sidebar.classList.add('-translate-x-full');
            if(backdrop){ backdrop.classList.add('hidden'); }
        }
        if(btn){
            btn.addEventListener('click', function(){
                if(sidebar && sidebar.classList.contains('-translate-x-full')){ openSidebar(); } else { closeSidebar(); }
            });
        }
        if(backdrop){
            backdrop.addEventListener('click', closeSidebar);
        }
        document.addEventListener('keydown', function(e){
            if(e.key === 'Escape'){ closeSidebar(); }
        });
    })();
    </script>
    @stack('scripts')
</body>
</html>
