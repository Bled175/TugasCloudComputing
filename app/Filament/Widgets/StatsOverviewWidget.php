<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalStudents = Student::count();
        $todayAttendance = Attendance::whereDate('tanggal', Carbon::today())->count();
        $thisWeekPresent = Attendance::where('status', 'hadir')
            ->whereBetween('tanggal', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count();
        $thisMonthPresent = Attendance::where('status', 'hadir')
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();

        return [
            Stat::make('Total Siswa', $totalStudents)
                ->description('Peserta ekstrakurikuler')
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->icon('heroicon-o-user-group'),

            Stat::make('Absensi Hari Ini', $todayAttendance)
                ->description('Record absensi')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Hadir Minggu Ini', $thisWeekPresent)
                ->description('Minggu ' . Carbon::now()->format('W'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning')
                ->icon('heroicon-o-calendar'),

            Stat::make('Hadir Bulan Ini', $thisMonthPresent)
                ->description(Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary')
                ->icon('heroicon-o-chart-bar'),
        ];
    }
}
