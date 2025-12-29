<?php

use App\Http\Controllers\Runner\DashboardController;
use App\Http\Controllers\Runner\CalendarController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Services\StravaClubService;

Route::get('/', function (StravaClubService $stravaService) {
    $leaderboard = $stravaService->getLeaderboard();

    $topRunner = \App\Models\User::where('role', 'runner')
        ->withCount(['followers', 'posts'])
        ->orderByDesc('followers_count')
        ->first();

    $topPacer = \App\Models\Pacer::with('user')
        ->orderByDesc('total_races')
        ->first();

    $topCoachData = \App\Models\ProgramEnrollment::selectRaw('programs.coach_id as coach_id, COUNT(*) as students_count')
        ->join('programs', 'program_enrollments.program_id', '=', 'programs.id')
        ->groupBy('programs.coach_id')
        ->orderByDesc('students_count')
        ->first();

    $topCoach = $topCoachData ? \App\Models\User::find($topCoachData->coach_id) : null;

    return view('home.index', compact('leaderboard', 'topRunner', 'topPacer', 'topCoach', 'topCoachData'));
})->name('home');

// Challenge: 40 Days Challenge - reuse realistic program design view with challenge mode
Route::get('/challenge/40-days-challenge', function () {
    return view('programs.design', [
        'challengeMode' => true,
        'challengeProgramId' => 9
    ]);
})->name('challenge.40days');

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
    if (!empty($data['name'])) $updates['name'] = $data['name'];
    if (!empty($data['gender'])) $updates['gender'] = strtolower($data['gender']) === 'wanita' ? 'female' : 'male';
    if (isset($data['weeklyVolume'])) $updates['weekly_volume'] = $data['weeklyVolume'];

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
    if (!empty($updates)) {
        $user->update($updates);
    }

    return response()->json(['ok' => true]);
})->name('challenge.40days.assessment');

Route::post('/challenge/register', function (Illuminate\Http\Request $request) {
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'required|string|max:20',
        'password' => 'required|string|min:6',
    ]);

    // Create User
    $user = \App\Models\User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
        'role' => 'runner',
    ]);

    // Generate OTP
    $code = str_pad((string)random_int(0,999999), 6, '0', STR_PAD_LEFT);
    \App\Models\OtpToken::create([
        'user_id' => $user->id,
        'code' => $code,
        'expires_at' => now()->addMinutes(10),
        'used' => false,
    ]);

    // Send WhatsApp
    \App\Helpers\WhatsApp::send($data['phone'], 'Kode OTP RuangLari Anda: '.$code);

    return response()->json(['ok' => true, 'user_id' => $user->id]);
});

Route::post('/challenge/verify-otp', function (Illuminate\Http\Request $request) {
    $data = $request->validate([
        'user_id' => 'required|exists:users,id',
        'otp' => 'required|string|size:6'
    ]);

    $token = \App\Models\OtpToken::where('user_id', $data['user_id'])
        ->where('code', $data['otp'])
        ->where('used', false)
        ->where('expires_at', '>', now())
        ->first();

    if (!$token) {
        return response()->json(['ok' => false, 'message' => 'OTP Salah atau Kadaluarsa'], 400);
    }

    $token->update(['used' => true]);
    
    $user = \App\Models\User::find($data['user_id']);
    \Illuminate\Support\Facades\Auth::login($user);

    // Auto Join Program 9
    $programId = 9;
    // Check if already enrolled
    $exists = \App\Models\ProgramEnrollment::where('runner_id', $user->id)
        ->where('program_id', $programId)
        ->exists();

    if (!$exists) {
        \App\Models\ProgramEnrollment::create([
            'program_id' => $programId,
            'runner_id' => $user->id,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays(40), // Assuming 40 days
            'payment_status' => 'paid', // Free challenge
        ]);
    }

    return response()->json(['ok' => true, 'redirect' => route('runner.calendar')]);
});

// Public routes
Route::get('/runcalendar', [App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.public');
Route::get('/runcalendar/events-proxy', [App\Http\Controllers\CalendarController::class, 'getEvents'])->name('calendar.events.proxy');
Route::get('/runcalendar/strava/connect', [App\Http\Controllers\CalendarController::class, 'stravaConnect'])->name('calendar.strava.connect');
Route::get('/runcalendar/strava/callback', [App\Http\Controllers\CalendarController::class, 'stravaCallback'])->name('calendar.strava.callback');
Route::post('/runcalendar/ai-analysis', [App\Http\Controllers\CalendarController::class, 'getAiAnalysis'])->name('calendar.ai.analysis');

// Pacer listing and profile
Route::get('/pacers', [App\Http\Controllers\PacerController::class, 'index'])->name('pacer.index');
Route::get('/pacer/{slug}', [App\Http\Controllers\PacerController::class, 'show'])->name('pacer.show');
Route::get('/pacer-register', [App\Http\Controllers\PacerRegistrationController::class, 'create'])->name('pacer.register');
Route::post('/pacer-register', [App\Http\Controllers\PacerRegistrationController::class, 'store'])->name('pacer.register.store');
Route::get('/pacer-otp', function(Illuminate\Http\Request $request){ return view('pacer.otp'); })->name('pacer.otp');
Route::post('/pacer-otp', function(Illuminate\Http\Request $request){
    $data = $request->validate(['user_id'=>'required|integer','code'=>'required|string|size:6']);
    $token = App\Models\OtpToken::where('user_id',$data['user_id'])->where('code',$data['code'])->where('used',false)->first();
    if(!$token || $token->expires_at->isPast()){
        return back()->with('success','Kode OTP tidak valid atau kedaluwarsa');
    }
    $token->update(['used'=>true]);
    $user = App\Models\User::findOrFail($data['user_id']);
    Illuminate\Support\Facades\Auth::login($user);
    return redirect('/dashboard')->with('success','Verifikasi berhasil!');
})->name('pacer.otp.verify');

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
    if (!$url) abort(404);

    try {
        $response = Illuminate\Support\Facades\Http::withoutVerifying()->get($url);
        return response($response->body())
            ->header('Content-Type', $response->header('Content-Type'))
            ->header('Access-Control-Allow-Origin', '*');
    } catch (\Exception $e) {
        abort(500);
    }
})->name('image.proxy');

