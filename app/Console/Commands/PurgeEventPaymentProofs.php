<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PurgeEventPaymentProofs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:purge-payment-proofs {--days=7 : Jumlah hari setelah event selesai untuk menghapus bukti transfer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus otomatis berkas fisik bukti transfer pembayaran H+7 setelah event berakhir demi menghemat penyimpanan server.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        if ($days < 1) {
            $days = 7;
        }

        $cutoff = Carbon::now()->subDays($days);
        $this->info("Memeriksa event yang telah berakhir sebelum {$cutoff->format('Y-m-d H:i:s')} (H+{$days})...");

        // Cari event yang sudah lewat batas H+days
        $pastEvents = Event::where(function ($q) use ($cutoff) {
            $q->where(function ($sub) use ($cutoff) {
                $sub->whereNotNull('end_at')
                    ->where('end_at', '<=', $cutoff);
            })->orWhere(function ($sub) use ($cutoff) {
                $sub->whereNull('end_at')
                    ->whereNotNull('start_at')
                    ->where('start_at', '<=', $cutoff);
            });
        })->get();

        if ($pastEvents->isEmpty()) {
            $this->info('Tidak ada event yang memenuhi kriteria purge saat ini.');
            return Command::SUCCESS;
        }

        $totalPurged = 0;
        $totalBytesFreed = 0;

        foreach ($pastEvents as $event) {
            $transactions = Transaction::where('event_id', $event->id)
                ->whereNotNull('payment_proof')
                ->where('payment_proof', '!=', '')
                ->get();

            if ($transactions->isEmpty()) {
                continue;
            }

            $eventPurgedCount = 0;

            foreach ($transactions as $transaction) {
                $filePath = $transaction->payment_proof;

                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    $fileSize = Storage::disk('public')->size($filePath);
                    try {
                        Storage::disk('public')->delete($filePath);
                        $totalBytesFreed += $fileSize;
                    } catch (\Throwable $e) {
                        Log::warning("Gagal menghapus file bukti transfer: {$filePath}", ['error' => $e->getMessage()]);
                    }
                }

                $transaction->update([
                    'payment_proof' => null,
                    'proof_archived_at' => now(),
                ]);

                $totalPurged++;
                $eventPurgedCount++;
            }

            // Bersihkan folder event jika sudah kosong
            $eventProofDir = 'payment_proofs/' . $event->id;
            try {
                if (Storage::disk('public')->exists($eventProofDir)) {
                    $remainingFiles = Storage::disk('public')->files($eventProofDir);
                    if (empty($remainingFiles)) {
                        Storage::disk('public')->deleteDirectory($eventProofDir);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore folder cleanup errors
            }

            if ($eventPurgedCount > 0) {
                $this->line("Event #{$event->id} ({$event->name}): {$eventPurgedCount} bukti transfer dibersihkan.");
            }
        }

        $mbFreed = round($totalBytesFreed / (1024 * 1024), 2);
        $summary = "Selesai! {$totalPurged} bukti transfer berhasil dihapus, membebaskan {$mbFreed} MB ruang server.";
        $this->info($summary);
        Log::info("PurgeEventPaymentProofs: {$summary}");

        return Command::SUCCESS;
    }
}
