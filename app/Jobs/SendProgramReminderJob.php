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
            // Dedupe: jangan kirim lebih dari 1x per hari ke nomor yang sama.
            $phoneNormalized = preg_replace('/\D+/', '', (string) $this->user->phone);
            if ($phoneNormalized !== '') {
                if (str_starts_with($phoneNormalized, '0')) {
                    $phoneNormalized = '62' . substr($phoneNormalized, 1);
                } elseif (!str_starts_with($phoneNormalized, '62')) {
                    $phoneNormalized = '62' . $phoneNormalized;
                }
            }
            $alreadySent = \App\Models\WhatsAppLog::where('to', $phoneNormalized)
                ->where('message', 'like', 'Halo ' . $this->user->name . '%')
                ->where('created_at', '>=', now()->startOfDay())
                ->exists();
            if ($alreadySent) {
                Log::info("Skipping program reminder for User #{$this->user->id}: already sent today.");
                return;
            }

            // Get user profile data for AI context
            $profileData = $profileService->getProfile($this->user);
            
            // Build the prompt based on session type (Rest vs Run)
            $prompt = $this->buildPrompt($profileData);

            // Generate message using OpenAI
            $systemMessage = "Anda adalah pelatih lari (Coach lari) Ruang Lari. Tulis pesan WhatsApp singkat, padat, dan langsung fokus pada menu latihan program besok.\n\n"
                . "ATURAN:\n"
                . "- Tulis pesan yang sangat singkat (maksimal 1-2 kalimat) dan langsung ke intinya.\n"
                . "- Gunakan bahasa Indonesia santai dan akrab sehari-hari, sebut nama panggilan atlet secara langsung.\n"
                . "- Jangan gunakan emoji sama sekali di dalam pesan.\n"
                . "- Jangan gunakan format markdown (seperti *bold* atau _miring_). Tulis teks polos saja.";
            
            $message = $openAiService->getAiResponse($prompt, $systemMessage);

            // Link langsung ke kalender (tanpa token login agar tidak di-flag spam/phishing).
            $calendarUrl = route('runner.calendar');

            if ($message) {
                $message = $this->sanitizeMessage($message);
                $message = str_replace('[LINK_CALENDAR]', '', $message);
            } else {
                // Fallback message if OpenAI fails
                $message = $this->getFallbackMessage($calendarUrl);
            }

            // Tambahkan footer berhenti berlangganan (anti-spam WA).
            $message .= "\n\nBalas STOP untuk berhenti menerima pengingat.";

            // Send via WhatsApp
            WhatsApp::send($this->user->phone, $message);
            
            Log::info("Program reminder sent to User #{$this->user->id} via WA.");

        } catch (\Exception $e) {
            Log::error("Failed to send program reminder to User #{$this->user->id}: " . $e->getMessage());
        }
    }

    /**
     * Bersihkan output AI agar aman dikirim via WhatsApp:
     * - hapus markdown (*bold*, _miring_)
     * - hapus emoji
     * - normalisasi whitespace
     */
    private function sanitizeMessage(string $message): string
    {
        // Hapus formatting markdown
        $message = preg_replace('/[*_~`]+/', '', $message);
        // Hapus emoji (range unicode)
        $message = preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2190}-\x{21FF}\x{2B00}-\x{2BFF}\x{FE00}-\x{FE0F}]/u', '', $message);
        // Rapikan spasi berlebih
        $message = preg_replace('/\s+/', ' ', $message);
        return trim($message);
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
        $description = $this->sessionData['description'] ?? $this->sessionData['notes'] ?? $this->sessionData['instruction'] ?? '';
        
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
            $prompt .= "Instruksi: Tulis pesan singkat yang hangat agar atlet beristirahat dengan baik besok.";
        } else {
            $prompt .= "Jadwal Besok: {$this->sessionData['type']}\n";
            if ($distance) $prompt .= "Jarak: {$distance} km\n";
            if ($duration) $prompt .= "Durasi: {$duration}\n";
            if ($targetPace) $prompt .= "Target Pace: {$targetPace}\n";
            if ($description) $prompt .= "Deskripsi Latihan (Instruksi Coach): {$description}\n";
            
            $prompt .= "Instruksi: Informasikan menu latihan besok secara singkat berdasarkan deskripsi latihan dari coach, dan beri motivasi ringkas agar semangat.";
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
            return "Halo {$this->user->name}, besok jadwal program {$this->program->title} kamu adalah Rest Day ya. Selamat beristirahat! Selengkapnya: {$calendarUrl}";
        }

        $description = $this->sessionData['description'] ?? $this->sessionData['notes'] ?? $this->sessionData['instruction'] ?? '';

        $detail = "";
        if (!empty($description)) {
            $detail .= "\n- Deskripsi: {$description}";
        }
        if (!empty($this->sessionData['distance'])) {
            $detail .= "\n- Jarak: {$this->sessionData['distance']} km";
        }
        if (!empty($this->sessionData['duration'])) {
            $detail .= "\n- Durasi: {$this->sessionData['duration']}";
        }
        if (!empty($this->sessionData['target_pace'])) {
            $detail .= "\n- Target Pace: {$this->sessionData['target_pace']}";
        }
        
        return "Halo {$this->user->name}, besok jadwal kamu adalah {$this->sessionData['type']} untuk program {$this->program->title}."
            . (!empty($detail) ? "\n\nDetail latihan:{$detail}" : "")
            . "\n\nSemangat! Detail latihan: {$calendarUrl}";
    }
}
