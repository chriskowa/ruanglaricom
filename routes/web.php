<?php

use App\Http\Controllers\Runner\CalendarController;
use App\Http\Controllers\Runner\DashboardController;
use App\Services\StravaClubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (StravaClubService $stravaService) {
    $homepageContent = \Illuminate\Support\Facades\Cache::remember('home.content', 3600, function () {
        return \App\Models\HomepageContent::first();
    });

    $leaderboard = \Illuminate\Support\Facades\Cache::remember('home.leaderboard.data', 3600, function () use ($stravaService) {
        try {
            $data = $stravaService->getLeaderboard();
            return (is_array($data) && ($data['fastest'] || $data['distance'] || $data['elevation'])) ? $data : null;
        } catch (\Throwable $e) {
            return null;
        }
    });

    // Fallback to permanent cache if recent fetch failed/returned null but we have old data
    if (!$leaderboard) {
        $leaderboard = \Illuminate\Support\Facades\Cache::get('home.leaderboard.last');
    } else {
        // Update permanent cache
        \Illuminate\Support\Facades\Cache::forever('home.leaderboard.last', $leaderboard);
        \Illuminate\Support\Facades\Cache::forever('home.leaderboard.last_at', now()->toISOString());
    }

    $topStats = \Illuminate\Support\Facades\Cache::remember('home.top_stats', 3600, function () {
        $runner = \App\Models\User::where('role', 'runner')
            ->withCount(['followers', 'posts'])
            ->orderByDesc('followers_count')
            ->first();

        $pacer = \App\Models\Pacer::with('user')
            ->orderByDesc('total_races')
            ->first();

        $coachData = \App\Models\ProgramEnrollment::selectRaw('programs.coach_id as coach_id, COUNT(*) as students_count')
            ->join('programs', 'program_enrollments.program_id', '=', 'programs.id')
            ->groupBy('programs.coach_id')
            ->orderByDesc('students_count')
            ->first();

        $coach = $coachData ? \App\Models\User::find($coachData->coach_id) : null;

        return [
            'runner' => $runner,
            'pacer' => $pacer,
            'coach' => $coach,
            'coachData' => $coachData
        ];
    });

    return view('home.index', [
        'homepageContent' => $homepageContent,
        'leaderboard' => $leaderboard,
        'topRunner' => $topStats['runner'],
        'topPacer' => $topStats['pacer'],
        'topCoach' => $topStats['coach'],
        'topCoachData' => $topStats['coachData']
    ]);
})->name('home');

// Challenge: 40 Days Challenge - reuse realistic program design view with challenge mode
Route::get('/challenge/40-days-challenge', function () {
    return view('programs.design', [
        'challengeMode' => true,
        'challengeProgramId' => 9,
    ]);
})->name('challenge.40days');

Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/v-card', [App\Http\Controllers\VCardController::class, 'index'])->name('vcard.index');
Route::get('/vcard', function () {
    return redirect()->route('vcard.index', [], 301);
});

// Challenge assessment persistence (auth required)
Route::middleware('auth')->post('/challenge/40-days-challenge/assessment', function (Illuminate\Http\Request $request) {
    $data = $request->validate([
        'name' => 'nullable|string|max:255',
        'age' => 'nullable|integer|min:1|max:120',
        'gender' => 'nullable|string|in:Pria,Wanita',
        'childhood' => 'nullable|string|in:active,labor,sedentary',
        'latestDistance' => 'required|numeric|in:5,10,21.1',
        'timeMin' => 'required|integer|min:1|max:600',
        'timeSec' => 'nullable|integer|min:0|max:59',
        'weeklyVolume' => 'nullable|numeric|min:0|max:1000',
        'targetDistance' => 'nullable|string|in:5k,10k,hm,fm',
        'goalDescription' => 'nullable|string|max:255',
    ]);

    /** @var \App\Models\User $user */
    $user = $request->user();

    // Update user fields (non-destructive)
    $updates = [];
    if (! empty($data['name'])) {
        $updates['name'] = $data['name'];
    }
    if (! empty($data['gender'])) {
        $updates['gender'] = strtolower($data['gender']) === 'wanita' ? 'female' : 'male';
    }
    if (isset($data['weeklyVolume'])) {
        $updates['weekly_volume'] = $data['weeklyVolume'];
    }

    // Prepare audit entry
    $audit = $user->audit_history ?? [];
    $audit[] = [
        'at' => now()->toISOString(),
        'actor' => $user->id,
        'context' => '40-days-challenge-assessment',
        'form' => $data,
    ];

    $updates['audit_history'] = $audit;

    // Persist
    if (! empty($updates)) {
        $user->update($updates);
    }

    return response()->json(['ok' => true]);
})->name('challenge.40days.assessment');

use App\Http\Controllers\ChallengeController;

Route::post('/challenge/join', [ChallengeController::class, 'join'])->name('challenge.join');
Route::post('/challenge/send-otp', [ChallengeController::class, 'sendOtp'])->name('challenge.send-otp');
Route::post('/challenge/verify-otp', [ChallengeController::class, 'verifyOtp'])->name('challenge.verify-otp');

Route::get('/challenge/leaderboard', [ChallengeController::class, 'index'])->name('challenge.index');
Route::get('/challenge/submit', [ChallengeController::class, 'create'])->name('challenge.create');

