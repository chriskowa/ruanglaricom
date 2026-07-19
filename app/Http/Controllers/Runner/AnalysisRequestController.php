<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\RunningAnalysis\AnalysisRequest;
use App\Models\User;
use Illuminate\Http\Request;

class AnalysisRequestController extends Controller
{
    /**
     * List the runner's own analysis requests.
     */
    public function index()
    {
        $user = auth()->user();

        $requests = AnalysisRequest::where('runner_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->with('session')
            ->paginate(10);

        $pendingCount = AnalysisRequest::where('runner_id', $user->id)
            ->where('status', AnalysisRequest::STATUS_PENDING)
            ->count();

        return view('runner.analysis-requests.index', [
            'requests'     => $requests,
            'pendingCount' => $pendingCount,
            'focusAreas'   => AnalysisRequest::FOCUS_AREAS,
        ]);
    }

    /**
     * Show the request form.
     */
    public function create()
    {
        $user = auth()->user();

        // Prevent spamming: block if there is already a pending request
        $hasPending = AnalysisRequest::where('runner_id', $user->id)
            ->where('status', AnalysisRequest::STATUS_PENDING)
            ->exists();

        return view('runner.analysis-requests.create', [
            'focusAreas' => AnalysisRequest::FOCUS_AREAS,
            'hasPending' => $hasPending,
            'user'       => $user,
        ]);
    }

    /**
     * Store a new analysis request and notify all admins.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Guard: one pending request at a time
        $hasPending = AnalysisRequest::where('runner_id', $user->id)
            ->where('status', AnalysisRequest::STATUS_PENDING)
            ->exists();

        if ($hasPending) {
            return redirect()
                ->route('runner.analysis-requests.index')
                ->with('error', 'Anda masih memiliki permintaan analisis yang menunggu persetujuan. Silakan tunggu hingga diproses.');
        }

        $validated = $request->validate([
            'focus_area'         => ['required', 'string', 'in:' . implode(',', array_keys(AnalysisRequest::FOCUS_AREAS))],
            'goals'              => ['nullable', 'string', 'max:1000'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'preferred_location' => ['nullable', 'string', 'max:255'],
            'preferred_date'     => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $analysisRequest = AnalysisRequest::create([
            'runner_id'          => $user->id,
            'runner_name'        => $user->name,
            'runner_email'       => $user->email,
            'focus_area'         => $validated['focus_area'],
            'goals'              => $validated['goals'] ?? null,
            'notes'              => $validated['notes'] ?? null,
            'preferred_location' => $validated['preferred_location'] ?? null,
            'preferred_date'     => $validated['preferred_date'] ?? null,
            'status'             => AnalysisRequest::STATUS_PENDING,
        ]);

        // Notify all admins
        $admins = User::query()->where('role', 'admin')->get();
        if ($admins->isNotEmpty()) {
            $now = now();
            $rows = [];
            foreach ($admins as $admin) {
                $rows[] = [
                    'user_id'        => $admin->id,
                    'type'           => 'running_analysis_request',
                    'title'          => 'Permintaan Analisis Lari Baru',
                    'message'        => $user->name . ' meminta analisis lari (' . $analysisRequest->focusAreaLabel() . ').',
                    'reference_type' => AnalysisRequest::class,
                    'reference_id'   => $analysisRequest->id,
                    'is_read'        => false,
                    'read_at'        => null,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
            Notification::insert($rows);
        }

        return redirect()
            ->route('runner.analysis-requests.index')
            ->with('success', 'Permintaan analisis lari berhasil dikirim. Admin akan meninjau dan menjadwalkan sesi untuk Anda.');
    }
}
