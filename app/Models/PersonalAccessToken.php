<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class PersonalAccessToken extends Model
{
    protected $table = 'personal_access_tokens';

    protected $fillable = [
        'name',
        'token',
        'abilities',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Generate a new personal access token for a user model.
     */
    public static function createToken(Model $user, string $name = 'mobile-app', array $abilities = ['*']): array
    {
        $plainTextToken = Str::random(40);
        $tokenHash = hash('sha256', $plainTextToken);

        /** @var self $accessToken */
        $accessToken = $user->tokens()->create([
            'name' => $name,
            'token' => $tokenHash,
            'abilities' => $abilities,
            'last_used_at' => now(),
        ]);

        return [
            'plainTextToken' => $plainTextToken,
            'accessToken' => $accessToken,
        ];
    }

    /**
     * Find a token record by plain text token.
     */
    public static function findToken(string $plainTextToken): ?self
    {
        if (trim($plainTextToken) === '') {
            return null;
        }

        $tokenHash = hash('sha256', $plainTextToken);

        return static::where('token', $tokenHash)->first();
    }
}
