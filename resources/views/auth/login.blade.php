<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Ruang Lari</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/favicon.png') }}">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (via CDN for simplicity, or use asset('css/app.css') if built) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        neon: '#ccff00',
                        dark: '#0f172a',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #0f172a;
            color: #e2e8f0;
        }
        .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .input-glass {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.2);
            color: white;
        }
        .input-glass:focus {
            border-color: #ccff00;
            box-shadow: 0 0 10px rgba(204, 255, 0, 0.2);
            outline: none;
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center relative overflow-hidden">
    
    <!-- Background Effects -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-cyan-500/20 rounded-full blur-[120px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-purple-500/20 rounded-full blur-[120px] animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="w-full max-w-md px-6 relative z-10">
        
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black italic tracking-tighter text-white mb-2">
                RUANG<span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-neon">LARI</span>
            </h1>
            <p class="text-slate-400 text-sm">Welcome back, Runner.</p>
        </div>

        <div class="glass p-8 rounded-3xl shadow-2xl">
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-sm">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-3 rounded-xl input-glass transition-all" placeholder="name@example.com" required autofocus>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Password</label>
                    <input type="password" name="password" class="w-full px-4 py-3 rounded-xl input-glass transition-all" placeholder="••••••••" required>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-white transition-colors">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-neon focus:ring-neon">
                        <span>Remember me</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-cyan-400 hover:text-cyan-300 transition-colors">Forgot Password?</a>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold shadow-lg shadow-cyan-500/25 transition-all transform hover:scale-[1.02]">
                    SIGN IN
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-slate-500 text-sm">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="text-neon hover:text-white font-bold transition-colors ml-1">Join Now</a>
                </p>
            </div>
        </div>

        <div class="mt-8 text-center">
             <p class="text-xs text-slate-600">&copy; {{ date('Y') }} RuangLari. All rights reserved.</p>
        </div>
    </div>

</body>
</html>
