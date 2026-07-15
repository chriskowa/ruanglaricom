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

    public function showUploadForm(Request $request, Session $session)
    {
        Gate::authorize('capture', $session);

        $session->load(['runners' => function($q) {
            $q->orderBy('running_analysis_session_runner.sequence_no');
        }]);

        $trial = null;
        $videoUrl = null;
        if ($request->has('trial_id')) {
            $trial = \App\Models\RunningAnalysis\Trial::find($request->query('trial_id'));
            if ($trial) {
                $videoArtifact = $trial->artifacts()->where('type', 'video_clip')->first();
                if ($videoArtifact) {
                    $videoUrl = route('admin.running-analysis.trials.artifact', [$trial, $videoArtifact]);
                }
            }
        }

        return view('admin.running-analysis.upload', compact('session', 'trial', 'videoUrl'));
    }
}
