<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RunThreadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $creator = $this->creator;
        $participants = [];

        if ($this->relationLoaded('participants')) {
            foreach ($this->participants as $p) {
                $user = $p->user;
                if ($user) {
                    $participants[] = [
                        'participant_id' => $p->id,
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'avatar_url' => $user->avatar_url,
                        'status' => $p->status, // joined, pending, rejected
                        'joined_at' => optional($p->created_at)->toISOString(),
                    ];
                }
            }
        }

        $joinedCount = count(array_filter($participants, fn ($p) => $p['status'] === 'joined'));

        $startDateTimeStr = $this->start_date . ' ' . ($this->start_time ?: '06:00');
        $startCarbon = null;
        try {
            $startCarbon = Carbon::parse($startDateTimeStr);
        } catch (\Exception $e) {
            $startCarbon = null;
        }

        $gpxUrl = null;
        if ($this->gpx_path) {
            $gpxUrl = Str::startsWith($this->gpx_path, ['http://', 'https://'])
                ? $this->gpx_path
                : asset('storage/' . ltrim($this->gpx_path, '/'));
        }

        $recapUrl = null;
        if ($this->recap_image_url) {
            $recapUrl = Str::startsWith($this->recap_image_url, ['http://', 'https://'])
                ? $this->recap_image_url
                : asset('storage/' . ltrim($this->recap_image_url, '/'));
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => Str::slug($this->title),
            'start_date' => $this->start_date,
            'start_time' => $this->start_time,
            'start_datetime' => optional($startCarbon)->toISOString(),
            'formatted_date' => optional($startCarbon)->translatedFormat('l, d F Y') ?: $this->start_date,
            'start_location_name' => $this->start_location_name,
            'latitude' => (float) $this->start_latitude,
            'longitude' => (float) $this->start_longitude,
            'distance_km' => (float) $this->run_distance_km,
            'target_pace' => $this->target_pace,
            'max_participants' => (int) $this->max_participants,
            'joined_participants_count' => $joinedCount,
            'available_slots' => max(0, ((int) $this->max_participants) - $joinedCount),
            'notes' => $this->notes,
            'beginner_friendly' => (bool) $this->beginner_friendly,
            'women_only' => (bool) $this->women_only,
            'approval_required' => (bool) $this->approval_required,
            'status' => $this->status ?: 'open',
            'gpx_url' => $gpxUrl,
            'recap_image_url' => $recapUrl,
            'creator' => $creator ? [
                'id' => $creator->id,
                'name' => $creator->name,
                'username' => $creator->username,
                'avatar_url' => $creator->avatar_url,
                'buddy_rating' => $creator->buddy_rating ?: 5.0,
            ] : null,
            'participants' => $participants,
            'created_at' => optional($this->created_at)->toISOString(),
        ];
    }
}
