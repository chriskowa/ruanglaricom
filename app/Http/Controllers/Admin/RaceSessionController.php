<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Race;
use App\Models\RaceCertificate;
use App\Models\RaceSession;
use App\Models\RaceSessionLap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RaceSessionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user?->isAdmin();

        $query = RaceSession::query()
            ->with([
                'race:id,name,slug,logo_path,created_by',
                'creator:id,name,email',
            ])
            ->withCount(['laps', 'certificates'])
            ->orderByDesc('id');

        if (! $isAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('race', function ($rq) use ($user) {
                        $rq->where('created_by', $user->id);
                    });
            });
        }

        // Filter search (Race Name, Category, Slug)
        if ($request->filled('q')) {
            $term = trim((string) $request->get('q'));
            $query->where(function ($q) use ($term) {
                $q->where('slug', 'like', "%{$term}%")
                    ->orWhere('category', 'like', "%{$term}%")
                    ->orWhereHas('race', function ($rq) use ($term) {
                        $rq->where('name', 'like', "%{$term}%");
                    });
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'running') {
                $query->whereNotNull('started_at')->whereNull('ended_at');
            } elseif ($status === 'finished') {
                $query->whereNotNull('ended_at');
            } elseif ($status === 'draft') {
                $query->whereNull('started_at');
            } elseif ($status === 'empty') {
                $query->doesntHave('laps');
            }
        }

        // Filter by Race
        if ($request->filled('race_id')) {
            $query->where('race_id', $request->integer('race_id'));
        }

        // Quick Stats
        $baseStatsQuery = RaceSession::query();
        if (! $isAdmin) {
            $baseStatsQuery->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('race', function ($rq) use ($user) {
                        $rq->where('created_by', $user->id);
                    });
            });
        }

        $totalSessions = (clone $baseStatsQuery)->count();
        $runningSessions = (clone $baseStatsQuery)->whereNotNull('started_at')->whereNull('ended_at')->count();
        $finishedSessions = (clone $baseStatsQuery)->whereNotNull('ended_at')->count();
        $emptySessions = (clone $baseStatsQuery)->doesntHave('laps')->count();

        $sessions = $query->paginate(25)->withQueryString();

        // Get races list for dropdown filter
        $racesQuery = Race::query()->select(['id', 'name'])->orderByDesc('id');
        if (! $isAdmin) {
            $racesQuery->where('created_by', $user->id);
        }
        $races = $racesQuery->get();

        return view('admin.race-sessions.index', compact(
            'sessions',
            'races',
            'totalSessions',
            'runningSessions',
            'finishedSessions',
            'emptySessions'
        ));
    }

    public function destroy(RaceSession $session)
    {
        $this->ensureCanManageSession($session);

        DB::transaction(function () use ($session) {
            RaceCertificate::query()->where('race_session_id', $session->id)->delete();
            RaceSessionLap::query()->where('race_session_id', $session->id)->delete();
            $session->delete();
        }, 3);

        return back()->with('success', 'Sesi lomba #' . ($session->slug ?: $session->id) . ' berhasil dihapus.');
    }

    public function reset(RaceSession $session)
    {
        $this->ensureCanManageSession($session);

        DB::transaction(function () use ($session) {
            RaceCertificate::query()->where('race_session_id', $session->id)->delete();
            RaceSessionLap::query()->where('race_session_id', $session->id)->delete();
            $session->started_at = null;
            $session->ended_at = null;
            $session->save();
        }, 3);

        return back()->with('success', 'Hasil dan leaderboard sesi #' . ($session->slug ?: $session->id) . ' berhasil direset.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'session_ids' => 'required|array|min:1',
            'session_ids.*' => 'integer|exists:race_sessions,id',
        ]);

        $user = Auth::user();
        $isAdmin = $user?->isAdmin();

        $query = RaceSession::query()->whereIn('id', $validated['session_ids']);
        if (! $isAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('race', function ($rq) use ($user) {
                        $rq->where('created_by', $user->id);
                    });
            });
        }

        $sessions = $query->get();
        $count = 0;

        DB::transaction(function () use ($sessions, &$count) {
            foreach ($sessions as $session) {
                RaceCertificate::query()->where('race_session_id', $session->id)->delete();
                RaceSessionLap::query()->where('race_session_id', $session->id)->delete();
                $session->delete();
                $count++;
            }
        }, 3);

        return back()->with('success', "{$count} sesi lomba terpilih berhasil dibersihkan dan dihapus.");
    }

    public function cleanEmpty(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user?->isAdmin();

        $query = RaceSession::query()->doesntHave('laps');
        if (! $isAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('race', function ($rq) use ($user) {
                        $rq->where('created_by', $user->id);
                    });
            });
        }

        $sessions = $query->get();
        $count = 0;

        DB::transaction(function () use ($sessions, &$count) {
            foreach ($sessions as $session) {
                RaceCertificate::query()->where('race_session_id', $session->id)->delete();
                $session->delete();
                $count++;
            }
        }, 3);

        if ($count === 0) {
            return back()->with('info', 'Tidak ditemukan sesi kosong/percobaan tanpa lap.');
        }

        return back()->with('success', "Berhasil membersihkan {$count} sesi kosong/percobaan.");
    }

    private function ensureCanManageSession(RaceSession $session): void
    {
        $user = Auth::user();
        if ($user?->isAdmin()) {
            return;
        }

        if ($session->created_by === $user?->id) {
            return;
        }

        if ($session->race && $session->race->created_by === $user?->id) {
            return;
        }

        abort(403, 'Anda tidak memiliki akses untuk mengelola sesi balap ini.');
    }
}
