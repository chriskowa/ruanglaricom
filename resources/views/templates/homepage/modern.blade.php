<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->meta_title ?? $page->title ?? 'RuangLari' }}</title>
    <meta name="description" content="{{ $page->meta_description ?? 'Komunitas lari terbesar di Indonesia' }}">
    <meta name="keywords" content="{{ $page->meta_keywords ?? 'lari, running, komunitas lari, event lari' }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('{{ $page->template_data['hero_image'] ?? '/images/default-hero.jpg' }}');
            background-size: cover;
            background-position: center;
        }
        
        .floating-btn {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body class="font-sans bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full top-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <img src="/images/logo.png" alt="RuangLari" class="h-10">
                    <span class="ml-2 text-xl font-bold text-green-600">RuangLari</span>
                </div>
                
                <div class="hidden md:flex space-x-6">
                    <a href="/events" class="text-gray-700 hover:text-green-600">Events</a>
                    <a href="/komunitas" class="text-gray-700 hover:text-green-600">Komunitas</a>
                    <a href="/program" class="text-gray-700 hover:text-green-600">Program</a>
                    <a href="/about" class="text-gray-700 hover:text-green-600">Tentang</a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="/login" class="text-gray-700 hover:text-green-600">Login</a>
                    <a href="/register" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Daftar</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section min-h-screen flex items-center pt-16">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl">
                <h1 class="text-5xl md:text-6xl font-bold text-white mb-6">
                    {{ $page->template_data['headline'] ?? 'Temukan Komunitas Lari Terbaik' }}
                </h1>
                
                <p class="text-xl text-white mb-8">
                    {{ $page->template_data['subheadline'] ?? 'Bergabung dengan ribuan pelari di seluruh Indonesia. Event, program latihan, dan komunitas yang mendukung goals lari Anda.' }}
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ $page->template_data['cta_link'] ?? '/events' }}" class="bg-green-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-green-700 transition duration-300 text-center">
                        {{ $page->template_data['cta_text'] ?? 'Jelajahi Events' }}
                    </a>
                    
                    <a href="/komunitas" class="border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-green-600 transition duration-300 text-center">
                        Lihat Komunitas
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Mengapa Memilih RuangLari?</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Platform lengkap untuk segala kebutuhan lari Anda</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-6">
                    <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-calendar-alt text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">100+ Events</h3>
                    <p class="text-gray-600">Event lari terbaik dari berbagai penyelenggara di seluruh Indonesia</p>
                </div>
                
                <div class="text-center p-6">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Komunitas Aktif</h3>
                    <p class="text-gray-600">Bergabung dengan komunitas lari yang supportive dan berpengalaman</p>
                </div>
                
                <div class="text-center p-6">
                    <div class="bg-orange-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-orange-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Tracking Progress</h3>
                    <p class="text-gray-600">Pantau perkembangan lari Anda dengan tools yang lengkap</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-green-600 text-white">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl font-bold mb-2">50K+</div>
                    <div class="text-lg">Pelari Terdaftar</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-2">500+</div>
                    <div class="text-lg">Events</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-2">100+</div>
                    <div class="text-lg">Komunitas</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-2">30+</div>
                    <div class="text-lg">Kota</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Siap Memulai Perjalanan Lari Anda?</h2>
            <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">Bergabung dengan komunitas lari terbesar di Indonesia dan raih goals lari Anda</p>
            
            <div class="flex justify-center gap-4">
                <a href="/register" class="bg-green-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-green-700 transition duration-300">
                    Daftar Sekarang
                </a>
                <a href="/about" class="border-2 border-green-600 text-green-600 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-green-600 hover:text-white transition duration-300">
                    Pelajari Lebih Lanjut
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <img src="/images/logo.png" alt="RuangLari" class="h-10 mb-4">
                    <p class="text-gray-400">Platform komunitas lari terbesar di Indonesia</p>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Tautan Cepat</h4>
                    <ul class="space-y-2">
                        <li><a href="/events" class="text-gray-400 hover:text-white">Events</a></li>
                        <li><a href="/komunitas" class="text-gray-400 hover:text-white">Komunitas</a></li>
                        <li><a href="/program" class="text-gray-400 hover:text-white">Program</a></li>
                        <li><a href="/blog" class="text-gray-400 hover:text-white">Blog</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Dukungan</h4>
                    <ul class="space-y-2">
                        <li><a href="/help" class="text-gray-400 hover:text-white">Bantuan</a></li>
                        <li><a href="/contact" class="text-gray-400 hover:text-white">Kontak</a></li>
                        <li><a href="/terms" class="text-gray-400 hover:text-white">Syarat & Ketentuan</a></li>
                        <li><a href="/privacy" class="text-gray-400 hover:text-white">Privasi</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Ikuti Kami</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-youtube text-xl"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2024 RuangLari. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>