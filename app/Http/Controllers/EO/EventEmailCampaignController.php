<?php

namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventEmailCampaign;
use App\Models\Participant;
use Illuminate\Http\Request;

class EventEmailCampaignController extends Controller
{
    protected function authorizeEvent(Event $event): void
    {
        if ((int) $event->user_id !== (int) auth()->id()) {
            abort(403);
        }
    }

    public function all()
    {
        $campaigns = EventEmailCampaign::whereHas('event', function ($q) {
            $q->where('user_id', auth()->id());
        })
            ->with('event')
            ->withCount(['deliveries as total_deliveries'])
            ->withCount(['deliveries as sent_deliveries' => function ($q) {
                $q->where('status', 'sent');
            }])
            ->latest()
            ->paginate(15);

        return view('eo.email-campaigns.index', compact('campaigns'));
    }

    public function index(Event $event)
    {
        $this->authorizeEvent($event);

        $campaigns = EventEmailCampaign::where('event_id', $event->id)
            ->withCount(['deliveries as total_deliveries'])
            ->withCount(['deliveries as sent_deliveries' => function ($q) {
                $q->where('status', 'sent');
            }])
            ->latest()
            ->paginate(10);

        return view('eo.events.campaigns.index', compact('event', 'campaigns'));
    }

    public function create(Event $event)
    {
        $this->authorizeEvent($event);
        return view('eo.events.campaigns.create', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:instant,absolute', // Relative excluded for MVP
            'preset_template' => 'required|string|max:100',
            'subject' => 'required|string|max:255',
            
            // Content blocks
            'headline' => 'nullable|string|max:255',
            'body_text' => 'nullable|string',
            'cta_text' => 'nullable|string|max:100',
            'cta_url' => 'nullable|url|max:500',
            
            // Scheduling
            'send_at' => 'nullable|required_if:type,absolute|date|after:now',
            
            // Targeting
            'filter_payment' => 'nullable|array',
        ]);

        $content = [
            'headline' => $validated['headline'] ?? null,
            'body_text' => $validated['body_text'] ?? null,
            'cta_text' => $validated['cta_text'] ?? null,
            'cta_url' => $validated['cta_url'] ?? null,
        ];

        $filters = [];
        if (!empty($validated['filter_payment'])) {
            $filters['payment_status'] = $validated['filter_payment'];
        }

        $status = $validated['type'] === 'instant' ? 'processing' : 'scheduled';

        $campaign = EventEmailCampaign::create([
            'event_id' => $event->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'preset_template' => $validated['preset_template'],
            'subject' => $validated['subject'],
            'content' => $content,
            'send_at' => $validated['send_at'] ?? null,
            'filters' => $filters,
            'status' => $status,
        ]);

        // If instant, dispatch job immediately to queue deliveries
        if ($campaign->type === 'instant') {
            \App\Jobs\ProcessEventEmailCampaign::dispatch($campaign)->onQueue('emails-blast');
        }

        return redirect()->route('eo.events.campaigns.index', $event)
            ->with('success', 'Email Campaign berhasil dibuat.');
    }

    public function show(Event $event, EventEmailCampaign $campaign)
    {
        $this->authorizeEvent($event);
        
        if ($campaign->event_id !== $event->id) {
            abort(404);
        }

        $deliveries = $campaign->deliveries()->paginate(20);

        return view('eo.events.campaigns.show', compact('event', 'campaign', 'deliveries'));
    }

    public function preview(Request $request, Event $event)
    {
        $this->authorizeEvent($event);
        
        // Return rendered HTML view for preview
        $content = [
            'headline' => $request->headline,
            'body_text' => $request->body_text,
            'cta_text' => $request->cta_text,
            'cta_url' => $request->cta_url,
        ];

        // Mock participant
        $participant = new Participant([
            'name' => 'John Doe',
            'bib_number' => '1234',
        ]);

        $html = view('emails.events.campaign-preset', [
            'event' => $event,
            'participant' => $participant,
            'subjectLine' => $request->subject ?? 'Preview Subject',
            'preset' => $request->preset_template ?? 'general',
            'contentData' => $content,
        ])->render();

        return response()->json(['html' => $html]);
    }
}
