<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Latest 5 Transactions:\n";
$txs = App\Models\Transaction::orderBy('id', 'desc')->limit(5)->get();
foreach ($txs as $tx) {
    echo "ID: {$tx->id} | Event: " . ($tx->event ? $tx->event->slug : 'N/A') . " | Status: {$tx->payment_status} | Phone: " . ($tx->pic_data['phone'] ?? 'N/A') . "\n";
}
