<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted()
    {
        static::creating(function ($user) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'username')) {
                if (empty($user->username) && !empty($user->name)) {
                    $username = \Illuminate\Support\Str::slug($user->name);
                    $count = 1;
                    while (static::where('username', $username)->exists()) {
                        $username = \Illuminate\Support\Str::slug($user->name) . $count++;
                    }
                    $user->username = $username;
                }
            }
        });
    }

    protected $appends = ['avatar_url', 'vdot', 'training_paces'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'is_active',
        'city_id',
        'package_tier',
        'bank_account',
        'bank_verified_at',
        'wallet_id',
        'referral_code',
        'referred_by',
        'avatar',
        'profile_images',
        'banner',
        'phone',
        'date_of_birth',
        'address',
        'strava_id',
        'strava_token',
        'strava_access_token',
        'strava_refresh_token',
        'strava_expires_at',
        'strava_url',
        'instagram_url',
        'facebook_url',
        'tiktok_url',
        'google_calendar_token',
        'gender',
        'weight',
        'height',
        'is_pacer',
        'pb_5k',
        'pb_10k',
        'pb_hm',
        'pb_fm',
        'pb_balke',
        'audit_history',
        'weekly_volume',
        'weekly_km_target',
        'current_package_id',
        'membership_expires_at',
        'membership_status',
        'run_points',
        'buddy_rating',
        'is_receive_wa',
    ];

    /**
     * Get avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if (! $this->avatar) {
            return $this->gender === 'female' ? asset('images/default-female.svg') : asset('images/default-male.svg');
        }

        if (str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }

        if (str_starts_with($this->avatar, 'images/')) {
            return asset($this->avatar);
        }

        // Normalize path: trim spaces and trim leading/trailing slashes
        $path = trim($this->avatar);
        
        // Strip out existing storage prefix if stored in DB to prevent duplicates
        if (str_starts_with($path, '/storage/')) {
            $path = substr($path, 9);
        } elseif (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        } elseif ($path === 'storage' || $path === '/storage') {
            $path = '';
        }

        // Strip leading slash
        $path = ltrim($path, '/');

        if ($path === '') {
            return $this->gender === 'female' ? asset('images/default-female.svg') : asset('images/default-male.svg');
        }

        return asset('storage/' . $path);
    }

    public function pacer()
    {
        return $this->hasOne(Pacer::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'bank_account' => 'array',
            'bank_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'profile_images' => 'array',
            'strava_expires_at' => 'datetime',
            'audit_history' => 'array',
            'weekly_volume' => 'decimal:2',
            'weekly_km_target' => 'decimal:2',
            'followers_count' => 'integer',
            'is_pacer' => 'boolean',
            'is_active' => 'boolean',
            'membership_expires_at' => 'datetime',
            'is_receive_wa' => 'boolean',
        ];
    }

    public function currentPackage()
    {
        return $this->belongsTo(Package::class, 'current_package_id');
    }

    public function membershipTransactions()
    {
        return $this->hasMany(MembershipTransaction::class);
    }

    public function isMembershipActive(): bool
    {
        if ($this->role !== 'eo') {
            return true; // Non-EO users don't need membership
        }

        if ($this->membership_status === 'active' && $this->membership_expires_at && $this->membership_expires_at->isFuture()) {
            return true;
        }

        // Check if user is on free trial or special access (optional logic)
        // For LITE package (price 0), it should be active indefinitely or renewable
        if ($this->currentPackage && $this->currentPackage->price == 0) {
            return true;
        }

        return false;
    }

    // Relationships
    public function createdRunThreads()
    {
        return $this->hasMany(RunThread::class, 'creator_id');
    }

    public function joinedRunThreads()
    {
        return $this->belongsToMany(RunThread::class, 'run_thread_participants', 'user_id', 'run_thread_id')
            ->withPivot('status', 'joined_at')
            ->withTimestamps();
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    // Role helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCoach(): bool
    {
        return $this->role === 'coach';
    }

    public function isRunner(): bool
    {
        return $this->role === 'runner';
    }

    public function isEO(): bool
    {
        return $this->role === 'eo';
    }

    public function isEventOrganizer(): bool
    {
        return $this->role === 'eo';
    }

    public function getIsSellerAttribute(): bool
    {
        return true;
    }

    // Programs (as coach)
    public function programs()
    {
        return $this->hasMany(Program::class, 'coach_id');
    }

    // Program enrollments (as runner)
    public function programEnrollments()
    {
        return $this->hasMany(ProgramEnrollment::class, 'runner_id');
    }

    // Running Analysis Sessions (as runner)
    public function runningAnalysisSessions()
    {
        return $this->belongsToMany(\App\Models\RunningAnalysis\Session::class, 'running_analysis_session_runner', 'runner_id', 'session_id')
            ->using(\App\Models\RunningAnalysis\SessionRunner::class)
            ->withPivot(['id', 'sequence_no', 'status', 'notes', 'consent_pose', 'consent_video', 'consent_report', 'consent_ai'])
            ->withTimestamps();
    }

    // Running Analysis Trials (as runner)
    public function runningAnalysisTrials()
    {
        return $this->hasMany(\App\Models\RunningAnalysis\Trial::class, 'runner_id');
    }

    // Running Analysis Reports (as runner)
    public function runningAnalysisReports()
    {
        return $this->hasMany(\App\Models\RunningAnalysis\Report::class, 'runner_id');
    }

    // Follow relationships
    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    // Message relationships
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    // Post relationships
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function postLikes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function postComments()
    {
        return $this->hasMany(PostComment::class);
    }

    // Blog Articles
    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    // Run Connect Enhancements
    public function achievements()
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function ratingsReceived()
    {
        return $this->hasMany(UserRating::class, 'reviewee_id');
    }

    public function ratingsGiven()
    {
        return $this->hasMany(UserRating::class, 'reviewer_id');
    }

    // Notification relationships
    public function notifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->where('is_read', false)->latest();
    }

    // Marketplace relationships
    public function marketplaceProducts()
    {
        return $this->hasMany(\App\Models\Marketplace\MarketplaceProduct::class, 'user_id');
    }

    /**
     * Calculate best VDOT from PBs
     */
    public function getVdotAttribute()
    {
        $daniels = app(\App\Services\DanielsRunningService::class);
        $bestVdot = 0;

        $pbs = [
            '5k' => $this->pb_5k,
            '10k' => $this->pb_10k,
            '21k' => $this->pb_hm,
            '42k' => $this->pb_fm,
        ];

        foreach ($pbs as $dist => $time) {
            if ($time) {
                try {
                    $vdot = $daniels->calculateVDOT($time, $dist);
                    if ($vdot > $bestVdot) {
                        $bestVdot = $vdot;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        // Balke Test 15-Minute Run (in meters)
        if ($this->pb_balke) {
            try {
                $balkeVdot = (($this->pb_balke / 15) - 133) * 0.172 + 33.3;
                if ($balkeVdot > $bestVdot) {
                    $bestVdot = $balkeVdot;
                }
            } catch (\Exception $e) {
            }
        }

        return $bestVdot > 0 ? $bestVdot : null;
    }

    /**
     * Get training paces based on best VDOT
     */
    public function getTrainingPacesAttribute()
    {
        $vdot = $this->vdot;
        if (! $vdot) {
            return null;
        }

        $daniels = app(\App\Services\DanielsRunningService::class);

        return $daniels->calculateTrainingPaces($vdot);
    }

    /**
     * Get equivalent race times based on best VDOT
     */
    public function getEquivalentRaceTimesAttribute()
    {
        $vdot = $this->vdot;
        if (! $vdot) {
            return null;
        }

        $daniels = app(\App\Services\DanielsRunningService::class);

        return $daniels->calculateEquivalentRaceTimes($vdot);
    }
}
