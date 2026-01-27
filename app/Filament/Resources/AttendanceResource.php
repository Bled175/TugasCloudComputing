<?php

namespace App\Filament\Resources;

use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Absensi';
    protected static ?string $pluralLabel = 'Data Absensi';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Absensi')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->relationship('student', 'nama')
                            ->required()
                            ->searchable(),

                        Forms\Components\DatePicker::make('tanggal')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'Hadir' => 'Hadir',
                                'Izin' => 'Izin',
                                'Sakit' => 'Sakit',
                                'Alpha' => 'Alpha',
                            ])
                            ->required(),

                        Forms\Components\Select::make('scanned_by')
                            ->relationship('scanner', 'name')
                            ->label('Discan oleh'),

                        Forms\Components\DateTimePicker::make('scanned_at')
                            ->label('Waktu Scan'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d-m-Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('student.nama')
                    ->label('Nama Siswa')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('student.kelas')
                    ->label('Kelas')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'Hadir',
                        'warning' => 'Izin',
                        'danger' => 'Sakit',
                        'secondary' => 'Alpha',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('scanner.name')
                    ->label('Discan oleh')
                    ->sortable(),

                Tables\Columns\TextColumn::make('scanned_at')
                    ->label('Waktu Scan')
                    ->dateTime('d-m-Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                // Filter Hari Ini
                Filter::make('hari_ini')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query) => $query->whereDate('tanggal', Carbon::today())),

                // Filter Status
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Izin' => 'Izin',
                        'Sakit' => 'Sakit',
                        'Alpha' => 'Alpha',
                    ]),

                // Filter Siswa
                SelectFilter::make('student')
                    ->label('Siswa')
                    ->relationship('student', 'nama')
                    ->searchable(),

                // Filter Range Tanggal
                Filter::make('tanggal')
                    ->label('Range Tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_dari')
                            ->label('Dari'),
                        Forms\Components\DatePicker::make('tanggal_sampai')
                            ->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal_dari'],
                                fn (Builder $q, $date) => $q->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['tanggal_sampai'],
                                fn (Builder $q, $date) => $q->whereDate('tanggal', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('tanggal', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\AttendanceResource\Pages\ListAttendances::class,
            'create' => \App\Filament\Resources\AttendanceResource\Pages\CreateAttendance::class,
            'edit' => \App\Filament\Resources\AttendanceResource\Pages\EditAttendance::class,
        ];
    }
}
