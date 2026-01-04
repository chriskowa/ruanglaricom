<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventPackage;
use App\Models\RaceCategory;
use Illuminate\Support\Facades\Cache;

class EventCacheService
{
    /**
     * Cache TTL in seconds
     */
    const CACHE_TTL_EVENT_DETAIL = 300; // 5 minutes

    const CACHE_TTL_PACKAGES = 60; // 1 minute

    const CACHE_TTL_QUOTA = 10; // 10 seconds

    /**
     * Cache event detail with categories
     */
    public function cacheEventDetail(Event $event): array
    {
        $cacheKey = $event->getCacheKey();

        return Cache::remember($cacheKey, self::CACHE_TTL_EVENT_DETAIL, function () use ($event) {
            $event->load(['categories', 'user']);

            return [
                'event' => $event->toArray(),
                'categories' => $event->categories->where('is_active', true)->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'distance_km' => $category->distance_km,
                        'code' => $category->code,
                        'quota' => $category->quota,
                        'price_early' => $category->price_early,
                        'price_regular' => $category->price_regular,
                        'price_late' => $category->price_late,
                        'min_age' => $category->min_age,
                        'max_age' => $category->max_age,
                        'cutoff_minutes' => $category->cutoff_minutes,
                    ];
                })->toArray(),
            ];
        });
    }

    /**
     * Get cached event detail
     */
    public function getCachedEventDetail(string $slug): ?array
    {
        $cacheKey = "event:detail:{$slug}";

        return Cache::get($cacheKey);
    }

    /**
     * Cache package quota
     */
    public function cachePackageQuota(EventPackage $package): int
    {
        $cacheKey = $package->getQuotaCacheKey();

        return Cache::remember($cacheKey, self::CACHE_TTL_QUOTA, function () use ($package) {
            // Refresh package from database
            $package->refresh();

            return $package->getRemainingQuota();
        });
    }

    /**
     * Get cached package quota
     */
    public function getCachedPackageQuota(int $packageId): ?int
    {
        $cacheKey = "package:quota:{$packageId}";

        return Cache::get($cacheKey);
    }

    /**
     * Cache categories with quota for an event
     */
    public function cacheCategoriesWithQuota(Event $event): array
    {
        $cacheKey = "event:categories:{$event->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL_PACKAGES, function () use ($event) {
            $categories = $event->categories()->where('is_active', true)->get();

            return $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'distance_km' => $category->distance_km,
                    'code' => $category->code,
                    'quota' => $category->quota,
                    'price_early' => $category->price_early,
                    'price_regular' => $category->price_regular,
                    'price_late' => $category->price_late,
                    'min_age' => $category->min_age,
                    'max_age' => $category->max_age,
                    'cutoff_minutes' => $category->cutoff_minutes,
                ];
            })->toArray();
        });
    }

    /**
     * Invalidate event cache
     */
    public function invalidateEventCache(Event $event): void
    {
        Cache::forget($event->getCacheKey());
        Cache::forget("event:categories:{$event->id}");
    }

    /**
     * Cache category quota
     */
    public function cacheCategoryQuota(RaceCategory $category): int
    {
        $cacheKey = $category->getQuotaCacheKey();

        return Cache::remember($cacheKey, self::CACHE_TTL_QUOTA, function () use ($category) {
            // Refresh category from database
            $category->refresh();

            return $category->getRemainingQuota();
        });
    }

    /**
     * Get cached category quota
     */
    public function getCachedCategoryQuota(int $categoryId): ?int
    {
        $cacheKey = "category:quota:{$categoryId}";

        return Cache::get($cacheKey);
    }

    /**
     * Invalidate category cache
     */
    public function invalidateCategoryCache($category): void
    {
        if ($category->event) {
            Cache::forget("event:categories:{$category->event->id}");
            Cache::forget($category->event->getCacheKey());
        }
        Cache::forget($category->getQuotaCacheKey());
    }
}
