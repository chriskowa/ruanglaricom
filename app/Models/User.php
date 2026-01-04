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
        'audit_history',
        'weekly_volume',
        'weekly_km_target',
    ];

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
            'is_pacer' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
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
