<?php

namespace App\Jobs;

use App\Models\EoEmailBlast;
use App\Models\EoEmailBlastDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessEoEmailBlast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $blast;

    /**
     * Create a new job instance.
     */
    public function __construct(EoEmailBlast $blast)
    {
        $this->blast = $blast;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->blast->source_type === 'csv' && $this->blast->csv_path) {
            $path = Storage::path($this->blast->csv_path);
            
            if (($handle = fopen($path, "r")) !== FALSE) {
                $headers = fgetcsv($handle, 10000, ",");
                if ($headers) {
                    $normalizedHeaders = array_map(function($header) {
                        return Str::slug(trim($header), '_');
                    }, $headers);

                    $emailColumnIndex = array_search(Str::slug($this->blast->email_column, '_'), $normalizedHeaders);
                    $nameColumnIndex = $this->blast->name_column ? array_search(Str::slug($this->blast->name_column, '_'), $normalizedHeaders) : false;

                    if ($emailColumnIndex !== false) {
                        $targetCount = 0;
                        while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                            $email = trim($data[$emailColumnIndex] ?? '');
                            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $name = $nameColumnIndex !== false ? trim($data[$nameColumnIndex] ?? '') : null;
                                
                                $payload = [];
                                foreach ($normalizedHeaders as $index => $key) {
                                    $payload[$key] = $data[$index] ?? '';
                                }

                                // Avoid duplicates
                                $exists = EoEmailBlastDelivery::where('eo_email_blast_id', $this->blast->id)
                                    ->where('to_email', $email)
                                    ->exists();

                                if (!$exists) {
                                    EoEmailBlastDelivery::create([
                                        'eo_email_blast_id' => $this->blast->id,
                                        'to_email' => $email,
                                        'to_name' => $name,
                                        'payload' => $payload,
                                        'status' => 'pending'
                                    ]);
                                    $targetCount++;
                                }
                            }
                        }

                        $this->blast->target_count = $targetCount;
                    }
                }
                fclose($handle);
            }
        }

        $this->blast->status = 'processing';
        $this->blast->save();

        // Dispatch Send jobs
        EoEmailBlastDelivery::where('eo_email_blast_id', $this->blast->id)
            ->where('status', 'pending')
            ->chunk(100, function ($deliveries) {
                foreach ($deliveries as $delivery) {
                    $delivery->update(['status' => 'queued']);
                    SendEoEmailBlastDelivery::dispatch($delivery);
                }
            });
    }
}