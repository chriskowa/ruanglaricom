<?php

namespace App\Http\Controllers;

use App\Models\Community;
use Illuminate\Http\Request;

class CommunityProfileController extends Controller
{
    public function show($slug)
    {
        $community = Community::where('slug', $slug)->firstOrFail();

        // Default theme colors if not set
        $colors = [
            'neon' => ['#CCFF00', '#0A0A0A', '#1F1F1F'],
            'dark' => ['#ffffff', '#000000', '#333333'],
            'blue' => ['#3b82f6', '#1e3a8a', '#172554'],
            'red' => ['#ef4444', '#7f1d1d', '#450a0a'],
        ];
        
        $theme = $community->theme_color ?? 'neon';
        $palette = $colors[$theme] ?? $colors['neon'];

        // Process Schedules to match View expectations
        $schedules = collect($community->schedules ?? [])->map(function($s) {
            return [
                'day' => $s['day'] ?? '',
                'time' => $s['time'] ?? '',
                'type' => $s['activity'] ?? '', // Map activity to type
                'loc' => $s['location'] ?? '', // Map location to loc
            ];
        });

        // Process Captains to include full image URL
        $captains = collect($community->captains ?? [])->map(function($c) {
            return [
                'name' => $c['name'] ?? '',
                'role' => $c['role'] ?? '',
                'img' => $c['image'] ? asset('storage/' . $c['image']) : 'https://via.placeholder.com/400x400?text=' . urlencode($c['name'] ?? 'Captain'),
            ];
        });

        // Process FAQs
        $faqs = collect($community->faqs ?? [])->map(function($f) {
            return [
                'question' => $f['question'] ?? '',
                'answer' => $f['answer'] ?? '',
                'open' => false,
            ];
        });

        return view('community.detail', compact('community', 'palette', 'schedules', 'captains', 'faqs'));
    }
}
