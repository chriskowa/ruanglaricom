<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\PhotoTaggingPhotoTag;
use Illuminate\Http\Request;

class PhotoTaggingController extends Controller
{
    public function index()
    {
        $events = Event::where('status', 'published')
            ->orderBy('start_at', 'desc')
            ->get();

        return view('photo-tagging.index', compact('events'));
    }

    public function show(Request $request, string $slug)
    {
        $event = Event::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $bib = $request->input('bib_number');
        $photos = null;

        if ($bib) {
            $bib = trim($bib);
            // Search photos related to this event and BIB number, only if published
            $photos = PhotoTaggingPhotoTag::with('photo')
                ->where('event_id', $event->id)
                ->where('bib_number', $bib)
                ->whereHas('photo', function ($q) {
                    $q->where('status', 'published');
                })
                ->paginate(12)
                ->withQueryString();
        }

        return view('photo-tagging.show', compact('event', 'photos', 'bib'));
    }
}
