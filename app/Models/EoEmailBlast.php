<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EoEmailBlast extends Model
{
    use HasFactory;

    protected $fillable = [
        'eo_user_id',
        'event_id',
        'name',
        'subject_template',
        'html_template',
        'source_type',
        'csv_original_name',
        'csv_path',
        'email_column',
        'name_column',
        'status',
        'target_count',
        'sent_count',
        'failed_count',
    ];

    public function eoUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'eo_user_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(EoEmailBlastDelivery::class);
    }
}
