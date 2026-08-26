<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Article extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'title_en',
        'slug',
        'excerpt',
        'excerpt_en',
        'content',
        'content_en',
        'featured_image',
        'status',
        'is_featured',
        'published_at',
        'meta_title',
        'meta_title_en',
        'meta_description',
        'meta_description_en',
        'meta_keywords',
        'meta_keywords_en',
        'focus_keyword',
        'focus_keyword_en',
        'secondary_keywords',
        'secondary_keywords_en',
        'canonical_url',
        'canonical_url_en',
        'views_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });

        static::updating(function ($article) {
            if ($article->isDirty('title') && ! $article->isDirty('slug')) {
                $article->slug = Str::slug($article->title);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'article_tag', 'article_id', 'tag_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BlogCategory::class, 'article_blog_category', 'article_id', 'blog_category_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function getLocalizedTitleAttribute(): string
    {
        if (app()->getLocale() === 'en' && $this->title_en) {
            return (string) $this->title_en;
        }

        return (string) $this->title;
    }

    public function getLocalizedExcerptAttribute(): ?string
    {
        if (app()->getLocale() === 'en' && $this->excerpt_en) {
            return $this->excerpt_en;
        }

        return $this->excerpt;
    }

    public function getLocalizedContentAttribute(): string
    {
        if (app()->getLocale() === 'en' && $this->content_en) {
            return (string) $this->content_en;
        }

        return (string) $this->content;
    }

    public function getLocalizedMetaTitleAttribute(): ?string
    {
        if (app()->getLocale() === 'en' && $this->meta_title_en) {
            return $this->meta_title_en;
        }

        return $this->meta_title;
    }

    public function getLocalizedMetaDescriptionAttribute(): ?string
    {
        if (app()->getLocale() === 'en' && $this->meta_description_en) {
            return $this->meta_description_en;
        }

        return $this->meta_description;
    }

    public function getLocalizedFocusKeywordAttribute(): ?string
    {
        if (app()->getLocale() === 'en' && $this->focus_keyword_en) {
            return $this->focus_keyword_en;
        }

        return $this->focus_keyword;
    }

    public function getLocalizedSecondaryKeywordsAttribute(): ?string
    {
        if (app()->getLocale() === 'en' && $this->secondary_keywords_en) {
            return $this->secondary_keywords_en;
        }

        return $this->secondary_keywords;
    }

    public function getLocalizedMetaKeywordsAttribute(): ?string
    {
        $focus = $this->localized_focus_keyword;
        $secondary = $this->localized_secondary_keywords;

        if ($focus || $secondary) {
            $parts = array_filter([$focus, $secondary]);
            return implode(', ', $parts);
        }

        if (app()->getLocale() === 'en' && $this->meta_keywords_en) {
            return $this->meta_keywords_en;
        }

        return $this->meta_keywords;
    }

    public function getLocalizedCanonicalUrlAttribute(): ?string
    {
        if (app()->getLocale() === 'en' && $this->canonical_url_en) {
            return $this->canonical_url_en;
        }

        return $this->canonical_url;
    }

    public function setSecondaryKeywordsAttribute($value): void
    {
        $this->attributes['secondary_keywords'] = is_array($value) ? implode(', ', array_filter($value)) : $value;
    }

    public function setSecondaryKeywordsEnAttribute($value): void
    {
        $this->attributes['secondary_keywords_en'] = is_array($value) ? implode(', ', array_filter($value)) : $value;
    }

    public function setMetaKeywordsAttribute($value): void
    {
        $this->attributes['meta_keywords'] = is_array($value) ? implode(', ', array_filter($value)) : $value;
    }

    public function setMetaKeywordsEnAttribute($value): void
    {
        $this->attributes['meta_keywords_en'] = is_array($value) ? implode(', ', array_filter($value)) : $value;
    }

    public function setFocusKeywordAttribute($value): void
    {
        $this->attributes['focus_keyword'] = is_array($value) ? implode(', ', array_filter($value)) : $value;
    }

    public function setFocusKeywordEnAttribute($value): void
    {
        $this->attributes['focus_keyword_en'] = is_array($value) ? implode(', ', array_filter($value)) : $value;
    }
}
