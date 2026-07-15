<?php

namespace App\Jobs\RunningAnalysis;

use App\Models\RunningAnalysis\Trial;
use App\Services\RunningAnalysis\ReportBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeTrialJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $trial;

    /**
     * Create a new job instance.
     */
    public function __construct(Trial $trial)
    {
        $this->trial = $trial;
    }

    /**
     * Execute the job.
     */
    public function handle(ReportBuilder $builder): void
    {
        @set_time_limit(300);

        try {
            $this->trial->update(['status' => Trial::STATUS_ANALYZING]);
            
            $builder->process($this->trial);

            Log::info("Trial {$this->trial->id} analyzed successfully.");
        } catch (\Exception $e) {
            $this->trial->update([
                'status' => Trial::STATUS_FAILED,
                'invalid_reason' => $e->getMessage()
            ]);
            Log::error("Failed to analyze trial {$this->trial->id}: " . $e->getMessage());
            
            throw $e;
        }
    }
}
