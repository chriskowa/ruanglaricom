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
            $systemMessage = "Anda adalah pelatih lari (Coach lari) Ruang Lari. Tulis pesan WhatsApp pengingat jadwal program lari besok.\n\n"
                . "Wajib sertakan rincian latihan berikut secara jelas pada baris terpisah (gunakan newline/baris baru):\n"
                . "- Jarak (km)\n"
                . "- Target Pace (WAJIB sebutkan target pace persisnya dalam min/km)\n"
                . "- Rincian/Instruksi Latihan\n\n"
                . "ATURAN FORMAT:\n"
                . "- Gunakan bahasa Indonesia santai dan akrab sehari-hari, sebut nama panggilan atlet secara langsung.\n"
                . "- WAJIB gunakan baris baru (newline) untuk memisahkan setiap poin rincian latihan agar mudah dibaca.\n"
                . "- JANGAN gabungkan seluruh pesan menjadi 1 alinea/paragraf panjang tanpa baris baru.\n"
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
                $message = $this->getFallbackMessage($profileData, $calendarUrl);
            }

            // Tambahkan footer berhenti berlangganan (anti-spam WA).
            $message .= "\n\nBalas STOP untuk berhenti menerima pengingat.";

            // Send via WhatsApp
            WhatsApp::send($this->user->phone, $message, 'reminder');
            
            Log::info("Program reminder sent to User #{$this->user->id} via WA.");

        } catch (\Exception $e) {
            Log::error("Failed to send program reminder to User #{$this->user->id}: " . $e->getMessage());
        }
    }

    /**
     * Bersihkan output AI agar aman dikirim via WhatsApp:
     * - hapus markdown (*bold*, _miring_)
     * - hapus emoji
     * - normalisasi whitespace per baris tanpa merusak newline (\n)
     */
    private function sanitizeMessage(string $message): string
    {
        // Hapus formatting markdown
        $message = preg_replace('/[*_~`]+/', '', $message);
        // Hapus emoji (range unicode)
        $message = preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2190}-\x{21FF}\x{2B00}-\x{2BFF}\x{FE00}-\x{FE0F}]/u', '', $message);
        // Normalisasi spasi horizontal per baris
        $message = preg_replace('/[ \t]+/', ' ', $message);
        // Batasi newline berturut-turut maksimal 2
        $message = preg_replace('/\n{3,}/', "\n\n", $message);
        return trim($message);
    }

    /**
     * Resolve target pace based on session type and athlete VDOT profile paces
     */
    private function resolveTargetPace(array $sessionData, array $profileData): string
    {
        $targetPaceVal = $sessionData['target_pace'] ?? $sessionData['pace'] ?? '';
        if (!empty($targetPaceVal)) {
            return (string) $targetPaceVal;
        }

        $type = strtolower($sessionData['type'] ?? $sessionData['title'] ?? '');
        $paces = $profileData['paces'] ?? [];

        if (in_array($type, ['easy_run', 'easy', 'recovery', 'recovery_run', 'run'])) {
            if (!empty($paces['E_high']) && !empty($paces['E_low'])) {
                return $this->formatMinPerKm($paces['E_high']) . ' - ' . $this->formatMinPerKm($paces['E_low']) . ' /km (Easy Pace)';
            } elseif (isset($paces['E'])) {
                return '~' . $this->formatMinPerKm($paces['E']) . ' /km (Easy Pace)';
            } elseif (isset($paces['easy'])) {
                return '~' . $this->formatMinPerKm($paces['easy']) . ' /km (Easy Pace)';
            }
            return 'Zona aerobik ringan (Easy Pace)';
        } elseif (in_array($type, ['tempo', 'threshold', 'tempo_run'])) {
            $tPace = isset($paces['T']) ? $this->formatMinPerKm($paces['T']) : ($paces['threshold'] ?? null);
            return $tPace ? '~' . $this->formatMinPerKm($tPace) . ' /km (Tempo/Threshold)' : 'Zona threshold terkontrol';
        } elseif (in_array($type, ['repetition', 'speed', 'repeats'])) {
            $rPace = isset($paces['R']) ? $this->formatMinPerKm($paces['R']) : ($paces['repetition'] ?? null);
            $iPace = isset($paces['I']) ? $this->formatMinPerKm($paces['I']) : ($paces['interval'] ?? null);
            return $rPace ? '~' . $this->formatMinPerKm($rPace) . ' /km (Repetition Pace)' : ($iPace ? '~' . $this->formatMinPerKm($iPace) . ' /km (Interval Pace)' : 'Repetition Pace (Kecepatan Neuromuskular)');
        } elseif (in_array($type, ['interval', 'vo2max'])) {
            $iPace = isset($paces['I']) ? $this->formatMinPerKm($paces['I']) : ($paces['interval'] ?? null);
            return $iPace ? '~' . $this->formatMinPerKm($iPace) . ' /km (Interval Pace)' : 'Interval Pace VO2max';
        } elseif (in_array($type, ['long_run', 'long'])) {
            $mPace = isset($paces['M']) ? $this->formatMinPerKm($paces['M']) : ($paces['marathon'] ?? null);
            $ePace = isset($paces['E']) ? $this->formatMinPerKm($paces['E']) : ($paces['easy'] ?? null);
            return $mPace ? '~' . $this->formatMinPerKm($mPace) . ' /km (Marathon Pace)' : ($ePace ? '~' . $this->formatMinPerKm($ePace) . ' /km (Endurance)' : 'Zona endurance terkontrol');
        }

        return 'Sesuaikan pace dengan instruksi program';
    }

    private function formatMinPerKm($minutes): string
    {
        if (is_string($minutes) && str_contains($minutes, ':')) return $minutes;
        $m = floor((float)$minutes);
        $s = round(((float)$minutes - $m) * 60);
        return sprintf('%d:%02d', $m, $s);
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
        $targetPace = $this->resolveTargetPace($this->sessionData, $profileData);
        $description = $this->sessionData['description'] ?? $this->sessionData['notes'] ?? $this->sessionData['instruction'] ?? '';
        
        $pacesInfo = "";
        if (!empty($profileData['paces'])) {
            $paces = $profileData['paces'];
            $pacesInfo = "Pace VDOT Atlet: Easy (" . ($paces['easy'] ?? $paces['E'] ?? '-') . "), Tempo (" . ($paces['threshold'] ?? $paces['T'] ?? '-') . "), Interval (" . ($paces['interval'] ?? $paces['I'] ?? '-') . ").";
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
            $prompt .= "Target Pace Spesifik: {$targetPace}\n";
            if ($description) $prompt .= "Deskripsi Latihan (Instruksi Coach): {$description}\n";
            
            $prompt .= "Instruksi: Tulis pesan WhatsApp dengan baris terpisah yang rapi. Informasikan menu latihan besok, jarak, target pace ({$targetPace}), dan rincian instruksi coach secara jelas.";
        }

        return $prompt;
    }

    /**
     * Fallback message if OpenAI fails
     */
    private function getFallbackMessage(array $profileData, string $calendarUrl): string
    {
        $type = strtolower($this->sessionData['type'] ?? 'rest');
        $isRest = in_array($type, ['rest', 'rest day', 'libur']);

        if ($isRest) {
            return "Halo {$this->user->name}, besok jadwal program {$this->program->title} kamu adalah Rest Day ya. Selamat beristirahat! Selengkapnya: {$calendarUrl}";
        }

        $sessionName = $this->sessionData['session_name'] ?? $this->sessionData['title'] ?? $this->sessionData['name'] ?? ucfirst(str_replace('_', ' ', $type));
        $distance = !empty($this->sessionData['distance']) ? "{$this->sessionData['distance']} km" : (!empty($this->sessionData['target_distance']) ? "{$this->sessionData['target_distance']} km" : '-');
        $targetPace = $this->resolveTargetPace($this->sessionData, $profileData);
        $description = $this->sessionData['description'] ?? $this->sessionData['notes'] ?? $this->sessionData['instruction'] ?? 'Lakukan latihan sesuai instruksi program.';

        return "Halo {$this->user->name}, besok kamu ada sesi: {$sessionName} untuk program {$this->program->title}.\n\n"
            . "- Jarak: {$distance}\n"
            . "- Target Pace: {$targetPace}\n"
            . "- Deskripsi: {$description}\n\n"
            . "Detail kalender: {$calendarUrl}";
    }
}
