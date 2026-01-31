<?php

namespace App\Jobs;

use App\Mail\EoReportEmail;
use App\Models\EoReportEmailDelivery;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendEoReportEmail implements ShouldQueue
{
    use Queueable;

    protected int $deliveryId;

    public function __construct(int $deliveryId)
    {
        $this->deliveryId = $deliveryId;
    }

    public function handle(Mailer $mailer): void
    {
        $delivery = EoReportEmailDelivery::query()->with(['event', 'eoUser'])->find($this->deliveryId);
        if (! $delivery) {
            Log::warning('SendEoReportEmail: delivery not found', ['delivery_id' => $this->deliveryId]);
            return;
        }

        $now = now();

        $delivery->attempts = (int) $delivery->attempts + 1;
        $delivery->first_attempt_at = $delivery->first_attempt_at ?: $now;
        $delivery->last_attempt_at = $now;
        $delivery->status = 'processing';
        $delivery->save();

        $toEmail = (string) $delivery->to_email;
        if (! filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $delivery->status = 'failed';
            $delivery->failure_code = 'invalid_email';
            $delivery->failure_message = 'Invalid email address.';
            $delivery->save();

            Log::warning('EO report email invalid recipient', [
                'delivery_id' => $delivery->id,
                'event_id' => $delivery->event_id,
                'to_email' => $toEmail,
            ]);

            return;
        }

        try {
            $mailer->to($toEmail)->send(new EoReportEmail($delivery));

            $delivery->status = 'sent';
            $delivery->sent_at = $now;
            $delivery->failure_code = null;
            $delivery->failure_message = null;
            $delivery->save();

            Log::info('EO report email sent', [
                'delivery_id' => $delivery->id,
                'event_id' => $delivery->event_id,
                'to_email' => $toEmail,
                'attempts' => (int) $delivery->attempts,
            ]);
        } catch (\Throwable $e) {
            $delivery->status = 'failed';
            $delivery->failure_code = $this->classifyFailure($e);
            $delivery->failure_message = mb_substr((string) $e->getMessage(), 0, 2000);
            $delivery->save();

            Log::error('EO report email failed', [
                'delivery_id' => $delivery->id,
                'event_id' => $delivery->event_id,
                'to_email' => $toEmail,
                'attempts' => (int) $delivery->attempts,
                'failure_code' => $delivery->failure_code,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function classifyFailure(\Throwable $e): string
    {
        $message = strtolower((string) $e->getMessage());

        if (str_contains($message, 'address') && str_contains($message, 'invalid')) {
            return 'invalid_email';
        }

        if (str_contains($message, 'connection') || str_contains($message, 'timeout') || str_contains($message, 'could not connect')) {
            return 'transport_error';
        }

        if (str_contains($message, '5') && (str_contains($message, 'smtp') || str_contains($message, 'server'))) {
            return 'server_error';
        }

        if (str_contains($message, 'bounce') || str_contains($message, 'bounced')) {
            return 'bounce';
        }

        return 'unknown';
    }
}
