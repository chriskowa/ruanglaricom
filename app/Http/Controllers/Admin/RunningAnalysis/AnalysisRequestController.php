<?php

namespace App\Http\Controllers\Admin\RunningAnalysis;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\RunningAnalysis\AnalysisRequest;
use App\Models\RunningAnalysis\Session;
use Illuminate\Http\Request;

class AnalysisRequestController extends Controller
{
    /**
     * List all analysis requests (admin).
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');

        $query = AnalysisRequest::query()->with(['runner', 'handler', 'session']);

        if ($status !== 'all' && in_array($status, AnalysisRequest::STATUSES, true)) {
            $query->where('status', $status);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        $counts = [
            'all'       => AnalysisRequest::count(),
            'pending'   => AnalysisRequest::where('status', AnalysisRequest::STATUS_PENDING)->count(),
            'approved'  => AnalysisRequest::where('status', AnalysisRequest::STATUS_APPROVED)->count(),
            'scheduled' => AnalysisRequest::where('status', AnalysisRequest::STATUS_SCHEDULED)->count(),
            'completed' => AnalysisRequest::where('status', AnalysisRequest::STATUS_COMPLETED)->count(),
            'rejected'  => AnalysisRequest::where('status', AnalysisRequest::STATUS_REJECTED)->count(),
        ];

        $statTabs = [
            'all'       => ['label' => 'Total', 'count' => $counts['all'], 'color' => 'slate'],
            'pending'   => ['label' => 'Menunggu', 'count' => $counts['pending'], 'color' => 'yellow'],
            'approved'  => ['label' => 'Disetujui', 'count' => $counts['approved'], 'color' => 'green'],
            'scheduled' => ['label' => 'Dijadwal', 'count' => $counts['scheduled'], 'color' => 'blue'],
            'completed' => ['label' => 'Selesai', 'count' => $counts['completed'], 'color' => 'purple'],
            'rejected'  => ['label' => 'Ditolak', 'count' => $counts['rejected'], 'color' => 'red'],
        ];

        return view('admin.running-analysis.requests.index', [
            'requests' => $requests,
            'counts'   => $counts,
            'status'   => $status,
            'statTabs' => $statTabs,
        ]);
    }

    /**
     * Show a single request.
     */
    public function show(AnalysisRequest $analysisRequest)
    {
        $analysisRequest->load(['runner', 'handler', 'session']);

        $sessions = Session::query()
            ->whereIn('status', [Session::STATUS_DRAFT, Session::STATUS_ACTIVE])
            ->orderBy('session_date', 'desc')
            ->get();

        return view('admin.running-analysis.requests.show', [
            'request'  => $analysisRequest,
            'sessions' => $sessions,
        ]);
    }

    /**
     * Approve a request (notify runner).
     */
    public function approve(Request $request, AnalysisRequest $analysisRequest)
    {
        if (! $analysisRequest->isPending()) {
            return redirect()
                ->route('admin.running-analysis.requests.show', $analysisRequest)
                ->with('error', 'Hanya permintaan dengan status menunggu yang dapat disetujui.');
        }

        $analysisRequest->update([
            'status'     => AnalysisRequest::STATUS_APPROVED,
            'handled_by' => auth()->id(),
            'handled_at' => now(),
        ]);

        Notification::create([
            'user_id'        => $analysisRequest->runner_id,
            'type'           => 'running_analysis',
            'title'          => 'Permintaan Analisis Disetujui',
            'message'        => 'Permintaan analisis lari Anda (' . $analysisRequest->focusAreaLabel() . ') telah disetujui. Admin akan menjadwalkan sesi pengambilan video.',
            'reference_type' => AnalysisRequest::class,
            'reference_id'   => $analysisRequest->id,
            'is_read'        => false,
        ]);

        return redirect()
            ->route('admin.running-analysis.requests.show', $analysisRequest)
            ->with('success', 'Permintaan disetujui. Notifikasi telah dikirim ke runner.');
    }

    /**
     * Reject a request (notify runner with reason).
     */
    public function reject(Request $request, AnalysisRequest $analysisRequest)
    {
        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'max:1000'],
        ]);

        if (! $analysisRequest->isPending()) {
            return redirect()
                ->route('admin.running-analysis.requests.show', $analysisRequest)
                ->with('error', 'Hanya permintaan dengan status menunggu yang dapat ditolak.');
        }

        $analysisRequest->update([
            'status'      => AnalysisRequest::STATUS_REJECTED,
            'admin_notes' => $validated['admin_notes'],
            'handled_by'  => auth()->id(),
            'handled_at'  => now(),
        ]);

        Notification::create([
            'user_id'        => $analysisRequest->runner_id,
            'type'           => 'running_analysis',
            'title'          => 'Permintaan Analisis Ditolak',
            'message'        => 'Permintaan analisis lari Anda ditolak. Alasan: ' . $validated['admin_notes'],
            'reference_type' => AnalysisRequest::class,
            'reference_id'   => $analysisRequest->id,
            'is_read'        => false,
        ]);

        return redirect()
            ->route('admin.running-analysis.requests.index', ['status' => 'rejected'])
            ->with('success', 'Permintaan ditolak. Runner telah dinotifikasi.');
    }

    /**
     * Schedule a request by linking it to a session.
     */
    public function schedule(Request $request, AnalysisRequest $analysisRequest)
    {
        $validated = $request->validate([
            'session_id'  => ['required', 'exists:running_analysis_sessions,id'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $analysisRequest->update([
            'status'      => AnalysisRequest::STATUS_SCHEDULED,
            'session_id'  => $validated['session_id'],
            'admin_notes' => $validated['admin_notes'] ?? $analysisRequest->admin_notes,
            'handled_by'  => auth()->id(),
            'handled_at'  => now(),
        ]);

        Notification::create([
            'user_id'        => $analysisRequest->runner_id,
            'type'           => 'running_analysis',
            'title'          => 'Sesi Analisis Dijadwalkan',
            'message'        => 'Sesi analisis lari Anda telah dijadwalkan. Silakan cek detail sesi di menu Analisis Lari.',
            'reference_type' => AnalysisRequest::class,
            'reference_id'   => $analysisRequest->id,
            'is_read'        => false,
        ]);

        return redirect()
            ->route('admin.running-analysis.requests.show', $analysisRequest)
            ->with('success', 'Permintaan dijadwalkan ke sesi terpilih.');
    }

    /**
     * Mark a request as completed.
     */
    public function complete(Request $request, AnalysisRequest $analysisRequest)
    {
        $analysisRequest->update([
            'status'     => AnalysisRequest::STATUS_COMPLETED,
            'handled_by' => auth()->id(),
            'handled_at' => now(),
        ]);

        Notification::create([
            'user_id'        => $analysisRequest->runner_id,
            'type'           => 'running_analysis',
            'title'          => 'Analisis Lari Selesai',
            'message'        => 'Analisis lari Anda telah selesai. Hasil dapat dilihat di menu Analisis Lari.',
            'reference_type' => AnalysisRequest::class,
            'reference_id'   => $analysisRequest->id,
            'is_read'        => false,
        ]);

        return redirect()
            ->route('admin.running-analysis.requests.show', $analysisRequest)
            ->with('success', 'Permintaan ditandai selesai.');
    }
}
