<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;

class PublicProgramController extends Controller
{
    /**
     * Show marketplace/landing page for programs
     */
    public function index(Request $request)
    {
        $query = Program::where('is_published', true)
            ->where('is_active', true)
            ->with(['coach', 'city']);

        // Filter by category (distance_target)
        if ($request->has('category') && $request->category) {
            $query->where('distance_target', $request->category);
        }

        // Filter by difficulty
        if ($request->has('difficulty') && $request->difficulty) {
            $query->where('difficulty', $request->difficulty);
        }

        // Filter by price range
        if ($request->has('price_min') && $request->price_min) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->has('price_max') && $request->price_max) {
            $query->where('price', '<=', $request->price_max);
        }

        // Filter by rating
        if ($request->has('rating') && $request->rating) {
            $query->where('average_rating', '>=', $request->rating);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('coach', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Sort
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->orderBy('average_rating', 'desc');
                break;
            case 'popular':
                $query->orderBy('enrolled_count', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $programs = $query->paginate(12);

        // If AJAX request, return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($programs);
        }

        return view('programs.index', [
            'programs' => $programs,
            'filters' => $request->only(['category', 'difficulty', 'price_min', 'price_max', 'rating', 'search', 'sort']),
        ]);
    }

    /**
     * Show program detail page
     */
    public function show($slug)
    {
        $program = Program::where('slug', $slug)
            ->where('is_published', true)
            ->where('is_active', true)
            ->with(['coach', 'city', 'reviews.runner'])
            ->firstOrFail();

        // Check if user is enrolled (if authenticated)
        $isEnrolled = false;
        if (auth()->check() && auth()->user()->role === 'runner') {
            $isEnrolled = $program->enrollments()
                ->where('runner_id', auth()->id())
                ->where('status', '!=', 'cancelled')
                ->exists();
        }

        // Get reviews with pagination
        $reviews = $program->reviews()
            ->with('runner')
            ->approved()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('programs.show', [
            'program' => $program,
            'isEnrolled' => $isEnrolled,
            'reviews' => $reviews,
        ]);
    }
}
