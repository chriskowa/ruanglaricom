<?php

namespace App\Console\Commands;

use App\Jobs\ProcessEventEmailCampaign;
use App\Models\EventEmailCampaign;
use Illuminate\Console\Command;

class RunScheduledEmailCampaigns extends Command
{
    protected $signature = 'events:run-scheduled-campaigns';
    protected $description = 'Check and dispatch scheduled event email campaigns';

    public function handle()
    {
        $this->info('Checking for scheduled campaigns...');

        $campaigns = EventEmailCampaign::where('status', 'scheduled')
            ->where('type', 'absolute')
            ->where('send_at', '<=', now())
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No campaigns due for processing.');
            return 0;
        }

        foreach ($campaigns as $campaign) {
            $this->info("Dispatching campaign: {$campaign->id} - {$campaign->name}");
            
            $campaign->update(['status' => 'processing']);
            
            // Queue the campaign builder logic
            ProcessEventEmailCampaign::dispatch($campaign)->onQueue('emails-blast');
        }

        $this->info('Done dispatching campaigns.');
        return 0;
    }
}
