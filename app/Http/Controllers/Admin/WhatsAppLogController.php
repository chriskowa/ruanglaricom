<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppLog;
use Illuminate\Http\Request;

class WhatsAppLogController extends Controller
{
    /**
     * Display a listing of WhatsApp logs.
     */
    public function index(Request $request)
    {
        $query = WhatsAppLog::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('to', 'like', "%{$q}%")
                    ->orWhere('message', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

        $statuses = WhatsAppLog::select('status')->distinct()->pluck('status');

        return view('admin.whatsapp-logs.index', compact('logs', 'statuses'));
    }

    /**
     * Resend a failed WhatsApp message.
     */
    public function resend(WhatsAppLog $log)
    {
        // Send the message using the helper
        \App\Helpers\WhatsApp::send($log->to, $log->message);

        return redirect()->back()->with('success', 'Pesan WhatsApp berhasil dikirim ulang.');
    }
}