Route::middleware('auth')->group(function () {
    // Route::get('/challenge/submit', [ChallengeController::class, 'create'])->name('challenge.create'); // Moved to public
    Route::post('/challenge/submit', [ChallengeController::class, 'store'])->name('challenge.store');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/challenge/approval', [App\Http\Controllers\AdminChallengeController::class, 'index'])->name('challenge.index');
    Route::post('/challenge/approve/{id}', [App\Http\Controllers\AdminChallengeController::class, 'approve'])->name('challenge.approve');
    Route::post('/challenge/reject/{id}', [App\Http\Controllers\AdminChallengeController::class, 'reject'])->name('challenge.reject');
    Route::post('/challenge/sync-strava', [App\Http\Controllers\AdminChallengeController::class, 'syncStrava'])->name('challenge.sync-strava');
    Route::get('/challenge/sync-strava', function () {
        return redirect()->route('admin.challenge.index');
    });

    // Strava Config
    Route::get('/strava/config', [App\Http\Controllers\Admin\StravaConfigController::class, 'index'])->name('strava.config');
    Route::post('/strava/config', [App\Http\Controllers\Admin\StravaConfigController::class, 'update'])->name('strava.update');

    // Pages Management
    Route::resource('pages', App\Http\Controllers\Admin\PageController::class);
    
    // Homepage Content Management
    Route::get('/homepage/content', [App\Http\Controllers\Admin\HomepageContentController::class, 'index'])->name('homepage.content');
    Route::post('/homepage/content', [App\Http\Controllers\Admin\HomepageContentController::class, 'update'])->name('homepage.content.update');
});

// Public routes
Route::get('/v-card', [App\Http\Controllers\VCardController::class, 'index'])->name('vcard.index');
Route::get('/v-card.html', function () {
    return redirect()->route('vcard.index');
});

// Tools Landing Page
Route::get('/tools', function () {
    return view('tools.index');
})->name('tools.index');

