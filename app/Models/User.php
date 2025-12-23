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
        'strava_token',
        'google_calendar_token',
        'gender',
    ];

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
        ];
    }

    // Relationships
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
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
}
