<?php

namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Models\EoReportEmailDelivery;
use App\Models\EventEmailDeliveryLog;
use App\Models\EventEmailMinuteCounter;

class EmailMonitoringController extends Controller
{
    public function index()
    {
        $eoUserId = auth()->id();

        $minuteFrom = now()->startOfMinute()->subMinutes(5);
        $minuteTo = now()->startOfMinute()->addMinutes(2);

        $rateStats = EventEmailMinuteCounter::query()
            ->with('event')
            ->whereBetween('minute_at', [$minuteFrom, $minuteTo])
            ->whereHas('event', function ($q) use ($eoUserId) {
                $q->where('user_id', $eoUserId);
            })
            ->orderByDesc('minute_at')
            ->orderByDesc('reserved_emails')
            ->limit(200)
            ->get();

        $recentFailures = EventEmailDeliveryLog::query()
            ->with('event')
            ->where('status', 'failed')
            ->whereHas('event', function ($q) use ($eoUserId) {
                $q->where('user_id', $eoUserId);
            })
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $reportDeliveries = EoReportEmailDelivery::query()
            ->with('event')
            ->where('eo_user_id', $eoUserId)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('eo.email-monitoring.index', [
            'withSidebar' => true,
            'rateStats' => $rateStats,
            'recentFailures' => $recentFailures,
            'reportDeliveries' => $reportDeliveries,
        ]);
    }
}

