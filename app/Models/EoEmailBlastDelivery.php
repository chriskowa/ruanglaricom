<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EoEmailBlastDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'eo_email_blast_id',
        'to_email',
        'to_name',
        'payload',
        'rendered_subject',
        'status',
        'attempts',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];

    public function blast(): BelongsTo
    {
        return $this->belongsTo(EoEmailBlast::class, 'eo_email_blast_id');
    }
}
