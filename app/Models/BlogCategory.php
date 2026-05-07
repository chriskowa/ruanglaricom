<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class BlogCategory extends Model
{
    protected $table = 'blog_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'meta_title',
        'meta_description',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_blog_category', 'blog_category_id', 'article_id');
    }

    public function parent()
    {
        return $this->belongsTo(BlogCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(BlogCategory::class, 'parent_id');
    }
}
