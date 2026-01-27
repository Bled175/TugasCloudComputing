<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Exception;

class AttendanceService
{
    public function scanQr(string $qrToken): Attendance
    {
        // 1. Cari siswa berdasarkan QR
        $student = Student::where('qr_token', $qrToken)->first();

        if (! $student) {
            throw new Exception('QR tidak valid');
        }

        $today = Carbon::today();

        // 2. Cek apakah sudah absen hari ini
        $already = Attendance::where('student_id', $student->id)
            ->whereDate('tanggal', $today)
            ->exists();

        if ($already) {
            throw new Exception('Siswa sudah absen hari ini');
        }

        // 3. Simpan absensi
        return Attendance::create([
            'student_id' => $student->id,
            'tanggal' => $today,
            'status' => 'hadir',
            'scanned_by' => Auth::id(), // sekretaris
            'scanned_at' => now(),
        ]);
    }
}
