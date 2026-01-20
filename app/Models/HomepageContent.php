<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'headline',
        'subheadline',
        'hero_image',
        'floating_image',
    ];
}
