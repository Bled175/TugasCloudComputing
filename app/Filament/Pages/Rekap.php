<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;

class Rekap extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;


protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';


    protected string $view = 'filament.pages.rekap';

    protected static ?string $navigationLabel = 'Rekap/Laporan';

    protected static ?string $title = 'Rekap Absensi';

    protected static ?int $navigationSort = 3;

    public ?Carbon $date_from = null;

    public ?Carbon $date_to = null;

    public ?string $kelas = null;

    public ?string $status = null;

    public function mount(): void
    {
        $this->date_from = Carbon::now()->startOfMonth();
        $this->date_to = Carbon::now();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getQuery())
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

                TextColumn::make('student.nisn')
                    ->label('NISN')
                    ->searchable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'hadir',
                        'warning' => 'izin',
                        'info' => 'sakit',
                        'danger' => 'alpha',
                    ]),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30),

                TextColumn::make('scanner.name')
                    ->label('Dicatat Oleh'),
            ])
            ->filters([
                // Filters handled by properties
            ])
            ->paginated([10, 25, 50, 100]);
    }

    private function getQuery(): Builder
    {
        $query = Attendance::with(['student', 'scanner']);

        if ($this->date_from) {
            $query->whereDate('tanggal', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('tanggal', '<=', $this->date_to);
        }

        if ($this->kelas) {
            $query->whereHas('student', fn($q) => $q->where('kelas', $this->kelas));
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->orderByDesc('tanggal');
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('date_from')
                ->label('Dari Tanggal')
                ->reactive(),

            DatePicker::make('date_to')
                ->label('Sampai Tanggal')
                ->reactive(),

            Select::make('kelas')
                ->label('Kelas')
                ->options(function () {
                    return \App\Models\Student::distinct('kelas')
                        ->pluck('kelas', 'kelas');
                })
                ->searchable()
                ->nullable()
                ->reactive(),

            Select::make('status')
                ->label('Status')
                ->options([
                    'hadir' => 'Hadir',
                    'izin' => 'Izin',
                    'sakit' => 'Sakit',
                    'alpha' => 'Alpha',
                ])
                ->nullable()
                ->reactive(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action('exportExcel')
                ->color('success'),

            Action::make('reset_filters')
                ->label('Reset Filter')
                ->icon('heroicon-o-arrow-path')
                ->action('resetFilters')
                ->color('gray'),
        ];
    }

    public function exportExcel()
    {
        $fileName = 'rekap-absensi-' . Carbon::now()->format('Y-m-d-His') . '.csv';

        $records = $this->getQuery()->get();

        $csv = fopen('php://memory', 'r+');

        // Header
        fputcsv($csv, ['Tanggal', 'Nama Siswa', 'NISN', 'Kelas', 'Status', 'Keterangan', 'Dicatat Oleh']);

        // Data
        foreach ($records as $record) {
            fputcsv($csv, [
                $record->tanggal->format('d-m-Y'),
                $record->student->nama,
                $record->student->nisn,
                $record->student->kelas,
                ucfirst($record->status),
                $record->keterangan ?? '-',
                $record->scanner?->name ?? '-',
            ]);
        }

        rewind($csv);
        $csv_content = stream_get_contents($csv);
        fclose($csv);

        return response()->streamDownload(
            fn() => print($csv_content),
            $fileName,
            ['Content-Type' => 'text/csv; charset=utf-8']
        );
    }

    public function resetFilters(): void
    {
        $this->date_from = Carbon::now()->startOfMonth();
        $this->date_to = Carbon::now();
        $this->kelas = null;
        $this->status = null;
    }
}
