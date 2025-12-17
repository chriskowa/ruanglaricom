<?php

namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\RaceResult;
use App\Models\RaceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RaceResultController extends Controller
{
    /**
     * Show race results management page
     */
    public function index(Event $event)
    {
        // Check authorization
        if ($event->user_id !== auth()->id()) {
            abort(403);
        }

        $results = RaceResult::forEvent($event->id)
            ->with('category')
            ->orderBy('rank_category', 'asc')
            ->orderBy('chip_time', 'asc')
            ->paginate(50);

        $categories = $event->categories()->where('is_active', true)->get();

        return view('eo.events.results', compact('event', 'results', 'categories'));
    }

    /**
     * Store race result manually
     */
    public function store(Request $request, Event $event)
    {
        // Check authorization
        if ($event->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'bib_number' => 'required|string|max:50',
            'runner_name' => 'required|string|max:255',
            'gender' => 'required|in:M,F',
            'nationality' => 'nullable|string|max:10',
            'category_code' => 'required|string|max:20',
            'race_category_id' => 'nullable|exists:race_categories,id',
            'gun_time' => 'nullable|date_format:H:i:s',
            'chip_time' => 'required|date_format:H:i:s',
            'pace' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
        ]);

        $validated['event_id'] = $event->id;
        $validated['nationality'] = $validated['nationality'] ?? 'IDN';

        // Calculate ranks (akan diupdate setelah semua data masuk)
        $validated['rank_overall'] = null;
        $validated['rank_category'] = null;
        $validated['rank_gender'] = null;
        $validated['is_podium'] = false;
        $validated['podium_position'] = null;

        $result = RaceResult::create($validated);

        // Recalculate ranks setelah insert
        $this->recalculateRanks($event->id);

        return response()->json([
            'success' => true,
            'message' => 'Race result berhasil ditambahkan',
            'data' => $result,
        ]);
    }

    /**
     * Show single race result (for edit)
     */
    public function show(Event $event, RaceResult $raceResult)
    {
        // Check authorization
        if ($event->user_id !== auth()->id() || $raceResult->event_id !== $event->id) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $raceResult->id,
                'bib_number' => $raceResult->bib_number,
                'runner_name' => $raceResult->runner_name,
                'gender' => $raceResult->gender,
                'nationality' => $raceResult->nationality,
                'category_code' => $raceResult->category_code,
                'gun_time' => $raceResult->getFormattedGunTime(),
                'chip_time' => $raceResult->getFormattedChipTime(),
                'pace' => $raceResult->pace,
                'notes' => $raceResult->notes,
            ],
        ]);
    }

    /**
     * Update race result
     */
    public function update(Request $request, Event $event, RaceResult $raceResult)
    {
        // Check authorization
        if ($event->user_id !== auth()->id() || $raceResult->event_id !== $event->id) {
            abort(403);
        }

        $validated = $request->validate([
            'bib_number' => 'required|string|max:50',
            'runner_name' => 'required|string|max:255',
            'gender' => 'required|in:M,F',
            'nationality' => 'nullable|string|max:10',
            'category_code' => 'required|string|max:20',
            'race_category_id' => 'nullable|exists:race_categories,id',
            'gun_time' => 'nullable|date_format:H:i:s',
            'chip_time' => 'required|date_format:H:i:s',
            'pace' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
        ]);

        $raceResult->update($validated);

        // Recalculate ranks setelah update
        $this->recalculateRanks($event->id);

        return response()->json([
            'success' => true,
            'message' => 'Race result berhasil diupdate',
            'data' => $raceResult->fresh(),
        ]);
    }

    /**
     * Delete race result
     */
    public function destroy(Event $event, RaceResult $raceResult)
    {
        // Check authorization
        if ($event->user_id !== auth()->id() || $raceResult->event_id !== $event->id) {
            abort(403);
        }

        $raceResult->delete();

        // Recalculate ranks setelah delete
        $this->recalculateRanks($event->id);

        return response()->json([
            'success' => true,
            'message' => 'Race result berhasil dihapus',
        ]);
    }

    /**
     * Upload CSV file
     */
    public function uploadCsv(Request $request, Event $event)
    {
        // Check authorization
        if ($event->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        
        $data = array_map('str_getcsv', file($path));
        
        // Skip header row
        $header = array_shift($data);
        
        // Expected CSV format: BIB, Name, Gender, Category, Gun Time, Chip Time, Pace, Nationality
        $errors = [];
        $successCount = 0;
        
        DB::beginTransaction();
        try {
            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 karena header dan 0-indexed
                
                if (count($row) < 6) {
                    $errors[] = "Baris {$rowNumber}: Format tidak valid (minimal 6 kolom)";
                    continue;
                }

                // Parse data
                $bibNumber = trim($row[0] ?? '');
                $runnerName = trim($row[1] ?? '');
                $gender = strtoupper(trim($row[2] ?? 'M'));
                $categoryCode = trim($row[3] ?? '');
                $gunTime = $this->parseTime($row[4] ?? '');
                $chipTime = $this->parseTime($row[5] ?? '');
                $pace = trim($row[6] ?? '');
                $nationality = trim($row[7] ?? 'IDN');

                if (empty($bibNumber) || empty($runnerName) || empty($chipTime)) {
                    $errors[] = "Baris {$rowNumber}: BIB, Nama, dan Chip Time wajib diisi";
                    continue;
                }

                if (!in_array($gender, ['M', 'F'])) {
                    $errors[] = "Baris {$rowNumber}: Gender harus M atau F";
                    continue;
                }

                // Find category by code
                $category = $event->categories()
                    ->where('code', $categoryCode)
                    ->orWhere('name', 'like', "%{$categoryCode}%")
                    ->first();

                RaceResult::create([
                    'event_id' => $event->id,
                    'race_category_id' => $category?->id,
                    'bib_number' => $bibNumber,
                    'runner_name' => $runnerName,
                    'gender' => $gender,
                    'nationality' => $nationality ?: 'IDN',
                    'category_code' => $categoryCode,
                    'gun_time' => $gunTime,
                    'chip_time' => $chipTime,
                    'pace' => $pace ?: null,
                ]);

                $successCount++;
            }

            // Recalculate ranks setelah import
            $this->recalculateRanks($event->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengimpor {$successCount} data",
                'errors' => $errors,
                'success_count' => $successCount,
                'error_count' => count($errors),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recalculate ranks untuk semua results dalam event
     */
    private function recalculateRanks($eventId)
    {
        // Reset semua ranks
        RaceResult::forEvent($eventId)->update([
            'rank_overall' => null,
            'rank_category' => null,
            'rank_gender' => null,
            'is_podium' => false,
            'podium_position' => null,
        ]);

        // Calculate overall rank
        $overallResults = RaceResult::forEvent($eventId)
            ->whereNotNull('chip_time')
            ->orderBy('chip_time', 'asc')
            ->get();

        foreach ($overallResults as $index => $result) {
            $result->update(['rank_overall' => $index + 1]);
        }

        // Calculate rank per category dan gender
        $categories = RaceResult::forEvent($eventId)
            ->distinct()
            ->pluck('category_code')
            ->filter();

        foreach ($categories as $categoryCode) {
            foreach (['M', 'F'] as $gender) {
                $results = RaceResult::forEvent($eventId)
                    ->forCategory($categoryCode)
                    ->forGender($gender)
                    ->whereNotNull('chip_time')
                    ->orderBy('chip_time', 'asc')
                    ->get();

                foreach ($results as $index => $result) {
                    $rank = $index + 1;
                    $result->update([
                        'rank_category' => $rank,
                        'rank_gender' => $rank,
                    ]);

                    // Set podium untuk juara 1-3
                    if ($rank <= 3) {
                        $result->update([
                            'is_podium' => true,
                            'podium_position' => $rank,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Parse time string ke format H:i:s
     */
    private function parseTime($timeString)
    {
        if (empty($timeString)) {
            return null;
        }

        // Remove whitespace
        $timeString = trim($timeString);

        // Handle format seperti "02:35:12" atau "2:35:12"
        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $timeString, $matches)) {
            return sprintf('%02d:%02d:%02d', $matches[1], $matches[2], $matches[3]);
        }

        // Handle format seperti "2:35" (asumsi menit:detik)
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $timeString, $matches)) {
            return sprintf('00:%02d:%02d', $matches[1], $matches[2]);
        }

        return null;
    }
}



