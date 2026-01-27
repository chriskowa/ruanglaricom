<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

$columns = Illuminate\Support\Facades\Schema::getColumnListing('transactions');
print_r($columns);

// Check payment_gateway type
$type = Illuminate\Support\Facades\Schema::getColumnType('transactions', 'payment_gateway');
echo "Type: " . $type . "\n";