Route::get('/realistic-running-program', function () { return view('programs.design'); })->name('programs.realistic');
Route::get('/coach-ladder-program', function () { return view('coach.hub'); })->name('coach.hub');
Route::get('/programs', [App\Http\Controllers\PublicProgramController::class, 'index'])->name('programs.index');
Route::get('/programs/{slug}', [App\Http\Controllers\PublicProgramController::class, 'show'])->name('programs.show');

// Public Coach Listing
Route::get('/coaches', [App\Http\Controllers\CoachListController::class, 'index'])->name('coaches.index');

// Public race results API (must be before /events/{slug} to avoid route conflict)
Route::get('/api/events/{slug}/results', [App\Http\Controllers\RaceResultController::class, 'index'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('api.events.results');

// Public event routes
Route::get('/events/{slug}', [App\Http\Controllers\PublicEventController::class, 'show'])->name('events.show');
Route::get('/events', [App\Http\Controllers\PublicEventController::class, 'index'])->name('events.index');
Route::get('/events/{slug}/register', [App\Http\Controllers\EventRegistrationController::class, 'show'])->name('events.register');
Route::post('/events/{slug}/register', [App\Http\Controllers\EventRegistrationController::class, 'store'])->middleware('throttle:5,1')->name('events.register.store');
Route::post('/events/{slug}/register/coupon', [App\Http\Controllers\EventRegistrationController::class, 'applyCoupon'])->name('events.register.coupon');
Route::post('/events/{slug}/register/quota', [App\Http\Controllers\EventRegistrationController::class, 'checkQuota'])->name('events.register.quota');

// Public API: Upcoming events for home page
Route::get('/api/events/upcoming', function () {
    try {
        if (!Illuminate\Support\Facades\Schema::hasTable('events')) {
            return response()->json([]);
        }

        $events = App\Models\Event::select('name','slug','start_at','location_name','created_at')
            ->orderByRaw('COALESCE(start_at, created_at) ASC')
            ->limit(4)
            ->get()
            ->map(function($e){
                $dt = $e->start_at ?: $e->created_at;
                return [
                    'name' => $e->name,
                    'slug' => $e->slug ?: Illuminate\Support\Str::slug($e->name),
                    'date' => optional($dt)->format('Y-m-d'),
                    'time' => optional($dt)->format('H:i'),
                    'location' => $e->location_name,
                ];
            });
        return response()->json($events);
    } catch (\Throwable $e) {
        return response()->json([]);
    }
})->name('api.events.upcoming');

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
});

Route::middleware('auth')->group(function () {
    // Strava & AI Analysis (Protected)
    Route::get('/runcalendar/strava/connect', [App\Http\Controllers\CalendarController::class, 'stravaConnect'])->name('calendar.strava.connect');
    Route::get('/runcalendar/strava/callback', [App\Http\Controllers\CalendarController::class, 'stravaCallback'])->name('calendar.strava.callback');
    Route::post('/runcalendar/ai-analysis', [App\Http\Controllers\CalendarController::class, 'getAiAnalysis'])->name('calendar.ai.analysis');

    Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');
    
    // Profile routes (accessible by all authenticated users)
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    
    // User list routes (accessible by all authenticated users)
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('/users/runners', function(Request $request) {
        $request->merge(['role' => 'runner']);
        return app(App\Http\Controllers\UserController::class)->index($request);
    })->name('users.runners');
    Route::get('/users/coaches', function(Request $request) {
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
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/api/notifications/unread', [App\Http\Controllers\NotificationController::class, 'getUnread'])->name('notifications.unread');
    Route::post('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    
    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    });

    // Runner routes
    Route::middleware('role:runner')->prefix('runner')->name('runner.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
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
        Route::post('/calendar/reset-plan-list', [CalendarController::class, 'resetPlanList'])->name('calendar.reset-plan-list');
        Route::post('/calendar/reschedule', [CalendarController::class, 'reschedule'])->name('calendar.reschedule');
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
    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        // Cart routes
        Route::get('/cart', [App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
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
        Route::post('/athletes/{enrollment}/feedback', [App\Http\Controllers\Coach\AthleteController::class, 'storeFeedback'])->name('athletes.feedback');
    });

    // EO routes
    Route::middleware('role:eo')->prefix('eo')->name('eo.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\EO\DashboardController::class, 'index'])->name('dashboard');
        
        // Event management
        Route::resource('events', App\Http\Controllers\EO\EventController::class);
        Route::get('events/{event}/preview', [App\Http\Controllers\EO\EventController::class, 'preview'])->name('events.preview');
        Route::get('events/{event}/participants', [App\Http\Controllers\EO\EventController::class, 'participants'])->name('events.participants');
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
    if (!Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('home');
    }
    $user = Illuminate\Support\Facades\Auth::user();
    return match($user->role) {
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
