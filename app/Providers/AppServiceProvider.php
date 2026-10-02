<?php

namespace App\Providers;

use App\Models\KalenderPelari\CalendarProject;
use App\Models\Program;
use App\Policies\CalendarProjectPolicy;
use App\Policies\ProgramPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Program::class => ProgramPolicy::class,
        CalendarProject::class => CalendarProjectPolicy::class,
    ];

    public function register(): void
    {
    }

    public function boot(): void
    {
        Gate::policy(Program::class, ProgramPolicy::class);
        Gate::policy(CalendarProject::class, CalendarProjectPolicy::class);

        $this->enforceCanonicalUrl();

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

    private function enforceCanonicalUrl(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $request = request();
        $host = $request->getHost();
        if (!$host) {
            return;
        }

        $isRuangLariProd = str_ends_with($host, 'ruanglari.com') || str_ends_with($host, 'ruanglari.id');
        if (!$isRuangLariProd) {
            return;
        }

        $canonicalHost = $host;
        if (str_starts_with($host, 'app.')) {
            $canonicalHost = substr($host, 4);
        }

        $isSecure = $request->isSecure()
            || $request->headers->get('X-Forwarded-Proto') === 'https'
            || $request->headers->get('X-Forwarded-Ssl') === 'on';
        $scheme = $isSecure ? 'https' : 'http';
        $root = $scheme . '://' . $canonicalHost;

        URL::forceRootUrl($root);
        if ($isSecure) {
            URL::forceScheme('https');
        }
    }
}
