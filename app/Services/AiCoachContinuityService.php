<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AiCoachContinuityService
{
    public const DAILY_TOKEN_LIMIT = 25000;
    public const DAILY_REQUEST_LIMIT = 60;
    public const FREE_CHAT_DAILY_LIMIT = 3;
    public const ACTIVE_PROGRAM_CHAT_DAILY_LIMIT = 15;
    public const PROGRAM_PRICE_IDR = 15000;

    public function getCoachUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'ai-coach@ruanglari.com'],
            [
                'name' => 'Coach Ruang Lari',
                'password' => bcrypt(str()->random(16)),
                'role' => 'coach',
                'username' => 'coach-ai',
                'avatar' => 'images/profile/17.jpg',
                'is_active' => true,
            ]
        );
    }

    public function getDailyQuotaStatus(User $user): array
    {
        $row = DB::table('user_ai_quotas')
            ->where('user_id', $user->id)
            ->where('usage_date', now()->toDateString())
            ->first();

        $tokensUsed = (int) ($row->total_tokens ?? 0);
        $requestsCount = (int) ($row->requests_count ?? 0);
        $limitTokens = $user->isAdmin() ? 250000 : self::DAILY_TOKEN_LIMIT;
        $limitRequests = $user->isAdmin() ? 500 : self::DAILY_REQUEST_LIMIT;

        return [
            'tokens_used' => $tokensUsed,
            'tokens_limit' => $limitTokens,
            'tokens_remaining' => max(0, $limitTokens - $tokensUsed),
            'requests_count' => $requestsCount,
            'requests_limit' => $limitRequests,
            'has_quota' => ($tokensUsed < $limitTokens) && ($requestsCount < $limitRequests),
        ];
    }

    public function hasRemainingQuota(User $user): bool
    {
        return $this->getDailyQuotaStatus($user)['has_quota'];
    }

    public function getCoachChatQuotaStatus(User $user): array
    {
        $coach = $this->getCoachUser();
        $hasActiveProgram = ProgramEnrollment::where('runner_id', $user->id)
            ->where('status', 'active')
            ->exists();

        $limit = $user->isAdmin()
            ? 200
            : ($hasActiveProgram ? self::ACTIVE_PROGRAM_CHAT_DAILY_LIMIT : self::FREE_CHAT_DAILY_LIMIT);

        $usedToday = Message::where('sender_id', $user->id)
            ->where('receiver_id', $coach->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $tokenQuota = $this->getDailyQuotaStatus($user);
        $remaining = max(0, $limit - $usedToday);

        return [
            'has_active_program' => $hasActiveProgram,
            'used' => $usedToday,
            'limit' => $limit,
            'remaining' => $remaining,
            'can_send' => ($remaining > 0) && $tokenQuota['has_quota'],
            'token_exhausted' => ! $tokenQuota['has_quota'],
        ];
    }

    public function recordTokenUsage(?User $user, int $tokens): void
    {
        if (! $user) {
            return;
        }

        $today = now()->toDateString();
        $tokens = max(0, $tokens);

        $updated = DB::table('user_ai_quotas')
            ->where('user_id', $user->id)
            ->where('usage_date', $today)
            ->update([
                'requests_count' => DB::raw('requests_count + 1'),
                'total_tokens' => DB::raw('total_tokens + ' . $tokens),
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            DB::table('user_ai_quotas')->insert([
                'user_id' => $user->id,
                'usage_date' => $today,
                'requests_count' => 1,
                'total_tokens' => $tokens,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function hasAvailableTrial(User $user): bool
    {
        return ((int) ($user->ai_program_trials_used ?? 0)) === 0;
    }

    public function consumeTrialIfAvailable(User $user): bool
    {
        $affected = DB::table('users')
            ->where('id', $user->id)
            ->where('ai_program_trials_used', 0)
            ->update([
                'ai_program_trials_used' => 1,
                'updated_at' => now(),
            ]);

        if ($affected > 0) {
            $user->ai_program_trials_used = 1;
            return true;
        }

        return false;
    }

    public function postProgramWelcomeMessage(User $runner, Program $program, ProgramEnrollment $enrollment, array $resultData = []): void
    {
        $coach = $this->getCoachUser();
        $paces = $resultData['paces'] ?? ($program->daniels_params['training_paces'] ?? []);
        $target = strtoupper((string) ($program->distance_target ?? '10K'));
        $vdot = $program->vdot_score ?? ($resultData['vdot'] ?? '-');
        $startStr = $enrollment->start_date ? Carbon::parse($enrollment->start_date)->format('d M Y') : '-';
        $endStr = $enrollment->end_date ? Carbon::parse($enrollment->end_date)->format('d M Y') : '-';

        $paceSummary = '';
        if (is_array($paces) && ! empty($paces)) {
            $parts = [];
            foreach (['E' => 'Easy', 'T' => 'Tempo', 'I' => 'Interval'] as $k => $lbl) {
                if (isset($paces[$k]) && is_numeric($paces[$k])) {
                    $sec = (int) round(((float) $paces[$k]) * 60);
                    $parts[] = "{$lbl}: " . sprintf('%d:%02d/km', intdiv($sec, 60), $sec % 60);
                }
            }
            if ($parts) {
                $paceSummary = "\nZona Pace Utama: " . implode(' | ', $parts);
            }
        }

        $notes = trim((string) ($resultData['coach_notes'] ?? ($program->daniels_params['ai_coach_notes'] ?? '')));
        $notesLine = $notes !== '' ? "\nFokus Pelatih: {$notes}" : '';

        $body = "Halo {$runner->name}, saya Coach Ruang Lari. Program {$target} ({$program->duration_weeks} minggu, VDOT {$vdot}) sudah aktif di kalender Anda mulai {$startStr} hingga {$endStr}.{$paceSummary}{$notesLine}\n\nSetiap selesai latihan, tandai sesi di Kalender Dashboard. Jika ada jadwal bentrok, kelelahan berlebih, atau nyeri ringan, langsung chat saya di sini supaya kita sesuaikan porsinya bersama.";

        Message::create([
            'sender_id' => $coach->id,
            'receiver_id' => $runner->id,
            'message' => $body,
        ]);
    }

    public function buildActiveProgramPromptContext(User $runner): string
    {
        $enrollment = ProgramEnrollment::where('runner_id', $runner->id)
            ->where('status', 'active')
            ->with('program')
            ->latest('id')
            ->first();

        if (! $enrollment || ! $enrollment->program) {
            return "- Program Aktif: Belum ada program aktif di kalender.\n";
        }

        $program = $enrollment->program;
        $start = $enrollment->start_date ? Carbon::parse($enrollment->start_date) : now();
        $daysElapsed = max(0, $start->diffInDays(now(), false));
        $currentWeek = max(1, min((int) ($program->duration_weeks ?? 12), intdiv($daysElapsed, 7) + 1));

        $completedCount = ProgramSessionTracking::where('enrollment_id', $enrollment->id)
            ->where('status', 'completed')
            ->count();

        $sessions = $program->program_json['sessions'] ?? [];
        $todaySession = null;
        if (is_array($sessions)) {
            $dayIndex = $daysElapsed + 1;
            foreach ($sessions as $s) {
                if ((int) ($s['day'] ?? 0) === $dayIndex) {
                    $todaySession = $s;
                    break;
                }
            }
        }

        $out = "- Program Aktif: {$program->title} (Minggu {$currentWeek} dari {$program->duration_weeks}, VDOT {$program->vdot_score}, Sesi selesai: {$completedCount})\n";
        if ($todaySession) {
            $out .= "- Jadwal Hari Ini: " . ($todaySession['type'] ?? '-') . ' ' . ($todaySession['distance'] ?? 0) . " km (" . ($todaySession['target_pace'] ?? '-') . ")\n";
        }

        return $out;
    }

    public function recordSessionFeedback(
        User $runner,
        ProgramEnrollment $enrollment,
        int $sessionDay,
        string $status,
        ?int $rpe = null,
        ?string $feeling = null
    ): ?string {
        if ($status !== 'completed') {
            return null;
        }

        $program = $enrollment->program;
        $sessions = $program?->program_json['sessions'] ?? [];
        $session = null;
        foreach ($sessions as $s) {
            if ((int) ($s['day'] ?? 0) === $sessionDay) {
                $session = $s;
                break;
            }
        }

        $type = strtolower((string) ($session['type'] ?? 'easy_run'));
        $isEasyType = in_array($type, ['easy_run', 'recovery_run', 'long_run'], true);

        if (in_array($feeling, ['pain', 'terrible'], true)) {
            $coach = $this->getCoachUser();
            $msg = "Catatan Coach Ruang Lari (Hari ke-{$sessionDay}): Anda melaporkan kondisi berat/nyeri setelah sesi {$type}. Jangan paksakan sesi Quality berikutnya. Kurangi beban ke Easy/Rest 1-2 hari dan kabari saya di chat agar jadwal bisa disesuaikan.";
            Message::create([
                'sender_id' => $coach->id,
                'receiver_id' => $runner->id,
                'message' => $msg,
            ]);
            return $msg;
        }

        if ($feeling === 'weak' || ($rpe !== null && $rpe >= 8 && $isEasyType)) {
            return 'Catatan Coach Ruang Lari: Usaha terasa cukup berat untuk sesi aerobik. Pastikan pace Easy tetap santai dan jaga hidrasi serta tidur malam ini.';
        }

        return 'Bagus, sesi tercatat selesai. Jaga pemulihan dan nutrisi agar siap menghadapi sesi berikutnya.';
    }
}
