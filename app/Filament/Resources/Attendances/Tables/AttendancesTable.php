<?php

namespace App\Filament\Resources\Attendances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                    ->searchable()
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
                    ->label('Scan By')
                    ->searchable(),

                TextColumn::make('scanned_at')
                    ->label('Waktu Scan')
                    ->dateTime('d M Y, H:i:s')
                    ->timezone('Asia/Jakarta')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->multiple()
                    ->options([
                        'hadir' => 'Hadir',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'alpha' => 'Alpha',
                    ]),

                SelectFilter::make('student.kelas')
                    ->label('Kelas')
                    ->multiple()
                    ->relationship('student', 'kelas'),

                Filter::make('tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('tanggal_dari')
                            ->label('Dari Tanggal'),
                        \Filament\Forms\Components\DatePicker::make('tanggal_sampai')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal_dari'] ?? null,
                                fn(Builder $q, $date) => $q->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['tanggal_sampai'] ?? null,
                                fn(Builder $q, $date) => $q->whereDate('tanggal', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
