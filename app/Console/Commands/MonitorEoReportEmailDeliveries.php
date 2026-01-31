<?php

namespace App\Console\Commands;

use App\Models\EoReportEmailDelivery;
use App\Models\Notification;
use Illuminate\Console\Command;

class MonitorEoReportEmailDeliveries extends Command
{
    protected $signature = 'eo-report-emails:monitor';

    protected $description = 'Notify EO when report emails fail to send within 5 minutes after first attempt.';

    public function handle(): int
    {
        $cutoff = now()->subMinutes(5);

        EoReportEmailDelivery::query()
            ->with('event')
            ->whereNull('sent_at')
            ->whereNull('failure_notified_at')
            ->whereNotNull('first_attempt_at')
            ->where('first_attempt_at', '<=', $cutoff)
            ->whereIn('status', ['failed', 'pending', 'processing'])
            ->orderBy('id')
            ->chunkById(200, function ($deliveries) {
                foreach ($deliveries as $delivery) {
                    $eventName = $delivery->event?->name ?? 'Event';
                    $status = strtoupper((string) $delivery->status);
                    $toEmail = (string) $delivery->to_email;

                    Notification::create([
                        'user_id' => $delivery->eo_user_id,
                        'type' => 'eo_report_email_failed',
                        'title' => 'Email laporan gagal terkirim',
                        'message' => "Pengiriman email laporan untuk {$eventName} ke {$toEmail} belum berhasil dalam 5 menit. Status: {$status}.",
                        'reference_type' => EoReportEmailDelivery::class,
                        'reference_id' => $delivery->id,
                        'is_read' => false,
                    ]);

                    $delivery->failure_notified_at = now();
                    $delivery->save();
                }
            });

        return self::SUCCESS;
    }
}

