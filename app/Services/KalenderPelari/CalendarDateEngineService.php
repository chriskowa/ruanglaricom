<?php

namespace App\Services\KalenderPelari;

use Carbon\Carbon;

class CalendarDateEngineService
{
    private const CANVAS_PRESETS_MM = [
        'A4_LANDSCAPE' => ['w' => 297, 'h' => 210],
        'A3_LANDSCAPE' => ['w' => 420, 'h' => 297],
        'DESK_CALENDAR' => ['w' => 210, 'h' => 148],
        'SQUARE' => ['w' => 250, 'h' => 250],
        'CUSTOM' => ['w' => 210, 'h' => 297],
    ];

    private const PIXELS_PER_MM = 3.7795275591;

    public function __construct()
    {
        Carbon::setLocale(config('app.locale', 'id'));
    }

    public function canvasSizeFor(string $formatType): array
    {
        $preset = self::CANVAS_PRESETS_MM[$formatType] ?? self::CANVAS_PRESETS_MM['A4_LANDSCAPE'];

        return [
            'format_type' => $formatType,
            'width_mm' => $preset['w'],
            'height_mm' => $preset['h'],
            'width_px' => (int) round($preset['w'] * self::PIXELS_PER_MM),
            'height_px' => (int) round($preset['h'] * self::PIXELS_PER_MM),
            'density' => 96,
        ];
    }

    public function yearMonthNames(int $year, string $locale = 'id_ID'): array
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $pages = [];
        $pages[] = [
            'month_number' => 0,
            'page_type' => 'COVER',
            'label' => 'Cover',
        ];
        for ($m = 1; $m <= 12; $m++) {
            $pages[] = [
                'month_number' => $m,
                'page_type' => 'MONTH',
                'year' => $year,
                'month_label' => $months[$m],
                'days_in_month' => Carbon::create($year, $m, 1)->daysInMonth,
                'first_day_of_week' => Carbon::create($year, $m, 1)->dayOfWeekIso,
            ];
        }
        $pages[] = [
            'month_number' => 13,
            'page_type' => 'YEAR_REVIEW',
            'label' => 'Ringkasan Tahunan '.$year,
        ];

        return $pages;
    }

    public function monthGrid(int $year, int $month, string $startWeekOn = 'MONDAY'): array
    {
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $daysInMonth = $start->daysInMonth;
        $grid = [];

        $offset = $startWeekOn === 'SUNDAY'
            ? (int) $start->dayOfWeek
            : (int) $start->dayOfWeekIso - 1;

        for ($i = 0; $i < $offset; $i++) {
            $grid[] = null;
        }

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $grid[] = [
                'day' => $d,
                'iso_date' => Carbon::create($year, $month, $d)->toDateString(),
                'is_weekend' => in_array((int) Carbon::create($year, $month, $d)->dayOfWeekIso, [6, 7], true),
            ];
        }

        return [
            'month' => $month,
            'year' => $year,
            'weekdays_short' => $startWeekOn === 'SUNDAY'
                ? ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']
                : ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            'weeks' => array_chunk($grid, 7),
        ];
    }

    public function mmToPx(float $mm): float
    {
        return round($mm * self::PIXELS_PER_MM, 2);
    }

    public function pxToMm(float $px): float
    {
        return round($px / self::PIXELS_PER_MM, 2);
    }
}
