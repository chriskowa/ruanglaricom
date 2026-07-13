<?php

namespace App\Jobs;

use App\Helpers\WhatsApp;
use App\Models\User;
use App\Models\Program;
use App\Services\OpenAiService;
use App\Services\RunningProfileService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendProgramReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $sessionData;
    public $program;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, array $sessionData, Program $program)
    {
        $this->user = $user;
        $this->sessionData = $sessionData;
        $this->program = $program;
    }

    /**
     * Execute the job.
     */
    public function handle(OpenAiService $openAiService, RunningProfileService $profileService): void
    {
        if (!$this->user->phone || !$this->user->is_receive_wa) {
            Log::info("Skipping program reminder for User #{$this->user->id}: No phone number or WA notifications disabled.");
            return;
        }

        try {
            // Get user profile data for AI context
            $profileData = $profileService->getProfile($this->user);
            
            // Build the prompt based on session type (Rest vs Run)
            $prompt = $this->buildPrompt($profileData);

            // Generate message using OpenAI
            $systemMessage = "Anda adalah pelatih lari (Coach lari) Ruang Lari. Tulis pesan WhatsApp singkat, padat, dan langsung fokus pada menu latihan program besok serta link program.\n\n"
                . "ATURAN:\n"
                . "- Tulis pesan yang sangat singkat (maksimal 1-2 kalimat) dan langsung ke intinya.\n"
                . "- Gunakan bahasa Indonesia santai dan akrab sehari-hari, sebut nama panggilan atlet secara langsung.\n"
                . "- Wajib sertakan placeholder '[LINK_CALENDAR]' di akhir pesan untuk akses detail program.";
            
            $message = $openAiService->getAiResponse($prompt, $systemMessage);
            $calendarUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'login.token',
                now()->addDays(7),
                ['user' => $this->user->id, 'redirect' => route('runner.calendar')]
            );

            // Shorten the temporary calendar URL
            $shortLink = \App\Models\ShortLink::where('original_url', $calendarUrl)->first();
            if ($shortLink) {
                $calendarUrlShort = route('shortlink.redirect', $shortLink->code);
            } else {
                do {
                    $code = \Illuminate\Support\Str::random(6);
                } while (\App\Models\ShortLink::where('code', $code)->exists());

                $newLink = \App\Models\ShortLink::create([
                    'code' => $code,
                    'original_url' => $calendarUrl,
                ]);
                $calendarUrlShort = route('shortlink.redirect', $newLink->code);
            }

            if ($message) {
                $message = str_replace('[LINK_CALENDAR]', $calendarUrlShort, $message);
                // Fallback if AI forgot to include the calendar link
                if (!str_contains($message, $calendarUrlShort)) {
                    $message .= "\n\nCek kalendermu di sini ya: " . $calendarUrlShort;
                }
            } else {
                // Fallback message if OpenAI fails
                $message = $this->getFallbackMessage($calendarUrlShort);
            }

            // Send via WhatsApp
            WhatsApp::send($this->user->phone, $message);
            
            Log::info("Program reminder sent to User #{$this->user->id} via WA.");

        } catch (\Exception $e) {
            Log::error("Failed to send program reminder to User #{$this->user->id}: " . $e->getMessage());
        }
    }

    /**
     * Build the prompt for OpenAI
     */
    private function buildPrompt(array $profileData): string
    {
        $type = strtolower($this->sessionData['type'] ?? 'rest');
        $isRest = in_array($type, ['rest', 'rest day', 'libur']);
        
        $distance = $this->sessionData['distance'] ?? '';
        $duration = $this->sessionData['duration'] ?? '';
        $targetPace = $this->sessionData['target_pace'] ?? '';
        
        $pacesInfo = "";
        if (!empty($profileData['paces'])) {
            $paces = $profileData['paces'];
            $pacesInfo = "Pace Latihan: Easy (" . ($paces['easy'] ?? '-') . "), Tempo (" . ($paces['threshold'] ?? '-') . "), Interval (" . ($paces['interval'] ?? '-') . ").";
        }

        $prompt = "Buatkan pesan WhatsApp pengingat jadwal program lari besok.\n\n";
        $prompt .= "Nama Atlet: {$this->user->name}\n";
        $prompt .= "Nama Program: {$this->program->title}\n";
        if ($pacesInfo) $prompt .= $pacesInfo . "\n";

        if ($isRest) {
            $prompt .= "Jadwal Besok: REST DAY (Hari Istirahat/Pemulihan).\n";
            $prompt .= "Instruksi: Tulis pesan singkat yang hangat agar atlet beristirahat dengan baik besok. Wajib sertakan '[LINK_CALENDAR]'.";
        } else {
            $prompt .= "Jadwal Besok: {$this->sessionData['type']}\n";
            if ($distance) $prompt .= "Jarak: {$distance} km\n";
            if ($duration) $prompt .= "Durasi: {$duration}\n";
            if ($targetPace) $prompt .= "Target Pace: {$targetPace}\n";
            
            $prompt .= "Instruksi: Informasikan menu latihan besok secara singkat dan beri motivasi ringkas agar semangat. Wajib sertakan '[LINK_CALENDAR]'.";
        }

        return $prompt;
    }

    /**
     * Fallback message if OpenAI fails
     */
    private function getFallbackMessage(string $calendarUrl): string
    {
        $type = strtolower($this->sessionData['type'] ?? 'rest');
        $isRest = in_array($type, ['rest', 'rest day', 'libur']);

        if ($isRest) {
            return "Halo {$this->user->name}, besok jadwal program *{$this->program->title}* kamu adalah *Rest Day* ya. Selamat beristirahat! Selengkapnya: {$calendarUrl}";
        }

        $detail = "";
        if (!empty($this->sessionData['distance'])) $detail .= " Jarak: {$this->sessionData['distance']} km.";
        if (!empty($this->sessionData['duration'])) $detail .= " Durasi: {$this->sessionData['duration']}.";
        if (!empty($this->sessionData['target_pace'])) $detail .= " Target Pace: {$this->sessionData['target_pace']}.";
        
        return "Halo {$this->user->name}, besok jadwal kamu adalah *{$this->sessionData['type']}* untuk program *{$this->program->title}*.{$detail} Semangat! Detail latihan: {$calendarUrl}";
    }
}
