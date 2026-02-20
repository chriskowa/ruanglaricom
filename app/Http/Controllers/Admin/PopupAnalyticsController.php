<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Popup;
use App\Models\PopupStat;
use Illuminate\Http\Request;

class PopupAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->input('range', '30');
        $days = max(7, min(180, (int) $range));
        $start = now()->subDays($days)->startOfDay();
        $stats = PopupStat::query()
            ->where('stat_date', '>=', $start->toDateString())
            ->orderBy('stat_date')
            ->get()
            ->groupBy('popup_id');
        $popups = Popup::query()->orderBy('name')->get();
        $summary = $popups->map(function (Popup $popup) use ($stats) {
            $rows = $stats->get($popup->id, collect());
            $views = $rows->sum('views');
            $clicks = $rows->sum('clicks');
            $conversions = $rows->sum('conversions');
            $ctr = $views > 0 ? round(($clicks / $views) * 100, 2) : 0;
            $cr = $views > 0 ? round(($conversions / $views) * 100, 2) : 0;
            return [
                'popup' => $popup,
                'views' => $views,
                'clicks' => $clicks,
                'conversions' => $conversions,
                'ctr' => $ctr,
                'cr' => $cr,
            ];
        });
        $totalViews = $summary->sum('views');
        $totalClicks = $summary->sum('clicks');
        $totalConversions = $summary->sum('conversions');
        $totalCtr = $totalViews > 0 ? round(($totalClicks / $totalViews) * 100, 2) : 0;
        $totalCr = $totalViews > 0 ? round(($totalConversions / $totalViews) * 100, 2) : 0;
        return view('admin.popups.analytics', [
            'summary' => $summary,
            'days' => $days,
            'totalViews' => $totalViews,
            'totalClicks' => $totalClicks,
            'totalConversions' => $totalConversions,
            'totalCtr' => $totalCtr,
            'totalCr' => $totalCr,
        ]);
    }
}