Route::get('/tools/calculator', [App\Http\Controllers\CalculatorController::class, 'index'])->name('calculator');
Route::get('/tools/form-analyzer', [App\Http\Controllers\FormAnalyzerController::class, 'index'])->name('tools.form-analyzer');
Route::post('/tools/form-analyzer/analyze', [App\Http\Controllers\FormAnalyzerController::class, 'analyze'])->name('tools.form-analyzer.analyze');
Route::get('/tools/buat-rute-lari', function () {
    return view('tools.buat-rute-lari', [
        'withSidebar' => true,
    ]);
})->name('tools.buat-rute-lari');
Route::middleware('auth')->post('/tools/buat-rute-lari/strava-upload', [App\Http\Controllers\CalendarController::class, 'uploadRouteToStrava'])->name('tools.buat-rute-lari.strava-upload');
Route::middleware('auth')->post('/tools/buat-rute-lari/strava-authorize-and-post', [App\Http\Controllers\CalendarController::class, 'authorizeAndPostRouteToStrava'])->name('tools.buat-rute-lari.strava-authorize-and-post');
Route::get('/tools/pace-pro', [App\Http\Controllers\PaceProController::class, 'index'])->name('tools.pace-pro');
Route::get('/tools/pace-pro/gpx/{masterGpx}', [App\Http\Controllers\PaceProController::class, 'gpx'])->name('tools.pace-pro.gpx');
Route::get('/calendar', [App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.public');
Route::get('/calendar/events-proxy', [App\Http\Controllers\CalendarController::class, 'getEvents'])->name('calendar.events.proxy');
Route::middleware('auth')->group(function () {
    Route::get('/calendar/strava/connect', [App\Http\Controllers\CalendarController::class, 'stravaConnect'])->name('calendar.strava.connect');
    Route::get('/calendar/strava/callback', [App\Http\Controllers\CalendarController::class, 'stravaCallback'])->name('calendar.strava.callback');
});
Route::post('/calendar/ai-analysis', [App\Http\Controllers\CalendarController::class, 'getAiAnalysis'])->name('calendar.ai.analysis');

// Pacer listing and profile
Route::get('/pacers', [App\Http\Controllers\PacerController::class, 'index'])->name('pacer.index');
Route::get('/pacer/{slug}', [App\Http\Controllers\PacerController::class, 'show'])->name('pacer.show');
Route::middleware(['auth', 'role:runner'])->post('/pacer/{slug}/book', [App\Http\Controllers\PacerBookingController::class, 'store'])->name('pacer.bookings.store');
Route::middleware(['auth', 'role:runner'])->get('/pacer-bookings/{booking}/pay', [App\Http\Controllers\PacerBookingController::class, 'pay'])->name('pacer.bookings.pay');
Route::middleware('auth')->post('/pacer-bookings/{booking}/confirm', [App\Http\Controllers\PacerBookingController::class, 'confirm'])->name('pacer.bookings.confirm');
Route::middleware(['auth', 'role:runner'])->post('/pacer-bookings/{booking}/complete', [App\Http\Controllers\PacerBookingController::class, 'complete'])->name('pacer.bookings.complete');
Route::middleware(['auth', 'role:runner'])->get('/pacer-bookings/my', [App\Http\Controllers\PacerBookingDashboardController::class, 'my'])->name('runner.pacer-bookings.my');
Route::middleware('auth')->get('/pacer-bookings/inbox', [App\Http\Controllers\PacerBookingDashboardController::class, 'inbox'])->name('pacer.bookings.inbox');
Route::get('/pacer-register', [App\Http\Controllers\PacerRegistrationController::class, 'create'])->name('pacer.register');
Route::post('/pacer-register', [App\Http\Controllers\PacerRegistrationController::class, 'store'])->name('pacer.register.store');
Route::get('/pacer-otp', function (Illuminate\Http\Request $request) {
    return view('pacer.otp');
})->name('pacer.otp');
Route::post('/pacer-otp', function (Illuminate\Http\Request $request) {
    $data = $request->validate(['user_id' => 'required|integer', 'code' => 'required|string|size:6']);
    $token = App\Models\OtpToken::where('user_id', $data['user_id'])->where('code', $data['code'])->where('used', false)->first();
    if (! $token || $token->expires_at->isPast()) {
        return back()->with('success', 'Kode OTP tidak valid atau kedaluwarsa');
    }
    $token->update(['used' => true]);
    $user = App\Models\User::findOrFail($data['user_id']);
    Illuminate\Support\Facades\Auth::login($user);
    $user->update(['is_active' => true]);

    return redirect()->route('dashboard')->with('success', 'Verifikasi berhasil!');
})->name('pacer.otp.verify');

// Midtrans webhook for pacer bookings (no auth)
Route::post('/pacer-bookings/webhook', [App\Http\Controllers\PacerBookingWebhookController::class, 'handle'])->name('pacer.bookings.webhook');

// Runner Profile (Public) - avoid conflicts with runner dashboard/calendar routes
Route::get('/runner/{username}', [App\Http\Controllers\RunnerProfileController::class, 'show'])
    ->where('username', '^(?!dashboard$)(?!calendar$)(?!programs$)[A-Za-z0-9._-]+$')
    ->name('runner.profile.show');

// Coach Registration Routes
Route::get('/coach-register', [App\Http\Controllers\CoachRegistrationController::class, 'create'])->name('coach.register');
Route::post('/coach-register', [App\Http\Controllers\CoachRegistrationController::class, 'store'])->name('coach.register.store');

// Add Image Proxy Route
Route::get('/image-proxy', function (Illuminate\Http\Request $request) {
    $url = $request->query('url');
    if (! $url) {
        abort(404);
    }

    try {
        $response = Illuminate\Support\Facades\Http::withoutVerifying()->get($url);

        return response($response->body())
            ->header('Content-Type', $response->header('Content-Type'))
            ->header('Access-Control-Allow-Origin', '*');
    } catch (\Exception $e) {
        abort(500);
    }
})->name('image.proxy');

Route::get('/realistic-running-program', function () {
    return view('programs.design');
})->name('programs.realistic');
Route::get('/coach-ladder-program', function () {
    return view('coach.hub');
})->name('coach.hub');
Route::get('/programs', [App\Http\Controllers\PublicProgramController::class, 'index'])->name('programs.index');
Route::get('/programs/{slug}', [App\Http\Controllers\PublicProgramController::class, 'show'])->name('programs.show');

// Public Coach Listing
Route::get('/coaches', [App\Http\Controllers\CoachListController::class, 'index'])->name('coaches.index');

// Public Marketplace
Route::get('/marketplace', [App\Http\Controllers\Marketplace\MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/marketplace/product/{slug}', [App\Http\Controllers\Marketplace\MarketplaceController::class, 'show'])->name('marketplace.show');

// Newsletter
Route::post('/subscribe', [App\Http\Controllers\NewsletterController::class, 'store'])->name('newsletter.subscribe');

// Public race results API (must be before /events/{slug} to avoid route conflict)
Route::get('/api/events/{slug}/results', [App\Http\Controllers\RaceResultController::class, 'index'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('api.events.results');

// Public event routes (Kalender Lari)
Route::get('/jadwal-lari', [App\Http\Controllers\PublicRunningEventController::class, 'index'])->name('events.index');
Route::get('/event-lari-di-{city}', [App\Http\Controllers\PublicRunningEventController::class, 'cityArchive'])->name('events.city');
Route::get('/event-lari/{slug}', [App\Http\Controllers\PublicRunningEventController::class, 'show'])->name('running-event.detail');

// Legacy public event routes (keep if needed or redirect)
Route::get('/events/{slug}', [App\Http\Controllers\PublicEventController::class, 'show'])->name('events.show');
Route::get('/legacy-events', [App\Http\Controllers\PublicEventController::class, 'index'])->name('legacy.events.index'); // Renamed old one if exists



Route::get('/events/{slug}/register', [App\Http\Controllers\EventRegistrationController::class, 'show'])->name('events.register');
Route::post('/events/{slug}/register', [App\Http\Controllers\EventRegistrationController::class, 'store'])->middleware('throttle:5,1')->name('events.register.store');
Route::post('/events/{slug}/register/coupon', [App\Http\Controllers\EventRegistrationController::class, 'applyCoupon'])->name('events.register.coupon');
Route::post('/events/{slug}/register/quota', [App\Http\Controllers\EventRegistrationController::class, 'checkQuota'])->name('events.register.quota');

// EO Landing Page
Route::get('/event-organizer', function () {
    return view('eo.landing');
})->name('eo.landing');

// Public Pages
Route::get('/p/{slug}', [App\Http\Controllers\PageController::class, 'show'])->name('pages.show');

// Public Blog
Route::get('/blog', [App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/kategori/{slug}', [App\Http\Controllers\BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{slug}', [App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');

// Public API: Upcoming events for home page
Route::get('/api/events/upcoming', function () {
    try {
        if (! Illuminate\Support\Facades\Schema::hasTable('events')) {
            return response()->json([]);
        }

        $events = App\Models\Event::select('name', 'slug', 'start_at', 'location_name', 'created_at', 'user_id', 'external_registration_link')
            ->orderByRaw('COALESCE(start_at, created_at) ASC')
            ->limit(4)
            ->get()
            ->map(function ($e) {
                $dt = $e->start_at ?: $e->created_at;

                return [
                    'name' => $e->name,
                    'slug' => $e->slug ?: Illuminate\Support\Str::slug($e->name),
                    'is_eo' => $e->is_eo,
                    'date' => optional($dt)->format('Y-m-d'),
                    'time' => optional($dt)->format('H:i'),
                    'location' => $e->location_name,
                    'url' => $e->public_url,
                ];
            });

        return response()->json($events);
    } catch (\Throwable $e) {
        return response()->json([]);
    }
})->name('api.events.upcoming');

// Public API: Latest blog articles for home page
Route::get('/api/blog/latest', function () {
    try {
        if (! Illuminate\Support\Facades\Schema::hasTable('articles')) {
            return response()->json([]);
        }

        return \App\Models\Article::published()
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->limit(3)
            ->get()
            ->map(function ($a) {
                $img = $a->featured_image;
                if ($img && ! str_starts_with($img, 'http')) {
                    $img = asset('storage/' . ltrim($img, '/'));
                }

                $dt = $a->published_at ?: $a->created_at;

                return [
                    'title' => $a->title,
                    'slug' => $a->slug,
                    'excerpt' => $a->excerpt,
                    'date' => optional($dt)->format('Y-m-d'),
                    'image' => $img,
                    'url' => $a->canonical_url ?: route('blog.show', $a->slug),
                ];
            });
    } catch (\Throwable $e) {
        return response()->json([]);
    }
})->name('api.blog.latest');

// Public API: Cyberpunk Leaderboard (40days)
Route::get('/api/leaderboard/40days', [\App\Http\Controllers\LeaderboardController::class, 'index'])
    ->name('api.leaderboard.40days');
Route::get('/api/club/members', [\App\Http\Controllers\LeaderboardController::class, 'clubMembers'])
    ->name('api.club.members');

// Leaderboard UI
Route::get('/leaderboard/40days', function () {
    return view('leaderboard.cyberpunk');
})->name('leaderboard.cyberpunk');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);
    Route::get('/register', [App\Http\Controllers\Auth\AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [App\Http\Controllers\Auth\AuthController::class, 'register']);

    // Runner special registration
    Route::get('/runner-register', [App\Http\Controllers\Runner\RunnerRegistrationController::class, 'create'])->name('runner.register');
    Route::post('/runner-register', [App\Http\Controllers\Runner\RunnerRegistrationController::class, 'store'])->name('runner.register.store');
    Route::get('/forgot-password', [App\Http\Controllers\Auth\AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [App\Http\Controllers\Auth\AuthController::class, 'sendResetLink'])->name('password.email');

    // Google Auth
    Route::get('auth/google', [App\Http\Controllers\Auth\AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [App\Http\Controllers\Auth\AuthController::class, 'handleGoogleCallback']);

    // Public Pages
    Route::get('/p/{slug}', [App\Http\Controllers\PageController::class, 'show'])->name('pages.show');
});

Route::middleware('auth')->group(function () {
    // Challenge / Leaderboard Routes
    // Route::get('/challenge/submit', [App\Http\Controllers\ChallengeController::class, 'create'])->name('challenge.create'); // Moved to public
    Route::post('/challenge/submit', [App\Http\Controllers\ChallengeController::class, 'store'])->name('challenge.store');

    Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');

    // Profile routes (accessible by all authenticated users)
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // User list routes (accessible by all authenticated users)
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('/users/runners', function (Request $request) {
        $request->merge(['role' => 'runner']);

        return app(App\Http\Controllers\UserController::class)->index($request);
    })->name('users.runners');
    Route::get('/users/coaches', function (Request $request) {
        $request->merge(['role' => 'coach']);

        return app(App\Http\Controllers\UserController::class)->index($request);
    })->name('users.coaches');

    // Follow routes
    Route::post('/follow/{user}', [App\Http\Controllers\FollowController::class, 'follow'])->name('follow');
    Route::post('/unfollow/{user}', [App\Http\Controllers\FollowController::class, 'unfollow'])->name('unfollow');

    // Chat routes
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{user}', [App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{user}', [App\Http\Controllers\ChatController::class, 'store'])->name('chat.store');
    Route::get('/api/chat/conversations', [App\Http\Controllers\ChatController::class, 'getConversations'])->name('chat.conversations');
    Route::get('/api/chat/{userId}/messages', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');

    // Feed routes
    Route::get('/feed', [App\Http\Controllers\FeedController::class, 'index'])->name('feed.index');
    Route::post('/feed', [App\Http\Controllers\FeedController::class, 'store'])->name('feed.store');
    Route::delete('/feed/{post}', [App\Http\Controllers\FeedController::class, 'destroy'])->name('feed.destroy');
    Route::post('/feed/{post}/like', [App\Http\Controllers\FeedController::class, 'like'])->name('feed.like');
    Route::post('/feed/{post}/unlike', [App\Http\Controllers\FeedController::class, 'unlike'])->name('feed.unlike');
    Route::post('/feed/{post}/comment', [App\Http\Controllers\FeedController::class, 'comment'])->name('feed.comment');

    // Notification routes
    Route::middleware(['role:runner|coach|eo|admin'])->group(function () {
        Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/api/notifications/unread', [App\Http\Controllers\NotificationController::class, 'getUnread'])->name('notifications.unread');
        Route::post('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    });

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        // Event Calendar Management
        Route::get('events/import', [App\Http\Controllers\Admin\EventController::class, 'import'])->name('events.import');
        Route::post('events/import', [App\Http\Controllers\Admin\EventController::class, 'storeImport'])->name('events.import.store');
        Route::post('events/sync', [App\Http\Controllers\Admin\EventController::class, 'sync'])->name('events.sync');
        Route::resource('events', App\Http\Controllers\Admin\EventController::class);
        Route::resource('master-gpx', App\Http\Controllers\Admin\MasterGpxController::class)->except(['show']);

        // User Management
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::get('users/{user}/transactions', [App\Http\Controllers\Admin\UserController::class, 'transactions'])->name('users.transactions');
    Route::post('users/{user}/wallet', [App\Http\Controllers\Admin\UserController::class, 'adjustWallet'])->name('users.wallet');
    Route::post('users/{user}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('users/{user}/impersonate', [App\Http\Controllers\Admin\UserController::class, 'impersonate'])->name('users.impersonate');

        Route::get('/marketplace/settings', [App\Http\Controllers\Admin\MarketplaceSettingsController::class, 'index'])->name('marketplace.settings');
        Route::post('/marketplace/settings', [App\Http\Controllers\Admin\MarketplaceSettingsController::class, 'update'])->name('marketplace.settings.update');
        Route::resource('marketplace/categories', App\Http\Controllers\Admin\MarketplaceCategoryController::class)->names('marketplace.categories');
        Route::resource('marketplace/brands', App\Http\Controllers\Admin\MarketplaceBrandController::class)->names('marketplace.brands');
        Route::get('/marketplace/auctions', [App\Http\Controllers\Admin\MarketplaceAuctionController::class, 'index'])->name('marketplace.auctions.index');
        Route::get('/marketplace/auctions/{product}', [App\Http\Controllers\Admin\MarketplaceAuctionController::class, 'show'])->name('marketplace.auctions.show');
        Route::post('/marketplace/auctions/{product}/cancel', [App\Http\Controllers\Admin\MarketplaceAuctionController::class, 'cancel'])->name('marketplace.auctions.cancel');
        Route::post('/marketplace/auctions/{product}/finalize', [App\Http\Controllers\Admin\MarketplaceAuctionController::class, 'finalize'])->name('marketplace.auctions.finalize');
        Route::get('/marketplace/consignments', [App\Http\Controllers\Admin\MarketplaceConsignmentController::class, 'index'])->name('marketplace.consignments.index');
        Route::post('/marketplace/consignments/{intake}/received', [App\Http\Controllers\Admin\MarketplaceConsignmentController::class, 'markReceived'])->name('marketplace.consignments.received');
        Route::post('/marketplace/consignments/{intake}/listed', [App\Http\Controllers\Admin\MarketplaceConsignmentController::class, 'markListed'])->name('marketplace.consignments.listed');

        // Page Management
        Route::resource('pages', App\Http\Controllers\Admin\PageController::class);

        // Menu Management
        Route::resource('menus', App\Http\Controllers\Admin\MenuController::class)->except(['show', 'create']);
        Route::post('menus/{menu}/items', [App\Http\Controllers\Admin\MenuController::class, 'addItem'])->name('menus.items.store');
        Route::put('menus/items/{item}', [App\Http\Controllers\Admin\MenuController::class, 'updateItem'])->name('menus.items.update');
        Route::delete('menus/items/{item}', [App\Http\Controllers\Admin\MenuController::class, 'deleteItem'])->name('menus.items.destroy');
        Route::post('menus/{menu}/reorder', [App\Http\Controllers\Admin\MenuController::class, 'reorder'])->name('menus.reorder');

        // Blog Management
        Route::resource('blog/articles', App\Http\Controllers\Admin\Blog\ArticleController::class)->names('blog.articles');
        Route::resource('blog/categories', App\Http\Controllers\Admin\Blog\CategoryController::class)->names('blog.categories');
        Route::post('blog/images/upload', [App\Http\Controllers\Admin\Blog\ImageController::class, 'upload'])->name('blog.images.upload');
        Route::get('blog/import', [App\Http\Controllers\Admin\Blog\ImportController::class, 'index'])->name('blog.import');
        Route::post('blog/import', [App\Http\Controllers\Admin\Blog\ImportController::class, 'store'])->name('blog.import.store');
        
        // Media Library
        Route::get('blog/media', [App\Http\Controllers\Admin\Blog\MediaController::class, 'index'])->name('blog.media.index');
        Route::post('blog/media', [App\Http\Controllers\Admin\Blog\MediaController::class, 'store'])->name('blog.media.store');
        Route::delete('blog/media/{media}', [App\Http\Controllers\Admin\Blog\MediaController::class, 'destroy'])->name('blog.media.destroy');

        Route::post('/leaderboard/sync', function () {
            Illuminate\Support\Facades\Artisan::call('leaderboard:sync');

            return response()->json(['ok' => true]);
        })->name('leaderboard.sync');

        // Transaction Management
        Route::get('transactions', [App\Http\Controllers\Admin\TransactionController::class, 'index'])->name('transactions.index');
        Route::post('transactions/withdrawals/{withdrawal}/approve', [App\Http\Controllers\Admin\TransactionController::class, 'approveWithdrawal'])->name('transactions.withdrawals.approve');
        Route::post('transactions/withdrawals/{withdrawal}/reject', [App\Http\Controllers\Admin\TransactionController::class, 'rejectWithdrawal'])->name('transactions.withdrawals.reject');

        // Integration Settings
        Route::get('/integration-settings', [App\Http\Controllers\Admin\IntegrationSettingsController::class, 'index'])->name('integration.settings');
        Route::post('/integration-settings', [App\Http\Controllers\Admin\IntegrationSettingsController::class, 'update'])->name('integration.settings.update');

        // V-Card Settings
        Route::get('/vcard-settings', [App\Http\Controllers\Admin\VCardSettingsController::class, 'index'])->name('vcard.settings');
        Route::post('/vcard-settings', [App\Http\Controllers\Admin\VCardSettingsController::class, 'update'])->name('vcard.settings.update');

        // SEO Settings
        Route::get('/seo-settings', [App\Http\Controllers\Admin\SeoSettingsController::class, 'index'])->name('seo.settings');
        Route::post('/seo-settings', [App\Http\Controllers\Admin\SeoSettingsController::class, 'update'])->name('seo.settings.update');
    });

    // Runner routes
    Route::middleware('role:runner')->prefix('runner')->name('runner.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/strava/connect', [App\Http\Controllers\Runner\StravaController::class, 'connect'])->name('strava.connect');
        Route::get('/strava/callback', [App\Http\Controllers\Runner\StravaController::class, 'callback'])->name('strava.callback');
        Route::post('/strava/sync', [App\Http\Controllers\Runner\StravaController::class, 'sync'])->name('strava.sync');
        Route::get('/strava/activities/{stravaActivityId}/details', [App\Http\Controllers\Runner\StravaController::class, 'activityDetails'])->name('strava.activities.details');
        Route::get('/strava/activities/{stravaActivityId}/streams', [App\Http\Controllers\Runner\StravaController::class, 'activityStreams'])->name('strava.activities.streams');
        // Challenge Programs listing (filtered)
        Route::get('/programs/challenges', function (Illuminate\Http\Request $request) {
            $request->merge(['challenge' => 1]);

            return app(App\Http\Controllers\PublicProgramController::class)->index($request);
        })->name('programs.challenges');
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
        Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
        Route::get('/calendar/workout-plans', [CalendarController::class, 'workoutPlans'])->name('calendar.workout-plans');
        Route::post('/calendar/update-session-status', [CalendarController::class, 'updateSessionStatus'])->name('calendar.update-session-status');
        Route::post('/calendar/custom-workout', [CalendarController::class, 'storeCustomWorkout'])->name('calendar.custom-workout.store');
        Route::delete('/calendar/custom-workout/{customWorkout}', [CalendarController::class, 'deleteCustomWorkout'])->name('calendar.custom-workout.delete');
        Route::delete('/calendar/enrollment/{enrollment}', [CalendarController::class, 'deleteEnrollment'])->name('calendar.enrollment.delete');
        Route::post('/calendar/enrollment/{enrollment}/delete', [CalendarController::class, 'deleteEnrollment'])->name('calendar.enrollment.delete.post');
        Route::post('/calendar/reset-plan', [CalendarController::class, 'resetPlan'])->name('calendar.reset-plan');
        Route::post('/calendar/reset-plan-list', [CalendarController::class, 'resetPlanList'])->name('calendar.reset-plan-list');
        Route::post('/calendar/apply-program', [CalendarController::class, 'applyProgram'])->name('calendar.apply-program');
        Route::post('/calendar/restore-program', [CalendarController::class, 'restoreProgram'])->name('calendar.restore-program');
        Route::post('/calendar/update-pb', [CalendarController::class, 'updatePb'])->name('calendar.update-pb');
        Route::post('/calendar/update-weekly-target', [CalendarController::class, 'updateWeeklyTarget'])->name('calendar.update-weekly-target');
        Route::post('/calendar/reset-plan-list', [CalendarController::class, 'resetPlanList'])->name('calendar.reset-plan-list');
        Route::post('/calendar/reschedule', [CalendarController::class, 'reschedule'])->name('calendar.reschedule');
        Route::post('/calendar/reschedule-program', [CalendarController::class, 'rescheduleProgram'])->name('calendar.reschedule-program');
        Route::get('/calendar/weekly-volume', [CalendarController::class, 'weeklyVolume'])->name('calendar.weekly-volume');

        // Program purchase & enrollment
        Route::post('/programs/{program}/purchase', [App\Http\Controllers\Runner\ProgramPurchaseController::class, 'purchase'])->name('programs.purchase');
        Route::post('/programs/{program}/enroll-free', [App\Http\Controllers\Runner\ProgramEnrollmentController::class, 'enrollFree'])->name('programs.enroll-free');

        // Program reviews
        Route::post('/programs/{program}/reviews', [App\Http\Controllers\Runner\ProgramReviewController::class, 'store'])->name('programs.reviews.store');

        // Generate program (Daniels Formula)
        Route::post('/programs/generate', [App\Http\Controllers\Runner\GenerateProgramController::class, 'generate'])->name('programs.generate');
    });

    // Marketplace routes (accessible by all authenticated users)
    Route::middleware(['role:runner,coach,eo,admin'])->prefix('marketplace')->name('marketplace.')->group(function () {
        // Seller Management
        Route::resource('seller/products', App\Http\Controllers\Marketplace\ProductController::class)->names('seller.products');

        Route::post('/product/{slug}/bid', [App\Http\Controllers\Marketplace\AuctionController::class, 'bid'])->name('auction.bid')->middleware('throttle:20,1');

        // Cart routes
        Route::get('/cart', [App\Http\Controllers\CartController::class, 'index'])->name('cart.index');

        // Checkout routes
        Route::post('/checkout/init', [App\Http\Controllers\Marketplace\CheckoutController::class, 'init'])->name('checkout.init');
        Route::get('/checkout/{order}/pay', [App\Http\Controllers\Marketplace\CheckoutController::class, 'pay'])->name('checkout.pay');

        // My Orders
        Route::get('/orders', [App\Http\Controllers\Marketplace\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [App\Http\Controllers\Marketplace\OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/shipped', [App\Http\Controllers\Marketplace\OrderController::class, 'markShipped'])->name('orders.shipped');
        Route::post('/orders/{order}/completed', [App\Http\Controllers\Marketplace\OrderController::class, 'markCompleted'])->name('orders.completed');
        Route::post('/cart/add/{program}', [App\Http\Controllers\CartController::class, 'add'])->name('cart.add');
        Route::delete('/cart/{cart}', [App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove');
        Route::put('/cart/{cart}', [App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart', [App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear');
        Route::get('/cart/count', [App\Http\Controllers\CartController::class, 'count'])->name('cart.count');

        // Checkout routes
        Route::get('/checkout', [App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/checkout', [App\Http\Controllers\CheckoutController::class, 'store'])->name('checkout.store');

        // Order routes
        Route::get('/orders', [App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/invoice', [App\Http\Controllers\OrderController::class, 'invoice'])->name('orders.invoice');
    });

    // Coach routes
    Route::middleware('role:coach')->prefix('coach')->name('coach.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Coach\DashboardController::class, 'index'])->name('dashboard');

        // Master Workouts
        Route::resource('master-workouts', App\Http\Controllers\Coach\MasterWorkoutController::class);

        // Custom Workouts
        Route::resource('custom-workouts', App\Http\Controllers\Coach\CustomWorkoutController::class);

        Route::resource('programs', App\Http\Controllers\Coach\ProgramController::class);
        Route::post('/programs/generate-template', [App\Http\Controllers\Coach\ProgramController::class, 'generateTemplate'])->name('programs.generate-template');
        Route::post('/programs/import-json', [App\Http\Controllers\Coach\ProgramController::class, 'importJson'])->name('programs.import-json');
        Route::post('/programs/generate-vdot', [App\Http\Controllers\Coach\ProgramController::class, 'generateVDOT'])->name('programs.generate-vdot');
        Route::post('/programs/{program}/publish', [App\Http\Controllers\Coach\ProgramController::class, 'publish'])->name('programs.publish');
        Route::post('/programs/{program}/unpublish', [App\Http\Controllers\Coach\ProgramController::class, 'unpublish'])->name('programs.unpublish');
        Route::get('/programs/{program}/export-json', [App\Http\Controllers\Coach\ProgramController::class, 'exportJson'])->name('programs.export-json');

        // Withdrawals
        Route::get('/withdrawals', [App\Http\Controllers\Coach\WithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('/withdrawals/request', [App\Http\Controllers\Coach\WithdrawalController::class, 'request'])->name('withdrawals.request');

        // Athlete Monitoring
        Route::get('/athletes', [App\Http\Controllers\Coach\AthleteController::class, 'index'])->name('athletes.index');
        Route::get('/athletes/{enrollment}', [App\Http\Controllers\Coach\AthleteController::class, 'show'])->name('athletes.show');
        Route::get('/athletes/{enrollment}/events', [App\Http\Controllers\Coach\AthleteController::class, 'calendarEvents'])->name('athletes.events');
        Route::get('/athletes/{enrollment}/strava/activities/{stravaActivityId}/details', [App\Http\Controllers\Coach\AthleteController::class, 'stravaActivityDetails'])->name('athletes.strava.activities.details');
        Route::get('/athletes/{enrollment}/strava/activities/{stravaActivityId}/streams', [App\Http\Controllers\Coach\AthleteController::class, 'stravaActivityStreams'])->name('athletes.strava.activities.streams');
        Route::post('/athletes/{enrollment}/feedback', [App\Http\Controllers\Coach\AthleteController::class, 'storeFeedback'])->name('athletes.feedback');
        Route::post('/athletes/{enrollment}/race', [App\Http\Controllers\Coach\AthleteController::class, 'storeRace'])->name('athletes.race.store');
        Route::post('/athletes/{enrollment}/workout', [App\Http\Controllers\Coach\AthleteController::class, 'storeWorkout'])->name('athletes.workout.store');
        Route::put('/athletes/{enrollment}/workout/{customWorkout}', [App\Http\Controllers\Coach\AthleteController::class, 'updateWorkout'])->name('athletes.workout.update');
        Route::delete('/athletes/{enrollment}/workout/{customWorkout}', [App\Http\Controllers\Coach\AthleteController::class, 'destroyWorkout'])->name('athletes.workout.destroy');
        Route::post('/athletes/{enrollment}/update-weekly-target', [App\Http\Controllers\Coach\AthleteController::class, 'updateWeeklyTarget'])->name('athletes.update-weekly-target');
    });

    // EO routes
    Route::middleware('role:eo')->prefix('eo')->name('eo.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\EO\DashboardController::class, 'index'])->name('dashboard');

        // Membership routes
        Route::get('/membership/packages', [App\Http\Controllers\EO\MembershipController::class, 'index'])->name('packages.index');
        Route::post('/membership/select', [App\Http\Controllers\EO\MembershipController::class, 'selectPackage'])->name('membership.select');
        Route::get('/membership/payment/{transaction}', [App\Http\Controllers\EO\MembershipController::class, 'payment'])->name('membership.payment');

        // Event management
        Route::post('events/upload-media', [App\Http\Controllers\EO\EventController::class, 'uploadMedia'])->name('events.upload-media');
        Route::resource('events', App\Http\Controllers\EO\EventController::class);
        Route::get('events/{event}/preview', [App\Http\Controllers\EO\EventController::class, 'preview'])->name('events.preview');
        Route::get('events/{event}/participants', [App\Http\Controllers\EO\EventController::class, 'participants'])->name('events.participants');
        Route::delete('events/{event}/participants/{participant}', [App\Http\Controllers\EO\EventController::class, 'destroyParticipant'])->name('events.participants.destroy');
        Route::get('events/{event}/participants/export', [App\Http\Controllers\EO\EventController::class, 'exportParticipants'])->name('events.participants.export');
        Route::post('events/{event}/participants/{participant}/status', [App\Http\Controllers\EO\EventController::class, 'updateParticipantStatus'])->name('events.participants.status');
        Route::post('events/{event}/transactions/{transaction_id}/payment-status', [App\Http\Controllers\EO\EventController::class, 'updatePaymentStatus'])->name('events.transactions.payment-status');
        Route::post('events/{event}/packages', [App\Http\Controllers\EO\EventPackageController::class, 'store'])->name('events.packages.store');
        Route::put('events/packages/{package}', [App\Http\Controllers\EO\EventPackageController::class, 'update'])->name('events.packages.update');
        Route::delete('events/packages/{package}', [App\Http\Controllers\EO\EventPackageController::class, 'destroy'])->name('events.packages.destroy');
        Route::post('events/{event}/coupons', [App\Http\Controllers\EO\CouponController::class, 'store'])->name('events.coupons.store');
        Route::delete('events/coupons/{coupon}', [App\Http\Controllers\EO\CouponController::class, 'destroy'])->name('events.coupons.destroy');

        // Race results management
        Route::get('events/{event}/results', [App\Http\Controllers\EO\RaceResultController::class, 'index'])->name('events.results');
        Route::post('events/{event}/results', [App\Http\Controllers\EO\RaceResultController::class, 'store'])->name('events.results.store');
        Route::get('events/{event}/results/{raceResult}', [App\Http\Controllers\EO\RaceResultController::class, 'show'])->name('events.results.show');
        Route::put('events/{event}/results/{raceResult}', [App\Http\Controllers\EO\RaceResultController::class, 'update'])->name('events.results.update');
        Route::delete('events/{event}/results/{raceResult}', [App\Http\Controllers\EO\RaceResultController::class, 'destroy'])->name('events.results.destroy');
        Route::post('events/{event}/results/upload-csv', [App\Http\Controllers\EO\RaceResultController::class, 'uploadCsv'])->name('events.results.upload-csv');
    });

    // Wallet routes (accessible by all authenticated users)
    Route::get('/wallet', [App\Http\Controllers\WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/topup', [App\Http\Controllers\WalletController::class, 'topup'])->name('wallet.topup');
    Route::post('/wallet/withdraw', [App\Http\Controllers\WalletController::class, 'withdraw'])->name('wallet.withdraw');
});

Route::get('/dashboard', function () {
    if (! Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('home');
    }
    $user = Illuminate\Support\Facades\Auth::user();

    return match ($user->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'coach' => redirect()->route('coach.dashboard'),
        'runner' => redirect()->route('runner.dashboard'),
        'eo' => redirect()->route('eo.dashboard'),
        default => redirect()->route('home'),
    };
})->middleware('auth')->name('dashboard');

// Midtrans webhook (no auth required)
Route::post('/wallet/topup/callback', [App\Http\Controllers\WalletController::class, 'topupCallback'])->name('wallet.topup.callback');
Route::post('/events/transactions/webhook', [App\Http\Controllers\EventTransactionWebhookController::class, 'handle'])->name('events.transactions.webhook');
Route::post('/membership/webhook', [App\Http\Controllers\MembershipWebhookController::class, 'handle'])->name('membership.webhook');
Route::post('/marketplace/webhook', [App\Http\Controllers\Marketplace\WebhookController::class, 'handle'])->name('marketplace.webhook');

Route::get('/run-queue-worker', function () {
    // Security: Only allow admin or secure usage (optional, for now open for debug)
    
    $exitCode = Illuminate\Support\Facades\Artisan::call('queue:work', [
        '--stop-when-empty' => true
    ]);
    
    return 'Worker executed. Output: ' . Illuminate\Support\Facades\Artisan::output();
});
