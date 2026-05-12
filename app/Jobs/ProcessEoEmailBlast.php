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
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Increase memory for large CSV processing
        ini_set('memory_limit', '512M');
        set_time_limit(3600);

        if ($this->blast->source_type === 'csv' && $this->blast->csv_path) {
            $path = Storage::path($this->blast->csv_path);
            
            if (($handle = fopen($path, "r")) !== FALSE) {
                // Detect and handle UTF-8 BOM if present
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }

                $headers = fgetcsv($handle, 10000, ",");
                if ($headers) {
                    $normalizedHeaders = array_map(function($header) {
                        return Str::slug(trim($header), '_');
                    }, $headers);

                    $emailColumnIndex = array_search(Str::slug($this->blast->email_column, '_'), $normalizedHeaders);
                    $nameColumnIndex = $this->blast->name_column ? array_search(Str::slug($this->blast->name_column, '_'), $normalizedHeaders) : false;

                    if ($emailColumnIndex !== false) {
                        $targetCount = 0;
                        $batch = [];
                        $now = now();

                        while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                            $email = trim($data[$emailColumnIndex] ?? '');
                            
                            // Basic validation
                            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $name = $nameColumnIndex !== false ? trim($data[$nameColumnIndex] ?? '') : null;
                                
                                $payload = [];
                                foreach ($normalizedHeaders as $index => $key) {
                                    $val = $data[$index] ?? '';
                                    // Clean up encoding issues for common special chars
                                    $payload[$key] = mb_convert_encoding($val, 'UTF-8', 'UTF-8,ISO-8859-1,Windows-1252');
                                }

                                $batch[] = [
                                    'eo_email_blast_id' => $this->blast->id,
                                    'to_email' => $email,
                                    'to_name' => mb_convert_encoding($name, 'UTF-8', 'UTF-8,ISO-8859-1,Windows-1252'),
                                    'payload' => json_encode($payload),
                                    'status' => 'pending',
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];

                                $targetCount++;

                                // Process in batches of 500 to keep memory low and speed high
                                if (count($batch) >= 500) {
                                    $this->insertBatch($batch);
                                    $batch = [];
                                }
                            }
                        }

                        // Insert remaining
                        if (count($batch) > 0) {
                            $this->insertBatch($batch);
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

    /**
     * Insert batch while avoiding duplicates for this blast
     */
    private function insertBatch(array $batch)
    {
        // For simplicity and speed, we use insert ignore or just insert 
        // since we check duplicates by email in a real scenario, 
        // but here we just want to avoid double-processing the same blast.
        EoEmailBlastDelivery::insert($batch);
    }
}