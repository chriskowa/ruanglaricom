<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Ruang Lari</title>
    
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
                        card: '#1e293b',
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
        /* Hide default radio */
        .role-radio:checked + .role-card {
            border-color: #ccff00;
            background-color: rgba(204, 255, 0, 0.1);
        }
        .role-radio:checked + .role-card .role-icon {
            color: #ccff00;
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center relative overflow-hidden py-10">
    
    <!-- Background Effects -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-cyan-500/20 rounded-full blur-[120px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-purple-500/20 rounded-full blur-[120px] animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="w-full max-w-lg px-6 relative z-10">
        
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black italic tracking-tighter text-white mb-2">
                RUANG<span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-neon">LARI</span>
            </h1>
            <p class="text-slate-400 text-sm">Join the community. Start your journey.</p>
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

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf
                
                <!-- Role Selection -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">I am a...</label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="runner" class="role-radio hidden" {{ old('role', 'runner') == 'runner' ? 'checked' : '' }}>
                            <div class="role-card border border-slate-700 rounded-xl p-3 text-center hover:bg-slate-800 transition-all h-full flex flex-col items-center justify-center gap-2">
                                <svg class="w-6 h-6 text-slate-400 role-icon transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <span class="text-xs font-bold text-white">Runner</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="coach" class="role-radio hidden" {{ old('role') == 'coach' ? 'checked' : '' }}>
                            <div class="role-card border border-slate-700 rounded-xl p-3 text-center hover:bg-slate-800 transition-all h-full flex flex-col items-center justify-center gap-2">
                                <svg class="w-6 h-6 text-slate-400 role-icon transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span class="text-xs font-bold text-white">Coach</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="eo" class="role-radio hidden" {{ old('role') == 'eo' ? 'checked' : '' }}>
                            <div class="role-card border border-slate-700 rounded-xl p-3 text-center hover:bg-slate-800 transition-all h-full flex flex-col items-center justify-center gap-2">
                                <svg class="w-6 h-6 text-slate-400 role-icon transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-xs font-bold text-white">Organizer</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full px-4 py-3 rounded-xl input-glass transition-all" placeholder="John Doe" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-3 rounded-xl input-glass transition-all" placeholder="name@example.com" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Password</label>
                        <input type="password" name="password" class="w-full px-4 py-3 rounded-xl input-glass transition-all" placeholder="••••••••" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Confirm</label>
                        <input type="password" name="password_confirmation" class="w-full px-4 py-3 rounded-xl input-glass transition-all" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold shadow-lg shadow-cyan-500/25 transition-all transform hover:scale-[1.02]">
                        CREATE ACCOUNT
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center">
                <p class="text-slate-500 text-sm">
                    Already have an account? 
                    <a href="{{ route('login') }}" class="text-neon hover:text-white font-bold transition-colors ml-1">Sign In</a>
                </p>
            </div>
        </div>

        <div class="mt-8 text-center">
             <p class="text-xs text-slate-600">&copy; {{ date('Y') }} RuangLari. All rights reserved.</p>
        </div>
    </div>

</body>
</html>
