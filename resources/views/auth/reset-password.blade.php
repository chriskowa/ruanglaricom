<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - Ruang Lari</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/favicon.png') }}">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
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
                RUANG<span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-neon pr-2">LARI</span>
            </h1>
            <p class="text-slate-400 text-sm">Create a new password for your account.</p>
        </div>

        <div class="glass p-8 rounded-3xl shadow-2xl">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-white mb-2">Reset Password</h2>
                <p class="text-slate-400 text-xs leading-relaxed">
                    Please enter your email and choose a secure new password.
                </p>
            </div>

            @if (session('status'))
                <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-300 text-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-sm">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                
                <!-- Hidden Token -->
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ request()->email ?? old('email') }}" class="w-full px-4 py-3 rounded-xl input-glass transition-all" placeholder="name@example.com" required readonly>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">New Password</label>
                    <input type="password" name="password" class="w-full px-4 py-3 rounded-xl input-glass transition-all" placeholder="••••••••" required autofocus>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="w-full px-4 py-3 rounded-xl input-glass transition-all" placeholder="••••••••" required>
                </div>

                <button type="submit" class="w-full py-3.5 mt-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold shadow-lg shadow-cyan-500/25 transition-all transform hover:scale-[1.02]">
                    RESET PASSWORD
                </button>
            </form>
        </div>

        <div class="mt-8 text-center">
             <p class="text-xs text-slate-600">&copy; {{ date('Y') }} RuangLari. All rights reserved.</p>
        </div>
    </div>

</body>
</html>
