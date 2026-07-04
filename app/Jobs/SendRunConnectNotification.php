<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRunConnectNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userIds;
    protected $type;
    protected $title;
    protected $message;
    protected $referenceType;
    protected $referenceId;

    /**
     * Create a new job instance.
     */
    public function __construct($userIds, $type, $title, $message, $referenceType = null, $referenceId = null)
    {
        $this->userIds = is_array($userIds) ? $userIds : [$userIds];
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->referenceType = $referenceType;
        $this->referenceId = $referenceId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->userIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'type' => $this->type,
                'title' => $this->title,
                'message' => $this->message,
                'reference_type' => $this->referenceType,
                'reference_id' => $this->referenceId,
            ]);
        }
    }
}
