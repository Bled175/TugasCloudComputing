<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Absensi')
                    ->description('Informasi kehadiran siswa')
                    ->columns(2)
                    ->schema([
                        Select::make('student_id')
                            ->label('Siswa')
                            ->relationship('student', 'nama')
                            ->searchable()
                            ->required()
                            ->columnSpanFull()
                            ->preload(),

                        DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->required()
                            ->default(now())
                            ->native(false),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'hadir' => 'Hadir',
                                'izin' => 'Izin',
                                'sakit' => 'Sakit',
                                'alpha' => 'Alpha',
                            ])
                            ->required()
                            ->default('hadir'),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Masukkan keterangan tambahan (opsional)'),
                    ]),
            ]);
    }
}
