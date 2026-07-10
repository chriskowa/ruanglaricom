<?php

namespace App\Http\Controllers\Admin\RunningAnalysis;

use App\Http\Controllers\Controller;
use App\Models\RunningAnalysis\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CaptureController extends Controller
{
    public function show(Session $session)
    {
        Gate::authorize('capture', $session);

        if (!$session->isActive()) {
            return redirect()->route('admin.running-analysis.sessions.show', $session)
                ->with('error', 'Session must be set to Active before launching capture.');
        }

        $session->load(['runners' => function($q) {
            $q->orderBy('running_analysis_session_runner.sequence_no');
        }]);

        return view('admin.running-analysis.capture', compact('session'));
    }
}
