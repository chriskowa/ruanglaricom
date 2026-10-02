<?php

namespace App\Services\KalenderPelari;

class AssetLifecycleService
{
    public function transition(string $from, string $to, array $context = []): string
    {
        $transitions = [
            'LOCAL_ONLY->TEMP_UPLOAD' => true,
            'TEMP_UPLOAD->SAVED_PROJECT' => true,
            'SAVED_PROJECT->PENDING_CHECKOUT' => true,
            'PENDING_CHECKOUT->PAID_ACTIVE' => true,
            'PAID_ACTIVE->EXPIRED' => true,
            '*->DELETED' => true,
        ];

        $direct = "{$from}->{$to}";
        $wildcard = "*->{$to}";
        if (empty($transitions[$direct]) && empty($transitions[$wildcard])) {
            return $from;
        }

        return $to;
    }
}
