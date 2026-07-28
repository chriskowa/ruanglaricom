<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class RunningEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $heroImg = $this->getHeroImageUrl();

        $categories = [];
        if ($this->relationLoaded('categories')) {
            foreach ($this->categories as $cat) {
                $categories[] = [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'distance' => $cat->distance_km ?? $cat->name,
                    'price' => $cat->price,
                    'early_bird_price' => $cat->early_bird_price,
                    'slot_quota' => $cat->quota,
                ];
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'start_at' => optional($this->start_at)->toISOString(),
            'end_at' => optional($this->end_at)->toISOString(),
            'formatted_date' => optional($this->start_at)->translatedFormat('d M Y') ?: null,
            'city_name' => optional($this->city)->name ?: ($this->location_name ?: 'Indonesia'),
            'location_name' => $this->location_name,
            'location_address' => $this->location_address,
            'latitude' => $this->location_lat,
            'longitude' => $this->location_lng,
            'image' => $heroImg,
            'is_featured' => (bool) $this->is_featured,
            'status' => $this->status,
            'registration_open_at' => optional($this->registration_open_at)->toISOString(),
            'registration_close_at' => optional($this->registration_close_at)->toISOString(),
            'organizer_name' => $this->organizer_name ?: 'RuangLari',
            'external_registration_link' => $this->external_registration_link,
            'web_url' => route('events.show', $this->slug),
            'categories' => $categories,
            'created_at' => optional($this->created_at)->toISOString(),
        ];
    }

    private function getHeroImageUrl(): ?string
    {
        $img = $this->hero_image_url ?: $this->hero_image;
        if (! $img) {
            return asset('ruanglari.webp');
        }
        if (Str::startsWith($img, ['http://', 'https://'])) {
            return $img;
        }

        return asset('storage/' . ltrim($img, '/'));
    }
}
