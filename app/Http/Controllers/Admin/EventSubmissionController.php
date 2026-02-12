<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class EventSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = EventSubmission::query()->with(['city', 'raceType', 'reviewer']);

        $status = $request->input('status', 'pending');
        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $s = trim((string) $request->input('search'));
            $query->where(function ($q) use ($s) {
                $q->where('event_name', 'like', "%{$s}%")
                    ->orWhere('location_name', 'like', "%{$s}%")
                    ->orWhere('contributor_email', 'like', "%{$s}%");
            });
        }

        $submissions = $query->orderBy('id', 'desc')->paginate(20)->appends($request->only('status', 'search'));

        return view('admin.event-submissions.index', [
            'submissions' => $submissions,
            'status' => $status,
            'search' => $request->input('search'),
        ]);
    }

    public function show(EventSubmission $submission)
    {
        $submission->load(['city', 'raceType', 'reviewer']);

        return view('admin.event-submissions.show', [
            'submission' => $submission,
        ]);
    }

    public function approve(Request $request, EventSubmission $submission)
    {
        if ($submission->status !== 'pending') {
            return redirect()->route('admin.event-submissions.show', $submission)->with('error', 'Submission sudah diproses.');
        }

        $request->validate([
            'review_note' => 'nullable|string|max:2000',
            'publish' => 'nullable|boolean',
        ]);

        $startAt = Carbon::parse($submission->event_date);
        if (! empty($submission->start_time)) {
            $startAt->setTimeFromTimeString((string) $submission->start_time);
        }

        $slug = $this->uniqueSlug($submission->event_name);

        $event = Event::create([
            'user_id' => auth()->id() ?? 1,
            'name' => $submission->event_name,
            'slug' => $slug,
            'short_description' => $submission->notes,
            'full_description' => $submission->notes,
            'start_at' => $startAt,
            'city_id' => $submission->city_id,
            'location_name' => $submission->location_name,
            'location_address' => $submission->location_address,
            'race_type_id' => $submission->race_type_id,
            'hero_image_url' => $submission->banner, // Map banner to hero_image_url
            'external_registration_link' => $submission->registration_link,
            'social_media_link' => $submission->social_media_link,
            'organizer_name' => $submission->organizer_name,
            'organizer_contact' => $submission->organizer_contact,
            'contributor_contact' => $submission->contributor_email,
            'event_kind' => 'directory',
            'status' => $request->boolean('publish', true) ? 'published' : 'draft',
            'is_active' => true,
        ]);

        $distanceIds = is_array($submission->race_distance_ids) ? array_values(array_unique($submission->race_distance_ids)) : [];
        if ($distanceIds) {
            $event->raceDistances()->sync($distanceIds);
        }

        $submission->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_note' => $request->input('review_note'),
        ]);

        return redirect()->route('admin.event-submissions.index')->with('success', 'Submission disetujui dan event dibuat.');
    }

    public function reject(Request $request, EventSubmission $submission)
    {
        if ($submission->status !== 'pending') {
            return redirect()->route('admin.event-submissions.show', $submission)->with('error', 'Submission sudah diproses.');
        }

        $request->validate([
            'review_note' => 'required|string|max:2000',
        ]);

        $submission->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_note' => $request->input('review_note'),
        ]);

        return redirect()->route('admin.event-submissions.index')->with('success', 'Submission ditolak.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base !== '' ? $base : Str::random(10);
        $i = 2;
        while (Event::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
