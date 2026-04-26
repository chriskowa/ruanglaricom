<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiArticleTopic extends Model
{
    protected $fillable = ['topic', 'url', 'is_active'];
}
