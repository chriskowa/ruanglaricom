<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Display a listing of all programs for Admin
     */
    public function index(Request $request)
    {
        $query = Program::with(['coach', 'city'])
            ->withCount(['enrollments as active_athletes_count' => function ($q) {
                $q->whereIn('status', ['active', 'purchased']);
            }]);

        // Search by title, description, or coach name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('coach', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('distance_target', $request->category);
        }

        // Filter by featured status
        if ($request->has('featured') && $request->featured !== '') {
            $query->where('is_featured', (bool) $request->featured);
        }

        // Sort
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'athletes':
                $query->orderByDesc('active_athletes_count');
                break;
            case 'rating':
                $query->orderByDesc('average_rating');
                break;
            case 'price_desc':
                $query->orderByDesc('price');
                break;
            case 'newest':
            default:
                $query->orderByDesc('created_at');
                break;
        }

        $programs = $query->paginate(15)->withQueryString();

        return view('admin.programs.index', [
            'programs' => $programs,
            'filters' => $request->only(['search', 'category', 'featured', 'sort']),
        ]);
    }

    /**
     * Toggle featured status of a program
     */
    public function toggleFeatured(Program $program)
    {
        $newStatus = ! $program->is_featured;
        $program->update(['is_featured' => $newStatus]);

        $message = $newStatus 
            ? "Program \"{$program->title}\" berhasil dijadikan Featured Program!" 
            : "Status Featured pada program \"{$program->title}\" berhasil dicabut.";

        return back()->with('success', $message);
    }

    /**
     * Remove the specified program from database
     */
    public function destroy(Program $program)
    {
        $title = $program->title;
        $program->delete();

        return back()->with('success', "Program \"{$title}\" telah berhasil dihapus dari sistem.");
    }
}
