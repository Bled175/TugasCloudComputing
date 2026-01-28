<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;      
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Icons\Heroicon;

class QrScanner extends Page
{
protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedQrCode;



    protected string $view = 'filament.pages.qr-scanner';

    protected static ?string $navigationLabel = 'QR Scanner';

    protected static ?string $title = 'QR Code Scanner';

    protected static ?int $navigationSort = 2;

    public ?string $qr_token = null;

    public ?Student $scannedStudent = null;

    public array $recentScans = [];

    protected function getListeners(): array
{
    return [
        'qr-scanned' => 'handleQrScan',
    ];
}


    public function mount(): void
    {
        $this->loadRecentScans();
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'qr_token' && !empty($this->qr_token)) {
            $this->handleQrScan($this->qr_token);
        }
    }

    public function handleQrScan(string $token): void
    {
        if (empty($token)) {
            return;
        }

        // Find student by QR token
        $student = Student::byQrToken($token)->first();

        if (!$student) {
            Notification::make()
                ->danger()
                ->title('QR Code Tidak Valid')
                ->body('Student dengan QR token ini tidak ditemukan.')
                ->send();
            $this->qr_token = null;
            return;
        }

        // Check if student already has attendance today
        $existingAttendance = Attendance::where('student_id', $student->id)
            ->whereDate('tanggal', Carbon::today())
            ->first();

        if ($existingAttendance) {
            Notification::make()
                ->warning()
                ->title('Sudah Absen')
                ->body($student->nama . ' sudah absen pada tanggal ' . Carbon::today()->format('d M Y') . '.')
                ->send();
            $this->qr_token = null;
            return;
        }

        try {
            // Create attendance record
            Attendance::create([
                'student_id' => $student->id,
                'tanggal' => Carbon::today(),
                'status' => 'hadir',
                'scanned_by' => Auth::id(),
                'scanned_at' => Carbon::now(),
            ]);

            $this->scannedStudent = $student;

            Notification::make()
                ->success()
                ->title('Absensi Berhasil')
                ->body($student->nama . ' (' . $student->kelas . ') berhasil diabsensi.')
                ->send();

            $this->loadRecentScans();
            $this->qr_token = null;

            // Clear scanned student after 3 seconds
            $this->dispatch('clear-scanned-after-delay');
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Gagal Mencatat Absensi')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->send();
            $this->qr_token = null;
        }
    }

    private function loadRecentScans(): void
    {
        $this->recentScans = Attendance::with('student')
            ->whereDate('tanggal', Carbon::today())
            ->orderByDesc('scanned_at')
            ->limit(10)
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'nama' => $attendance->student->nama,
                    'kelas' => $attendance->student->kelas,
                    'waktu' => $attendance->scanned_at?->format('H:i:s') ?? '-',
                    'status' => $attendance->status,
                ];
            })
            ->toArray();
    }
}

