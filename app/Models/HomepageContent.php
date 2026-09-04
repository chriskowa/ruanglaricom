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

    /**
     * Get array of 3 background slider image URLs
     */
    public function getHeroSlidesAttribute(): array
    {
        $defaultSlides = [
            'https://ruanglari.com/storage/blog/media/92e97762-78b7-48e4-8b94-176b4e7fdf95.webp',
            'https://ruanglari.com/storage/blog/media/305fd042-17e8-4e89-86d7-8666d4e2229f.webp',
            'https://ruanglari.com/storage/blog/media/21bb785d-d104-4129-99ce-d84ae98afd3a.webp',
        ];

        if (empty($this->hero_image)) {
            return $defaultSlides;
        }

        // Jika tersimpan dalam format JSON array
        $decoded = json_decode($this->hero_image, true);
        if (is_array($decoded) && !empty($decoded)) {
            $slides = [];
            for ($i = 0; $i < 3; $i++) {
                if (!empty($decoded[$i])) {
                    $slides[] = str_starts_with($decoded[$i], 'http') 
                        ? $decoded[$i] 
                        : asset($decoded[$i]);
                } else {
                    $slides[] = $defaultSlides[$i];
                }
            }
            return $slides;
        }

        // Jika tersimpan sebagai single string image
        $single = str_starts_with($this->hero_image, 'http') 
            ? $this->hero_image 
            : asset($this->hero_image);

        return [$single, $defaultSlides[1], $defaultSlides[2]];
    }

    /**
     * Get array of all athlete PNG image URLs
     */
    public function getAthleteImagesAttribute(): array
    {
        $defaultAthlete = 'https://ruanglari.com/storage/blog/media/469168c8-1ae3-4f55-ae3d-f30954235ae9.webp';

        if (empty($this->floating_image)) {
            return [$defaultAthlete];
        }

        // Jika tersimpan sebagai JSON array
        $decoded = json_decode($this->floating_image, true);
        if (is_array($decoded) && !empty($decoded)) {
            $images = [];
            foreach ($decoded as $img) {
                if (!empty($img)) {
                    $images[] = str_starts_with($img, 'http')
                        ? $img
                        : asset($img);
                }
            }
            return !empty($images) ? $images : [$defaultAthlete];
        }

        // Jika tersimpan sebagai single string image
        $single = str_starts_with($this->floating_image, 'http') 
            ? $this->floating_image 
            : asset($this->floating_image);

        return [$single];
    }

    /**
     * Get raw athlete images array (for admin management)
     */
    public function getRawAthleteImagesAttribute(): array
    {
        if (empty($this->floating_image)) {
            return [];
        }

        $decoded = json_decode($this->floating_image, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded));
        }

        return [$this->floating_image];
    }

    /**
     * Get athlete PNG image URL (primary / backward compatible)
     */
    public function getAthleteImageAttribute(): string
    {
        $images = $this->athlete_images;
        return $images[0] ?? 'https://ruanglari.com/storage/blog/media/469168c8-1ae3-4f55-ae3d-f30954235ae9.webp';
    }
}
