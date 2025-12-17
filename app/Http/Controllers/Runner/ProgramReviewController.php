<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramReviewController extends Controller
{
    /**
     * Store a new review
     */
    public function store(Request $request, Program $program)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:2000',
        ]);

        $user = auth()->user();

        // Check if user has completed the program
        $enrollment = $program->enrollments()
            ->where('runner_id', $user->id)
            ->where('status', 'completed')
            ->first();

        if (!$enrollment) {
            return back()->with('error', 'Anda harus menyelesaikan program terlebih dahulu sebelum memberikan review.');
        }

        // Check if user already reviewed this program
        $existingReview = ProgramReview::where('program_id', $program->id)
            ->where('runner_id', $user->id)
            ->first();

        DB::beginTransaction();
        try {
            if ($existingReview) {
                // Update existing review
                $existingReview->update([
                    'rating' => $validated['rating'],
                    'review' => $validated['review'] ?? null,
                ]);
            } else {
                // Create new review
                $existingReview = ProgramReview::create([
                    'program_id' => $program->id,
                    'runner_id' => $user->id,
                    'rating' => $validated['rating'],
                    'review' => $validated['review'] ?? null,
                ]);
            }

            // Recalculate program average rating and total reviews
            $reviews = ProgramReview::where('program_id', $program->id)->get();
            $averageRating = $reviews->avg('rating');
            $totalReviews = $reviews->count();

            $program->update([
                'average_rating' => round($averageRating, 2),
                'total_reviews' => $totalReviews,
            ]);

            DB::commit();

            return back()->with('success', 'Review berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan review.');
        }
    }
}
