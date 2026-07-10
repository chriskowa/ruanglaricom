<?php

namespace App\Http\Controllers\Admin\RunningAnalysis;

use App\Http\Controllers\Controller;
use App\Models\RunningAnalysis\Session;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SessionController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Session::class);

        $sessions = Session::withCount('runners')
            ->with('creator')
            ->orderBy('session_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('admin.running-analysis.sessions.index', compact('sessions'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Session::class);

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'location'     => 'nullable|string|max:255',
            'session_date' => 'required|date',
        ]);

        $session = Session::create([
            'name'         => $validated['name'],
            'location'     => $validated['location'],
            'session_date' => $validated['session_date'],
            'created_by'   => auth()->id(),
            'status'       => Session::STATUS_DRAFT,
        ]);

        return redirect()->route('admin.running-analysis.sessions.show', $session)
            ->with('success', 'Session created successfully.');
    }

    public function show(Session $session)
    {
        Gate::authorize('view', $session);

        $session->load(['runners', 'trials.runner']);
        
        $alreadyAddedIds = $session->runners->pluck('id')->toArray();
        
        $availableRunners = User::where('role', 'runner')
            ->where('is_active', true)
            ->whereNotIn('id', $alreadyAddedIds)
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return view('admin.running-analysis.sessions.show', compact('session', 'availableRunners'));
    }

    public function update(Request $request, Session $session)
    {
        Gate::authorize('update', $session);

        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', Session::STATUSES),
        ]);

        $session->update($validated);

        return redirect()->route('admin.running-analysis.sessions.show', $session)
            ->with('success', 'Session status updated.');
    }

    public function addRunners(Request $request, Session $session)
    {
        Gate::authorize('manageRunners', $session);

        $validated = $request->validate([
            'runner_ids'   => 'required|array',
            'runner_ids.*' => 'exists:users,id',
        ]);

        $currentSequence = $session->runners()->count();

        foreach ($validated['runner_ids'] as $runnerId) {
            if (!$session->runners()->where('users.id', $runnerId)->exists()) {
                $currentSequence++;
                $session->runners()->attach($runnerId, [
                    'sequence_no' => $currentSequence,
                    'status'      => 'pending',
                ]);
            }
        }

        return redirect()->route('admin.running-analysis.sessions.show', $session)
            ->with('success', 'Runners added to session.');
    }

    public function removeRunner(Session $session, User $user)
    {
        Gate::authorize('manageRunners', $session);

        $session->runners()->detach($user->id);

        return redirect()->route('admin.running-analysis.sessions.show', $session)
            ->with('success', 'Runner removed from session queue.');
    }

    public function searchRunners(Request $request, Session $session)
    {
        Gate::authorize('manageRunners', $session);

        $query = $request->input('q');
        $alreadyAddedIds = $session->runners->pluck('id')->toArray();

        $runners = User::where('role', 'runner')
            ->where('is_active', true)
            ->whereNotIn('id', $alreadyAddedIds)
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sq) use ($query) {
                    $sq->where('name', 'like', "%{$query}%")
                       ->orWhere('email', 'like', "%{$query}%")
                       ->orWhere('username', 'like', "%{$query}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'email']);

        return response()->json($runners);
    }
}
