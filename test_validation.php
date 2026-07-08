<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$requestData = [
    'title' => 'Test Thread',
    'description' => '',
    'type' => 'Casual Run',
    'run_distance_km' => '5',
    'pace_min' => '6:00',
    'pace_max' => '7:00',
    'start_date' => '2026-07-09',
    'start_time' => '06:00',
    'start_location_name' => 'Monas',
    'start_latitude' => '-6.175392',
    'start_longitude' => '106.827153',
    'route_url' => '',
    'quota' => '10',
    'visibility' => 'public',
    'is_beginner_friendly' => '0',
    'is_women_friendly' => '0',
    'is_recurring' => '0',
    'notes' => ''
];

$rules = [
    'title' => 'required|string|max:100',
    'description' => 'nullable|string|max:500',
    'type' => 'required|string|in:Casual Run,Long Run,Speed Session,Recovery Run,Race Prep,Community Run',
    'run_distance_km' => 'required|numeric|min:0.5|max:100',
    'pace_min' => 'nullable|string|max:10',
    'pace_max' => 'nullable|string|max:10',
    'start_date' => 'required|date|after_or_equal:today',
    'start_time' => 'required|string',
    'start_location_name' => 'required|string|max:150',
    'start_latitude' => 'required|numeric|between:-90,90',
    'start_longitude' => 'required|numeric|between:-180,180',
    'route_url' => 'nullable|url|max:255',
    'quota' => 'required|integer|min:2|max:100',
    'visibility' => 'required|string|in:public,community',
    'is_beginner_friendly' => 'boolean',
    'is_women_friendly' => 'boolean',
    'is_recurring' => 'boolean',
    'notes' => 'nullable|string|max:500',
    'gpx_file' => 'nullable|file|max:5120',
];

$validator = \Illuminate\Support\Facades\Validator::make($requestData, $rules);

if ($validator->fails()) {
    echo "Validation Failed:\n";
    print_r($validator->errors()->toArray());
} else {
    echo "Validation Passed!\n";
}
