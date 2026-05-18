<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class PhotoTaggingEventController extends Controller
{
    public function index()
    {
        // Get all events from the main events table, with count of photoTaggingPhotos
        $events = Event::withCount('photoTaggingPhotos')->latest()->paginate(10);
        return view('admin.photo-tagging.events.index', compact('events'));
    }
}
