<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageUploadService;

class ConvertExistingImagesToWebp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:convert-webp 
                            {--dry-run : Simulate the migration process without deleting or writing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert existing images in database and public storage to WebP format';

    protected ImageUploadService $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        parent::__construct();
        $this->imageService = $imageService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('--- DRY RUN MODE ENABLED ---');
        }

        $this->info('Starting existing image conversion to WebP...');

        $convertedCount = 0;
        $failedCount = 0;

        // 1. Users table (avatar)
        $convertedCount += $this->processTable('users', 'avatar', 'avatars', $dryRun, $failedCount);

        // 2. Events table
        $eventColumns = ['hero_image', 'logo_image', 'floating_image', 'medal_image', 'jersey_image'];
        foreach ($eventColumns as $col) {
            $convertedCount += $this->processTable('events', $col, 'events', $dryRun, $failedCount);
        }

        // 3. Event Submissions table
        $convertedCount += $this->processTable('event_submissions', 'banner', 'event-submissions', $dryRun, $failedCount);

        // 4. Marketplace Product Images table
        if (\Schema::hasTable('marketplace_product_images')) {
            $convertedCount += $this->processTable('marketplace_product_images', 'image_path', 'marketplace/products', $dryRun, $failedCount);
        }

        // 5. Marketplace Brands table
        if (\Schema::hasTable('marketplace_brands')) {
            $convertedCount += $this->processTable('marketplace_brands', 'logo', 'marketplace/brands', $dryRun, $failedCount);
        }

        $this->info("Migration completed! Successfully processed {$convertedCount} images. Failed/Skipped: {$failedCount}.");

        return Command::SUCCESS;
    }

    protected function processTable(string $table, string $column, string $folder, bool $dryRun, int &$failedCount): int
    {
        if (!\Schema::hasTable($table) || !\Schema::hasColumn($table, $column)) {
            return 0;
        }

        $records = DB::table($table)->whereNotNull($column)->where($column, '!=', '')->get();
        $count = 0;

        foreach ($records as $record) {
            $path = $record->{$column};

            // Skip if already webp or external URL
            if (str_ends_with(strtolower($path), '.webp') || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                continue;
            }

            // Standardize relative path
            $relativePath = ltrim(str_replace('/storage/', '', $path), '/');
            $fullDiskPath = Storage::disk('public')->path($relativePath);

            if (!file_exists($fullDiskPath)) {
                $failedCount++;
                continue;
            }

            if ($dryRun) {
                $this->line("[DRY RUN] Would convert: {$table} #{$record->id} ({$column}): {$relativePath}");
                $count++;
                continue;
            }

            try {
                // Convert to WebP single file
                $newWebpPath = $this->imageService->uploadSingle($fullDiskPath, $folder);

                // Update database
                DB::table($table)->where('id', $record->id)->update([
                    $column => $newWebpPath,
                ]);

                // Delete old file
                if (file_exists($fullDiskPath) && strtolower($newWebpPath) !== strtolower($relativePath)) {
                    @unlink($fullDiskPath);
                }

                $this->info("Converted: {$table} #{$record->id} ({$column}) -> {$newWebpPath}");
                $count++;
            } catch (\Throwable $e) {
                $this->error("Failed converting {$table} #{$record->id}: " . $e->getMessage());
                $failedCount++;
            }
        }

        return $count;
    }
}
