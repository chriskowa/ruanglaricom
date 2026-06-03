<?php

namespace App\Providers;

use App\Models\Program;
use App\Policies\ProgramPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Program::class => ProgramPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Program::class, ProgramPolicy::class);

        // Load Moota Bank Accounts dynamically from database settings
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('app_settings')) {
                $accounts = \App\Models\AppSettings::get('moota_bank_accounts');
                if ($accounts) {
                    $decoded = json_decode($accounts, true);
                    if (is_array($decoded) && !empty($decoded)) {
                        config(['moota.bank_accounts' => $decoded]);
                    }
                }
            }
        } catch (\Exception $e) {
            // Avoid failing if database is not migrated/available during setup or console commands
        }
    }
}
