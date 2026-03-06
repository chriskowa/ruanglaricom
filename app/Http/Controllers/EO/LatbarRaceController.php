<?php

namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Race;
use App\Models\RaceSession;
use App\Models\RaceSessionParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LatbarRaceController extends Controller
{
    public function status($slug)
    {
        $data = Cache::remember('race_status_'.$slug, 2, function () use ($slug) {
            $event = Event::where('slug', $slug)->first();

            if (! $event) {
                return null;
            }

            $race = Race::where('event_id', $event->id)->first();
            if (! $race) {
                return [
                    'status' => 'idle',
                    'message' => 'Race belum disiapkan',
                ];
            }

            $session = $race->sessions()->orderByDesc('id')->first();
            if (! $session) {
                return [
                    'status' => 'idle',
                    'race_name' => $race->name,
                    'participants' => [],
                ];
            }

            $state = 'ready';
            if ($session->started_at && ! $session->ended_at) {
                $state = 'running';
            } elseif ($session->ended_at) {
                $state = 'finished';
            }

            $participants = $race->participants()
                ->whereHas('participant', function ($q) {
                    $q->where('isApproved', 1);
                })
                ->select(['id', 'participant_id', 'name', 'bib_number', 'rank', 'finished_at', 'result_time_ms'])
                ->orderByRaw('CASE WHEN `rank` IS NULL THEN 1 ELSE 0 END ASC')
                ->orderBy('rank', 'asc')
                ->orderByRaw('CAST(bib_number AS UNSIGNED) ASC')
                ->orderBy('bib_number')
                ->get()
                ->map(function (RaceSessionParticipant $p) {
                    return [
                        'id' => $p->id,
                        'participant_id' => $p->participant_id,
                        'name' => $p->name,
                        'bib' => $p->bib_number,
                        'rank' => $p->rank,
                        'finished_at' => optional($p->finished_at)->toISOString(),
                        'result_time_ms' => $p->result_time_ms,
                    ];
                })
                ->values();

            return [
                'status' => $state,
                'race_name' => $race->name,
                'session_id' => $session->id,
                'started_at' => optional($session->started_at)->toISOString(),
                'ended_at' => optional($session->ended_at)->toISOString(),
                'participants' => $participants,
            ];
        });

        if (! $data) {
            abort(404);
        }

        return response()->json($data);
    }

    public function setup(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        if (! auth()->check() || $event->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $payload = $request->validate([
            'participant_ids' => ['required', 'array', 'min:2'],
            'participant_ids.*' => ['integer', 'exists:participants,id'],
        ]);

        $race = Race::firstOrCreate(
            ['event_id' => $event->id],
            ['name' => $event->name.' Race', 'created_by' => auth()->id()]
        );

        DB::transaction(function () use ($race, $payload) {
            $race->participants()->delete();
            $race->sessions()->delete();

            RaceSession::create([
                'race_id' => $race->id,
                'created_by' => auth()->id(),
                'started_at' => null,
                'ended_at' => null,
            ]);

            $participants = Participant::query()
                ->whereIn('id', $payload['participant_ids'])
                ->get(['id', 'bib_number', 'name']);

            foreach ($participants as $p) {
                $bib = $p->bib_number ? (string) $p->bib_number : ('P'.$p->id);

                RaceSessionParticipant::create([
                    'race_id' => $race->id,
                    'participant_id' => $p->id,
                    'bib_number' => $bib,
                    'name' => $p->name,
                    'predicted_time_ms' => null,
                    'result_time_ms' => null,
                    'rank' => null,
                    'finished_at' => null,
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function start($slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        if (! auth()->check() || $event->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $race = Race::where('event_id', $event->id)->firstOrFail();
        $session = $race->sessions()->orderByDesc('id')->firstOrFail();

        $session->update([
            'started_at' => now(),
            'ended_at' => null,
        ]);

        return response()->json(['success' => true]);
    }

    public function finish($slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        if (! auth()->check() || $event->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $race = Race::where('event_id', $event->id)->firstOrFail();
        $session = $race->sessions()->orderByDesc('id')->firstOrFail();

        if (! $session->started_at) {
            $session->update(['started_at' => now()]);
        }

        $session->update(['ended_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function setWinner(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        if (! auth()->check() || $event->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $payload = $request->validate([
            'participant_id' => ['required', 'integer', 'exists:participants,id'],
            'rank' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $race = Race::where('event_id', $event->id)->firstOrFail();

        $rsp = RaceSessionParticipant::where('race_id', $race->id)
            ->where('participant_id', $payload['participant_id'])
            ->firstOrFail();

        $rsp->update([
            'rank' => $payload['rank'],
            'finished_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function updateTargetTime(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        if (! auth()->check() || $event->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $payload = $request->validate([
            'participant_id' => ['required', 'integer', 'exists:participants,id'],
            'target_time' => ['required', 'string', 'regex:/^\d{2}:\d{2}:\d{2}$/'],
            'result_time_ms' => ['nullable', 'integer', 'min:0'],
        ]);

        $participant = Participant::with(['transaction', 'category'])->findOrFail($payload['participant_id']);

        $belongsToEvent = false;
        if ($participant->transaction && $participant->transaction->event_id == $event->id) {
            $belongsToEvent = true;
        } elseif ($participant->category && $participant->category->event_id == $event->id) {
            $belongsToEvent = true;
        }

        if (! $belongsToEvent) {
            abort(404);
        }

        $update = [
            'target_time' => $payload['target_time'],
        ];

        if ($request->has('result_time_ms') && $payload['result_time_ms'] !== null) {
            $update['result_time_ms'] = $payload['result_time_ms'];
        }

        $participant->update($update);

        return response()->json(['success' => true]);
    }

    public function reset($slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        if (! auth()->check() || $event->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $race = Race::where('event_id', $event->id)->first();
        if (! $race) {
            return response()->json(['success' => true]);
        }

        DB::transaction(function () use ($race) {
            $race->sessions()->delete();
            $race->participants()->update([
                'rank' => null,
                'result_time_ms' => null,
                'finished_at' => null,
            ]);
        });

        return response()->json(['success' => true]);
    }
}
