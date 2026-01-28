<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class StatusDistributionWidget extends ChartWidget
{
    protected  ?string $heading = 'Distribusi Status Absensi (Bulan Ini)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $hadir = Attendance::where('status', 'hadir')
            ->whereMonth('tanggal', Carbon::now()->month)
            ->count();

        $izin = Attendance::where('status', 'izin')
            ->whereMonth('tanggal', Carbon::now()->month)
            ->count();

        $sakit = Attendance::where('status', 'sakit')
            ->whereMonth('tanggal', Carbon::now()->month)
            ->count();

        $alpha = Attendance::where('status', 'alpha')
            ->whereMonth('tanggal', Carbon::now()->month)
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Status Absensi',
                    'data' => [$hadir, $izin, $sakit, $alpha],
                    'backgroundColor' => [
                        '#22c55e', // Hadir (hijau)
                        '#eab308', // Izin (kuning)
                        '#3b82f6', // Sakit (biru)
                        '#ef4444', // Alpha (merah)
                    ],
                    'borderColor' => [
                        '#16a34a',
                        '#ca8a04',
                        '#1d4ed8',
                        '#dc2626',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Hadir', 'Izin', 'Sakit', 'Alpha'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
