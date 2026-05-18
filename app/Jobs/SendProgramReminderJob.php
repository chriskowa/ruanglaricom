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
        if (!$this->user->phone) {
            Log::info("Skipping program reminder for User #{$this->user->id}: No phone number.");
            return;
        }

        try {
            // Get user profile data for AI context
            $profileData = $profileService->getProfile($this->user);
            
            // Build the prompt based on session type (Rest vs Run)
            $prompt = $this->buildPrompt($profileData);

            // Generate message using OpenAI
            $systemMessage = "Anda adalah 'Coach AI' dari Ruang Lari, asisten pelatih lari yang ahli, ramah, dan sangat memotivasi. Jawablah maksimal 3-4 kalimat singkat untuk pesan WhatsApp. Gunakan bahasa Indonesia kasual ala 'Coach Gaul'.";
            
            $message = $openAiService->getAiResponse($prompt, $systemMessage);

            if (!$message) {
                // Fallback message if OpenAI fails
                $message = $this->getFallbackMessage();
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
            $pacesInfo = "Pace Latihan Atlet: Easy (" . ($paces['easy'] ?? '-') . "), Tempo (" . ($paces['threshold'] ?? '-') . "), Interval (" . ($paces['interval'] ?? '-') . ").";
        }

        $prompt = "Tolong buatkan pesan WhatsApp natural untuk mengingatkan jadwal program lari besok kepada atlet saya.\n\n";
        $prompt .= "Nama Atlet: {$this->user->name}\n";
        $prompt .= "Nama Program: {$this->program->title}\n";
        $prompt .= $pacesInfo . "\n\n";

        if ($isRest) {
            $prompt .= "Jadwal Besok: REST DAY (Hari Istirahat/Pemulihan).\n";
            $prompt .= "Instruksi Khusus: Ingatkan dia untuk istirahat, beri semangat, dan sarankan sedikit tips active recovery ringan atau penguatan (strengthening/mobility) karena besok jadwalnya kosong. Pastikan bahasanya natural seperti asisten pelatih yang peduli.";
        } else {
            $prompt .= "Jadwal Besok: {$this->sessionData['type']}\n";
            if ($distance) $prompt .= "Jarak: {$distance} km\n";
            if ($duration) $prompt .= "Durasi: {$duration}\n";
            if ($targetPace) $prompt .= "Target Pace: {$targetPace}\n";
            
            $prompt .= "Instruksi Khusus: Beri tahu jadwal besok dan beri motivasi singkat yang berapi-api agar dia semangat latihan besok pagi. Pastikan bahasanya natural seperti chat WhatsApp dari asisten pelatih yang akrab.";
        }

        return $prompt;
    }

    /**
     * Fallback message if OpenAI fails
     */
    private function getFallbackMessage(): string
    {
        $type = strtolower($this->sessionData['type'] ?? 'rest');
        $isRest = in_array($type, ['rest', 'rest day', 'libur']);

        if ($isRest) {
            return "Halo {$this->user->name}! 🏃‍♂️\n\nBesok jadwal program *{$this->program->title}* kamu adalah *Rest Day*. Jangan lupa manfaatkan untuk istirahat maksimal, stretching, atau active recovery ringan ya biar otot siap buat sesi berikutnya! Tetap semangat! 💪";
        }

        $detail = "";
        if (!empty($this->sessionData['distance'])) $detail .= " Jarak: {$this->sessionData['distance']}km.";
        if (!empty($this->sessionData['duration'])) $detail .= " Durasi: {$this->sessionData['duration']}.";
        
        return "Halo {$this->user->name}! 🏃‍♂️\n\nSekadar mengingatkan, besok kamu ada jadwal *{$this->sessionData['type']}* untuk program *{$this->program->title}*.{$detail} Yuk siapkan sepatu dan outfit terbaikmu malam ini. Semangat buat besok pagi! 🔥";
    }
}
