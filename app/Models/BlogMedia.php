<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BlogMedia extends Model
{
    protected $table = 'blog_media';

    protected $fillable = [
        'user_id',
        'filename',
        'path',
        'disk',
        'mime_type',
        'size',
        'alt_text',
    ];

    protected $appends = ['url', 'formatted_size'];

    public function getUrlAttribute()
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        } elseif ($bytes > 1) {
            return $bytes.' bytes';
        } elseif ($bytes == 1) {
            return $bytes.' byte';
        } else {
            return '0 bytes';
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
