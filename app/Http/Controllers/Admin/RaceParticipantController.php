<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Race;
use App\Models\RaceSessionParticipant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RaceParticipantController extends Controller
{
    public function store(Request $request, Race $race)
    {
        $validated = $request->validate([
            'bib_number' => [
                'required',
                'string',
                'max:32',
                Rule::unique('race_session_participants', 'bib_number')->where('race_id', $race->id),
            ],
            'name' => 'required|string|max:255',
            'predicted_time' => 'nullable|string|max:32',
        ]);

        $predictedMs = $this->parseTimeToMs($validated['predicted_time'] ?? null, 'predicted_time');

        RaceSessionParticipant::create([
            'race_id' => $race->id,
            'participant_id' => null,
            'bib_number' => trim((string) $validated['bib_number']),
            'name' => trim((string) $validated['name']),
            'predicted_time_ms' => $predictedMs,
        ]);

        return back()->with('success', 'Participant berhasil ditambahkan.');
    }

    public function update(Request $request, Race $race, RaceSessionParticipant $raceSessionParticipant)
    {
        if ((int) $raceSessionParticipant->race_id !== (int) $race->id) {
            abort(404);
        }

        $validated = $request->validate([
            'bib_number' => [
                'required',
                'string',
                'max:32',
                Rule::unique('race_session_participants', 'bib_number')
                    ->where('race_id', $race->id)
                    ->ignore($raceSessionParticipant->id),
            ],
            'name' => 'required|string|max:255',
            'predicted_time' => 'nullable|string|max:32',
            'result_time' => 'nullable|string|max:32',
            'finished_at' => 'nullable|date',
            'created_at' => 'nullable|date',
        ]);

        $predictedMs = $this->parseTimeToMs($validated['predicted_time'] ?? null, 'predicted_time');
        $resultMs = $this->parseTimeToMs($validated['result_time'] ?? null, 'result_time');

        $raceSessionParticipant->bib_number = trim((string) $validated['bib_number']);
        $raceSessionParticipant->name = trim((string) $validated['name']);
        $raceSessionParticipant->predicted_time_ms = $predictedMs;
        $raceSessionParticipant->result_time_ms = $resultMs;
        $raceSessionParticipant->finished_at = $validated['finished_at'] ?? null;
        
        if (!empty($validated['created_at'])) {
            $raceSessionParticipant->created_at = $validated['created_at'];
        }

        $raceSessionParticipant->save();

        return back()->with('success', 'Participant berhasil diupdate.');
    }

    public function destroy(Race $race, RaceSessionParticipant $raceSessionParticipant)
    {
        if ((int) $raceSessionParticipant->race_id !== (int) $race->id) {
            abort(404);
        }

        $raceSessionParticipant->delete();

        return back()->with('success', 'Participant berhasil dihapus.');
    }

    private function parseTimeToMs($raw, $field = 'predicted_time'): ?int
    {
        $s = trim((string) ($raw ?? ''));
        if ($s === '') {
            return null;
        }

        if (ctype_digit($s)) {
            $n = (int) $s;
            if ($n < 0 || $n > 86400000) {
                throw ValidationException::withMessages([
                    $field => ucfirst(str_replace('_', ' ', $field)) . ' (ms) tidak valid.',
                ]);
            }

            return $n;
        }

        if (preg_match('/^(\d{1,3}):(\d{1,2})(?:\.(\d{1,2}))?$/', $s, $m)) {
            $minutes = (int) $m[1];
            $seconds = (int) $m[2];
            $cs = isset($m[3]) ? (int) $m[3] : 0;

            if ($minutes > 1440 || $seconds >= 60 || $cs >= 100) {
                throw ValidationException::withMessages([
                    $field => ucfirst(str_replace('_', ' ', $field)) . ' format mm:ss.cc tidak valid.',
                ]);
            }

            return ($minutes * 60 * 1000) + ($seconds * 1000) + ($cs * 10);
        }

        if (preg_match('/^(\d{1,2}):(\d{1,2}):(\d{1,2})(?:\.(\d{1,2}))?$/', $s, $m)) {
            $hours = (int) $m[1];
            $minutes = (int) $m[2];
            $seconds = (int) $m[3];
            $cs = isset($m[4]) ? (int) $m[4] : 0;

            if ($hours > 24 || $minutes >= 60 || $seconds >= 60 || $cs >= 100) {
                throw ValidationException::withMessages([
                    $field => ucfirst(str_replace('_', ' ', $field)) . ' format hh:mm:ss.cc tidak valid.',
                ]);
            }

            return ($hours * 3600 * 1000) + ($minutes * 60 * 1000) + ($seconds * 1000) + ($cs * 10);
        }

        throw ValidationException::withMessages([
            $field => ucfirst(str_replace('_', ' ', $field)) . ' format salah. Gunakan mm:ss.cc, hh:mm:ss.cc, atau milidetik.',
        ]);
    }
}
