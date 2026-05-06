<?php

namespace App\Jobs;

use App\Mail\EoCustomBlastEmail;
use App\Models\EoEmailBlastDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class SendEoEmailBlastDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $delivery;

    /**
     * Create a new job instance.
     */
    public function __construct(EoEmailBlastDelivery $delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->delivery->status === 'sent') {
            return;
        }

        $blast = $this->delivery->blast;
        $payload = $this->delivery->payload ?? [];

        $subject = $this->replacePlaceholders($blast->subject_template, $payload);
        $htmlBody = $this->replacePlaceholders($blast->html_template, $payload);

        try {
            Redis::throttle('eo_email_blast')
                ->allow(10)
                ->every(1)
                ->then(function () use ($subject, $htmlBody, $blast) {
                    $this->send($subject, $htmlBody, $blast);
                }, function () {
                    $this->release(1);
                });
        } catch (\Throwable $e) {
            report($e);
            $this->send($subject, $htmlBody, $blast);
        }
    }

    private function send($subject, $htmlBody, $blast): void
    {
        try {
            $this->delivery->attempts++;

            Mail::to($this->delivery->to_email)->send(new EoCustomBlastEmail($subject, $htmlBody));

            $this->delivery->update([
                'status' => 'sent',
                'rendered_subject' => $subject,
                'sent_at' => now(),
                'error_message' => null,
            ]);

            $blast->increment('sent_count');
            $this->checkBlastCompletion($blast);
        } catch (\Throwable $e) {
            $this->handleFailure($e, $blast, $subject);
        }
    }

    private function handleFailure(\Throwable $e, $blast, $subject): void
    {
        $this->delivery->update([
            'status' => 'failed',
            'rendered_subject' => $subject,
            'error_message' => $e->getMessage(),
        ]);

        $blast->increment('failed_count');
        $this->checkBlastCompletion($blast);
    }

    private function checkBlastCompletion($blast)
    {
        $totalProcessed = $blast->sent_count + $blast->failed_count;
        if ($totalProcessed >= $blast->target_count) {
            $blast->update(['status' => 'completed']);
        }
    }

    private function replacePlaceholders($template, $payload)
    {
        if (!$template) return '';
        
        return preg_replace_callback('/\{\{([a-zA-Z0-9_]+)\}\}/', function($matches) use ($payload) {
            $key = $matches[1];
            return isset($payload[$key]) ? $payload[$key] : '';
        }, $template);
    }
}