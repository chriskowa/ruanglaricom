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
            $systemMessage = "Anda adalah pelatih lari pribadi (Coach lari) dari Ruang Lari. Tulis pesan WhatsApp singkat yang super natural, hangat, akrab (gunakan sapaan santai kasual, seolah mengirim chat WhatsApp pribadi dari teman atau pelatih dekat), dan memotivasi atlet.\n\n"
                . "ATURAN PENTING:\n"
                . "- JANGAN gunakan format formal atau kaku. Gunakan bahasa Indonesia santai sehari-hari (casual Indonesian).\n"
                . "- Batasi maksimal 2-3 kalimat agar tidak terlalu panjang.\n"
                . "- Sisipkan placeholder '[LINK_CALENDAR]' di bagian akhir kalimat yang pas untuk mengarahkan atlet melihat detail kalender latihan mereka.\n"
                . "- Jangan gunakan panggilan kaku seperti 'Halo Atlet', sebut nama panggilan atlet secara langsung.";
            
            $message = $openAiService->getAiResponse($prompt, $systemMessage);
            $calendarUrl = route('runner.calendar');

            if ($message) {
                $message = str_replace('[LINK_CALENDAR]', $calendarUrl, $message);
                // Fallback if AI forgot to include the calendar link
                if (!str_contains($message, $calendarUrl)) {
                    $message .= "\n\nCek kalendermu di sini ya: " . $calendarUrl;
                }
            } else {
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
            $prompt .= "Instruksi Khusus: Ingatkan dia untuk istirahat, beri semangat, dan sarankan sedikit tips active recovery ringan atau penguatan (strengthening/mobility) karena besok jadwalnya kosong. Pastikan bahasanya natural seperti asisten pelatih yang peduli. Wajib sertakan placeholder '[LINK_CALENDAR]'.";
        } else {
            $prompt .= "Jadwal Besok: {$this->sessionData['type']}\n";
            if ($distance) $prompt .= "Jarak: {$distance} km\n";
            if ($duration) $prompt .= "Durasi: {$duration}\n";
            if ($targetPace) $prompt .= "Target Pace: {$targetPace}\n";
            
            $prompt .= "Instruksi Khusus: Beri tahu jadwal besok dan beri motivasi singkat yang berapi-api agar dia semangat latihan besok pagi. Pastikan bahasanya natural seperti chat WhatsApp dari asisten pelatih yang akrab. Wajib sertakan placeholder '[LINK_CALENDAR]'.";
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
        $calendarUrl = route('runner.calendar');

        if ($isRest) {
            return "Halo {$this->user->name}! Besok jadwal program *{$this->program->title}* kamu itu *Rest Day* ya. Manfaatin waktu istirahat sebaik mungkin biar ototnya recovery dengan maksimal. Tetap semangat! 💪\n\nCek kalendermu di sini: {$calendarUrl}";
        }

        $detail = "";
        if (!empty($this->sessionData['distance'])) $detail .= " Jarak: {$this->sessionData['distance']}km.";
        if (!empty($this->sessionData['duration'])) $detail .= " Durasi: {$this->sessionData['duration']}.";
        
        return "Halo {$this->user->name}! Mengingatkan saja, besok jadwal kamu adalah sesi *{$this->sessionData['type']}* untuk program *{$this->program->title}*.{$detail} Yuk, persiapkan sepatu dan kelengkapannya malam ini biar besok tinggal gas! Semangat! 🔥\n\nDetail kalender: {$calendarUrl}";
    }
}
