<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventEmailDeliveryLog;
use App\Models\EventEmailMinuteCounter;
use App\Models\PredictionErrorLog;
use Illuminate\Support\Facades\DB;

class EmailMonitoringController extends Controller
{
    public function index()
    {
        $queueStats = DB::table('jobs')
            ->selectRaw('queue, COUNT(*) as total, MIN(available_at) as oldest_available_at')
            ->where('queue', 'like', 'emails-%')
            ->groupBy('queue')
            ->orderByDesc('total')
            ->get();

        $minuteFrom = now()->startOfMinute()->subMinute();
        $minuteTo = now()->startOfMinute()->addMinutes(2);

        $rateStats = EventEmailMinuteCounter::query()
            ->with('event')
            ->whereBetween('minute_at', [$minuteFrom, $minuteTo])
            ->orderByDesc('minute_at')
            ->orderByDesc('reserved_emails')
            ->limit(200)
            ->get();

        $recentFailures = EventEmailDeliveryLog::query()
            ->with('event')
            ->where('status', 'failed')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $predictionErrors = PredictionErrorLog::query()
            ->with(['event', 'raceCategory'])
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('admin.email-monitoring.index', [
            'withSidebar' => true,
            'queueStats' => $queueStats,
            'rateStats' => $rateStats,
            'recentFailures' => $recentFailures,
            'predictionErrors' => $predictionErrors,
        ]);
    }
}
