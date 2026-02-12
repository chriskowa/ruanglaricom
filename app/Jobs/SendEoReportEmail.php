<?php

namespace App\Jobs;

use App\Mail\EoReportEmail;
use App\Models\EoReportEmailDelivery;
use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEoReportEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deliveryId;

    public function __construct($deliveryId)
    {
        $this->deliveryId = $deliveryId;
    }

    public function handle(Mailer $mailer)
    {
        $delivery = EoReportEmailDelivery::find($this->deliveryId);

        if (! $delivery) {
            Log::error("EoReportEmailDelivery not found: {$this->deliveryId}");

            return;
        }

        if ($delivery->status === 'sent') {
            return;
        }

        if (! filter_var($delivery->to_email, FILTER_VALIDATE_EMAIL)) {
            $delivery->update([
                'status' => 'failed',
                'failure_code' => 'invalid_email',
                'failure_message' => 'Invalid email address',
                'attempts' => $delivery->attempts + 1,
                'last_attempt_at' => now(),
                'first_attempt_at' => $delivery->first_attempt_at ?? now(),
            ]);

            return;
        }

        try {
            $delivery->update([
                'status' => 'processing',
                'attempts' => $delivery->attempts + 1,
                'last_attempt_at' => now(),
                'first_attempt_at' => $delivery->first_attempt_at ?? now(),
            ]);

            // Fetch Data
            $filters = $delivery->filters ?? [];
            $query = Participant::query()
                ->with(['category', 'transaction'])
                ->whereHas('transaction', function ($q) use ($delivery) {
                    $q->where('event_id', $delivery->event_id);
                });

            // Note: Participant table usually doesn't have event_id directly if it belongs to transaction
            // But checking Participant model in previous turn, it didn't show event_id fillable,
            // but usually it's related via Transaction.
            // Let's check if Participant has event_id column.
            // In the previous `Read` of Participant.php, fillable didn't have event_id.
            // But Relation is belongsTo Transaction.
            // So query should be via transaction.

            if (! empty($filters['status'])) {
                $status = $filters['status'];
                $query->whereHas('transaction', function ($q) use ($status) {
                    $q->where('payment_status', $status);
                });
            }

            if (! empty($filters['date_from'])) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            }

            if (! empty($filters['date_to'])) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            }

            $participants = $query->orderByDesc('created_at')->limit(500)->get();
            // Limit to 500 to avoid memory issues for email body.
            // If more needed, should use attachment (CSV).

            $data = [
                'event' => $delivery->event,
                'participants' => $participants,
                'filters' => $filters,
                'delivery' => $delivery,
            ];

            $mailer->to($delivery->to_email)->send(new EoReportEmail($data, $delivery->subject));

            $delivery->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

        } catch (\Throwable $e) {
            Log::error("Failed to send EO Report Email: {$e->getMessage()}", [
                'delivery_id' => $this->deliveryId,
                'trace' => $e->getTraceAsString(),
            ]);

            $delivery->update([
                'status' => 'failed',
                'failure_code' => 'transport_error',
                'failure_message' => substr($e->getMessage(), 0, 255), // Truncate to fit column
            ]);

            // Do not rethrow to prevent infinite loop
        }
    }
}
