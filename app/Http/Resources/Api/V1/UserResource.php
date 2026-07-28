<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'role' => $this->role,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'avatar_url' => $this->avatar_url,
            'vdot' => $this->vdot,
            'training_paces' => $this->training_paces,
            'city_id' => $this->city_id,
            'city_name' => optional($this->city)->name,
            'weight' => $this->weight,
            'height' => $this->height,
            'weekly_volume' => $this->weekly_volume,
            'weekly_km_target' => $this->weekly_km_target,
            'pbs' => [
                '5k' => $this->pb_5k,
                '10k' => $this->pb_10k,
                'hm' => $this->pb_hm,
                'fm' => $this->pb_fm,
                'balke' => $this->pb_balke,
            ],
            'membership' => [
                'status' => $this->membership_status ?? 'free',
                'expires_at' => $this->membership_expires_at,
            ],
            'created_at' => optional($this->created_at)->toISOString(),
        ];
    }
}
