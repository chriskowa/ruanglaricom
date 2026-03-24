<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'view_path',
        'sections',
        'is_active',
        'is_homepage'
    ];

    protected $casts = [
        'sections' => 'array',
        'is_active' => 'boolean',
        'is_homepage' => 'boolean'
    ];

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeHomepage($query)
    {
        return $query->where('is_homepage', true);
    }
}