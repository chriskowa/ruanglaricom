<?php

use Illuminate\Support\Facades\DB;

// Find duplicates
$duplicates = DB::table('participants')
    ->select('race_category_id', 'id_card', DB::raw('COUNT(*) as count'))
    ->groupBy('race_category_id', 'id_card')
    ->having('count', '>', 1)
    ->get();

foreach ($duplicates as $duplicate) {
    echo "Processing duplicate: Race Cat {$duplicate->race_category_id}, ID Card {$duplicate->id_card} (Count: {$duplicate->count})\n";

    // Get all IDs for this combination
    $ids = DB::table('participants')
        ->where('race_category_id', $duplicate->race_category_id)
        ->where('id_card', $duplicate->id_card)
        ->orderBy('id', 'desc') // Keep the latest one
        ->pluck('id')
        ->toArray();

    // Remove the first one (keep latest) or keep first? Let's keep the latest (highest ID)
    $idToKeep = array_shift($ids); // Shifts the first element (highest ID because of desc sort)

    // If we want to keep the OLDEST, we should sort by id ASC and shift.
    // Let's assume we keep the latest.

    // Actually, usually we keep the one that has more related data, but here participants are likely standalone or have transactions.
    // Let's just keep the latest one.

    if (! empty($ids)) {
        DB::table('participants')->whereIn('id', $ids)->delete();
        echo 'Deleted '.count($ids)." duplicate entries.\n";
    }
}

echo "Done.\n";
