<?php

namespace App\Models\KalenderPelari;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class UploadedAsset extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'kp_uploaded_assets';

    protected $fillable = [
        'user_id', 'storage_key', 'mime_type', 'width_px', 'height_px',
        'file_size_bytes', 'status', 'original_filename', 'checksum_sha256',
        'resolution_quality_score', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photoElements(): HasMany
    {
        return $this->hasMany(CalendarElement::class, 'asset_id');
    }

    public function isTemporary(): bool
    {
        return $this->status === 'TEMP_UPLOAD' || $this->status === 'LOCAL_ONLY';
    }

    public function storagePublicUrl(): ?string
    {
        if (! $this->storage_key) {
            return null;
        }

        return Storage::disk('public')->url($this->storage_key);
    }
}
