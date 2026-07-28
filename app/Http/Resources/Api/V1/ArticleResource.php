<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $lang = strtolower((string) ($request->query('lang') ?: app()->getLocale()));
        $isEn = $lang === 'en';

        $title = ($isEn && ! empty($this->title_en)) ? $this->title_en : $this->title;
        $excerpt = ($isEn && ! empty($this->excerpt_en)) ? $this->excerpt_en : $this->excerpt;
        $content = ($isEn && ! empty($this->content_en)) ? $this->content_en : $this->content;

        $img = $this->featured_image;
        if ($img) {
            if (! Str::startsWith($img, ['http://', 'https://'])) {
                $img = asset('storage/' . ltrim($img, '/'));
            }
        } else {
            $img = asset('ruanglari.webp');
        }

        return [
            'id' => $this->id,
            'title' => $title,
            'slug' => $this->slug,
            'excerpt' => $excerpt,
            'content' => $this->when($request->routeIs('api.v1.articles.show'), $content),
            'featured_image' => $img,
            'is_featured' => (bool) $this->is_featured,
            'views_count' => (int) $this->views_count,
            'published_at' => optional($this->published_at ?: $this->created_at)->toISOString(),
            'formatted_date' => optional($this->published_at ?: $this->created_at)->translatedFormat('d M Y'),
            'web_url' => route('blog.show', $this->slug) . ($isEn ? '?lang=en' : ''),
            'category' => $this->relationLoaded('category') && $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null,
        ];
    }
}
