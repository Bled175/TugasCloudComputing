<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('qr_scanner')
                ->label('QR Scanner')
                ->icon('heroicon-o-qr-code')
                ->url(route('filament.admin.pages.qr-scanner'))
                ->color('success')
                ->openUrlInNewTab(false),
            CreateAction::make(),
        ];
    }
}
