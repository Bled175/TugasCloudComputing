<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AttendanceChartWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\StatusDistributionWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use BackedEnum;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string $routePath = 'dashboard';

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            StatusDistributionWidget::class,
            AttendanceChartWidget::class,
            RecentActivityWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 4,
            'lg' => 4,
            'xl' => 4,
            '2xl' => 4,
        ];
    }
}
