<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class RecentActivityWidget extends BaseWidget
{
    protected static ?string $heading = 'Absensi Terbaru';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attendance::query()
                    ->with(['student', 'scanner'])
                    ->latest('created_at')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('student.nama')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.kelas')
                    ->label('Kelas')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'hadir',
                        'warning' => 'izin',
                        'info' => 'sakit',
                        'danger' => 'alpha',
                    ]),

                TextColumn::make('scanner.name')
                    ->label('Dicatat Oleh')
                    ->default('-'),

                TextColumn::make('scanned_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ]);
    }
}
