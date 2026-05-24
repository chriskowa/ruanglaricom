<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\CustomWorkout;
use App\Models\Message;
use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $user->load('wallet');

        // Calculate total earnings from program sales
        $totalEarnings = 0;
        if ($user->wallet) {
            $totalEarnings = $user->wallet->transactions()
                ->whereIn('type', ['commission', 'deposit'])
                ->where('status', 'completed')
                ->sum('amount');
        }

        $today = Carbon::now()->startOfDay();
        $startRange = $today->copy()->subDays(14);
        $endRange = $today->copy()->addDay()->endOfDay();

        $enrollments = ProgramEnrollment::whereHas('program', function ($q) use ($user) {
            $q->where('coach_id', $user->id);
        })
            ->where('status', 'active')
            ->with([
                'runner:id,name,avatar,email,weekly_km_target',
                'program:id,coach_id,title,program_json,difficulty',
            ])
            ->get();

        $enrollmentIds = $enrollments->pluck('id')->values();
        $runnerIds = $enrollments->pluck('runner_id')->unique()->values();

        $unreadCounts = collect();
        if ($runnerIds->isNotEmpty()) {
            $unreadCounts = Message::query()
                ->selectRaw('sender_id, COUNT(*) AS unread_count')
                ->whereIn('sender_id', $runnerIds)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->groupBy('sender_id')
                ->pluck('unread_count', 'sender_id');
        }

        $trackings = collect();
        if ($enrollmentIds->isNotEmpty()) {
            $trackings = ProgramSessionTracking::query()
                ->whereIn('enrollment_id', $enrollmentIds)
                ->where(function ($q) use ($startRange, $endRange) {
                    $q->whereBetween('rescheduled_date', [$startRange, $endRange])
                        ->orWhereBetween('completed_at', [$startRange, $endRange])
                        ->orWhere('status', 'started');
                })
                ->get();
        }

        $trackingsByEnrollmentDay = [];
        foreach ($trackings as $t) {
            $trackingsByEnrollmentDay[(int) $t->enrollment_id][(int) $t->session_day] = $t;
        }

        $sessionsByEnrollmentDay = [];
        foreach ($enrollments as $en) {
            $sessions = data_get($en->program, 'program_json.sessions', []);
            if (! is_array($sessions)) {
                $sessions = [];
            }
            foreach ($sessions as $s) {
                $day = (int) data_get($s, 'day', 0);
                if ($day <= 0) {
                    continue;
                }
                $sessionsByEnrollmentDay[(int) $en->id][$day] = [
                    'type' => data_get($s, 'type'),
                    'distance' => data_get($s, 'distance'),
                    'duration' => data_get($s, 'duration'),
                    'description' => data_get($s, 'description'),
                ];
            }
        }

        $bestByRunner = [];
        $dueTodayRunners = [];
        $overdueRunners = [];
        $needsReviewRunners = [];
        $riskRunners = [];

        $startOfWeek = $today->copy()->startOfWeek()->startOfDay();
        $endOfWeek = $today->copy()->endOfWeek()->endOfDay();
        $weekScheduled = 0;
        $weekCompleted = 0;

        foreach ($enrollments as $enrollment) {
            if (! $enrollment->start_date || ! $enrollment->program) {
                continue;
            }

            $sessions = data_get($enrollment->program, 'program_json.sessions', []);
            if (! is_array($sessions) || empty($sessions)) {
                continue;
            }

            foreach ($sessions as $session) {
                $day = (int) data_get($session, 'day', 0);
                if ($day <= 0) {
                    continue;
                }

                try {
                    $sessionDate = $enrollment->start_date->copy()->addDays($day - 1);
                } catch (\Exception $e) {
                    continue;
                }

                $tracking = $trackingsByEnrollmentDay[(int) $enrollment->id][$day] ?? null;
                if ($tracking && $tracking->rescheduled_date) {
                    $sessionDate = Carbon::parse($tracking->rescheduled_date)->startOfDay();
                }

                if ($sessionDate->lt($startRange) || $sessionDate->gt($endRange)) {
                    continue;
                }

                $status = $tracking ? ($tracking->status ?: 'pending') : 'pending';
                $isCompleted = $status === 'completed';
                $needsReview = $isCompleted && empty($tracking?->coach_rating);

                $rpe = $tracking?->rpe;
                $feeling = $tracking?->feeling;
                $risk = ($feeling === 'terrible')
                    || ($feeling === 'weak' && is_int($rpe) && $rpe >= 8)
                    || (is_int($rpe) && $rpe >= 9);

                $isToday = $sessionDate->isSameDay($today);
                $isOverdue = $sessionDate->lt($today) && ! $isCompleted;
                $isDueToday = $isToday && ! $isCompleted;

                if ($sessionDate->betweenIncluded($startOfWeek, $endOfWeek)) {
                    $weekScheduled++;
                    if ($isCompleted) {
                        $weekCompleted++;
                    }
                }

                $runnerId = (int) $enrollment->runner_id;

                if ($isDueToday) {
                    $dueTodayRunners[$runnerId] = true;
                }
                if ($isOverdue) {
                    $overdueRunners[$runnerId] = true;
                }
                if ($needsReview) {
                    $needsReviewRunners[$runnerId] = true;
                }
                if ($risk) {
                    $riskRunners[$runnerId] = true;
                }

                $unread = (int) ($unreadCounts[$runnerId] ?? 0);

                $priority = 0;
                if ($risk) {
                    $priority = 100;
                } elseif ($isOverdue) {
                    $priority = 80;
                } elseif ($needsReview) {
                    $priority = 60;
                } elseif ($isDueToday) {
                    $priority = 50;
                } elseif ($unread > 0) {
                    $priority = 40;
                }

                if ($priority <= 0) {
                    continue;
                }

                $item = [
                    'runner_id' => $runnerId,
                    'runner_name' => data_get($enrollment->runner, 'name', 'Runner'),
                    'runner_avatar' => $enrollment->runner ? $enrollment->runner->avatar_url : null,
                    'enrollment_id' => (int) $enrollment->id,
                    'program_title' => data_get($enrollment->program, 'title', 'Program'),
                    'date' => $sessionDate->toDateString(),
                    'date_label' => $sessionDate->translatedFormat('D, d M'),
                    'session_day' => $day,
                    'type' => data_get($session, 'type', 'run'),
                    'distance' => data_get($session, 'distance'),
                    'duration' => data_get($session, 'duration'),
                    'status' => $status,
                    'completed_at' => $tracking?->completed_at,
                    'notes' => $tracking?->notes,
                    'strava_link' => $tracking?->strava_link,
                    'rpe' => $rpe,
                    'feeling' => $feeling,
                    'coach_rating' => $tracking?->coach_rating,
                    'unread_count' => $unread,
                    'flags' => [
                        'risk' => $risk,
                        'overdue' => $isOverdue,
                        'needs_review' => $needsReview,
                        'due_today' => $isDueToday,
                    ],
                    'priority' => $priority,
                ];

                $prev = $bestByRunner[$runnerId] ?? null;
                if (! $prev) {
                    $bestByRunner[$runnerId] = $item;
                    continue;
                }
                if ($item['priority'] > $prev['priority']) {
                    $bestByRunner[$runnerId] = $item;
                    continue;
                }
                if ($item['priority'] === $prev['priority']) {
                    if ($item['date'] < $prev['date']) {
                        $bestByRunner[$runnerId] = $item;
                    }
                }
            }
        }

        $queueItems = array_values($bestByRunner);
        usort($queueItems, function ($a, $b) {
            if ($a['priority'] !== $b['priority']) {
                return $b['priority'] <=> $a['priority'];
            }
            return strcmp($a['date'], $b['date']);
        });
        $queueItems = array_slice($queueItems, 0, 12);

        $recentActivities = [];
        if ($enrollmentIds->isNotEmpty()) {
            $recentTrackings = ProgramSessionTracking::query()
                ->whereIn('enrollment_id', $enrollmentIds)
                ->where('status', 'completed')
                ->whereNotNull('completed_at')
                ->where('completed_at', '>=', $today->copy()->subDays(7))
                ->with(['enrollment.runner:id,name,avatar', 'enrollment.program:id,title'])
                ->latest('completed_at')
                ->limit(20)
                ->get();

            foreach ($recentTrackings as $t) {
                $enrollment = $t->enrollment;
                if (! $enrollment) {
                    continue;
                }
                $meta = $sessionsByEnrollmentDay[(int) $enrollment->id][(int) $t->session_day] ?? [];
                $recentActivities[] = [
                    'kind' => 'program',
                    'completed_at' => $t->completed_at,
                    'runner_id' => (int) $enrollment->runner_id,
                    'runner_name' => data_get($enrollment->runner, 'name', 'Runner'),
                    'runner_avatar' => $enrollment->runner ? $enrollment->runner->avatar_url : null,
                    'enrollment_id' => (int) $enrollment->id,
                    'program_title' => data_get($enrollment->program, 'title', 'Program'),
                    'session_day' => (int) $t->session_day,
                    'type' => data_get($meta, 'type', 'program_session'),
                    'distance' => data_get($meta, 'distance'),
                    'duration' => data_get($meta, 'duration'),
                    'rpe' => $t->rpe,
                    'feeling' => $t->feeling,
                    'notes' => $t->notes,
                    'strava_link' => $t->strava_link,
                    'coach_rating' => $t->coach_rating,
                ];
            }
        }

        if ($runnerIds->isNotEmpty()) {
            $recentCustom = CustomWorkout::query()
                ->whereIn('runner_id', $runnerIds)
                ->where('status', 'completed')
                ->whereNotNull('completed_at')
                ->where('completed_at', '>=', $today->copy()->subDays(7))
                ->with('runner:id,name,avatar')
                ->latest('completed_at')
                ->limit(10)
                ->get();

            foreach ($recentCustom as $cw) {
                $recentActivities[] = [
                    'kind' => 'custom',
                    'completed_at' => $cw->completed_at,
                    'runner_id' => (int) $cw->runner_id,
                    'runner_name' => data_get($cw->runner, 'name', 'Runner'),
                    'runner_avatar' => $cw->runner ? $cw->runner->avatar_url : null,
                    'enrollment_id' => null,
                    'program_title' => 'Custom Workout',
                    'session_day' => null,
                    'type' => $cw->type ?: 'custom',
                    'distance' => $cw->distance,
                    'duration' => $cw->duration,
                    'rpe' => null,
                    'feeling' => null,
                    'notes' => $cw->notes,
                    'strava_link' => null,
                    'coach_rating' => null,
                ];
            }
        }

        usort($recentActivities, function ($a, $b) {
            $at = $a['completed_at'] ? Carbon::parse($a['completed_at'])->timestamp : 0;
            $bt = $b['completed_at'] ? Carbon::parse($b['completed_at'])->timestamp : 0;
            return $bt <=> $at;
        });
        $recentActivities = array_slice($recentActivities, 0, 20);

        $weeklyCompletionRate = $weekScheduled > 0 ? round(($weekCompleted / $weekScheduled) * 100) : 0;

        $myPrograms = \App\Models\Program::where('coach_id', $user->id)
            ->withCount(['enrollments' => function ($q) {
                $q->where('status', 'active');
            }])
            ->latest()
            ->get();

        $riskRunnerIds = array_keys($riskRunners);
        $needsReviewRunnerIds = array_keys($needsReviewRunners);
        $unreadCountsArray = $unreadCounts->toArray();

        $mappedAthletes = $enrollments->map(function($en) use ($riskRunnerIds, $needsReviewRunnerIds, $unreadCountsArray) {
            $totalDays = ($en->program->duration_weeks ?? 12) * 7;
            $daysPassed = $en->start_date ? now()->diffInDays($en->start_date) : 0;
            $progress = $totalDays > 0 ? min(100, max(0, ($daysPassed / $totalDays) * 100)) : 0;
            $currentWeek = ceil(($daysPassed + 1) / 7);
            
            return [
                'id' => $en->id,
                'runner_id' => $en->runner_id,
                'runner_name' => $en->runner->name,
                'runner_email' => $en->runner->email,
                'runner_avatar' => $en->runner->avatar_url,
                'program_id' => $en->program_id,
                'program_title' => $en->program->title,
                'program_difficulty' => $en->program->difficulty,
                'start_date_formatted' => $en->start_date ? $en->start_date->format('d M Y') : 'Not Started',
                'progress_pct' => round($progress),
                'current_week' => (int) $currentWeek,
                'weekly_km_target' => $en->runner->weekly_km_target,
                'is_risk' => in_array($en->runner_id, $riskRunnerIds),
                'needs_review' => in_array($en->runner_id, $needsReviewRunnerIds),
                'unread_count' => (int) ($unreadCountsArray[$en->runner_id] ?? 0),
            ];
        });

        $mappedPrograms = $myPrograms->map(function($p) {
            return [
                'id' => $p->id,
                'title' => $p->title,
                'difficulty' => $p->difficulty,
                'distance_target' => $p->distance_target,
                'price' => $p->price,
                'duration_weeks' => $p->duration_weeks,
                'is_published' => (bool) $p->is_published,
                'enrollments_count' => $p->enrollments_count,
                'publish_url' => route('coach.programs.publish', $p->id),
                'unpublish_url' => route('coach.programs.unpublish', $p->id),
            ];
        });

        return view('coach.dashboard', [
            'walletBalance' => $user->wallet ? $user->wallet->balance : 0,
            'totalEarnings' => $totalEarnings,
            'queueItems' => $queueItems,
            'myPrograms' => $myPrograms,
            'enrollments' => $enrollments,
            'mappedAthletes' => $mappedAthletes,
            'mappedPrograms' => $mappedPrograms,
            'coachMetrics' => [
                'due_today' => count($dueTodayRunners),
                'needs_review' => count($needsReviewRunners),
                'overdue' => count($overdueRunners),
                'risk' => count($riskRunners),
                'unread_chats' => $unreadCounts->filter(fn ($c) => (int) $c > 0)->count(),
                'weekly_completion_rate' => $weeklyCompletionRate,
                'weekly_scheduled' => $weekScheduled,
                'weekly_completed' => $weekCompleted,
                'active_athletes' => $runnerIds->count(),
            ],
            'recentActivities' => $recentActivities,
        ]);
    }
}
