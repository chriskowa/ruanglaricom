<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\RaceCategory;

class CommunityPricingService
{
    public function getCategoryPrice(RaceCategory $category): array
    {
        $now = now();
        $early = (int) ($category->price_early ?? 0);
        $regular = (int) ($category->price_regular ?? 0);
        $late = (int) ($category->price_late ?? 0);

        if ($early > 0) {
            $isEarlyValid = true;

            if ($category->early_bird_end_at && $now->greaterThan($category->early_bird_end_at)) {
                $isEarlyValid = false;
            }

            if ($isEarlyValid && $category->early_bird_quota) {
                $earlySold = Participant::where('race_category_id', $category->id)
                    ->where('price_type', 'early')
                    ->whereHas('transaction', function ($q) {
                        $q->whereIn('payment_status', ['pending', 'paid', 'cod']);
                    })
                    ->count();

                if ($earlySold >= $category->early_bird_quota) {
                    $isEarlyValid = false;
                }
            }

            if ($isEarlyValid) {
                return ['price' => $early, 'type' => 'early'];
            }
        }

        if ($late > 0 && $regular === 0) {
            return ['price' => $late, 'type' => 'late'];
        }

        return ['price' => $regular, 'type' => 'regular'];
    }
}
