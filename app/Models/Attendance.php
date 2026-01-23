<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'tanggal',
        'status',
        'scanned_by',
        'scanned_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'scanned_at' => 'datetime',
    ];

    // ================= RELATIONS =================

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function scanner()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    // ================= QUERY SCOPES =================

    /**
     * 1️⃣ REKAP HARIAN - Semua siswa yang hadir hari ini
     * Cocok untuk: Dashboard Sekretaris
     */
    public function scopeDailyRecap($query)
    {
        return $query->with('student')
            ->whereDate('tanggal', \Carbon\Carbon::today())
            ->get();
    }

    /**
     * 2️⃣ REKAP PER SISWA - Riwayat absensi siswa
     * Cocok untuk: Halaman profil siswa (aman, tidak bisa lihat siswa lain)
     */
    public function scopeStudentHistory($query, $studentId)
    {
        return $query->where('student_id', $studentId)
            ->orderByDesc('tanggal')
            ->get();
    }

    /**
     * 3️⃣ REKAP RANGE TANGGAL - Laporan bulanan/mingguan
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->with('student')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();
    }

    /**
     * 4️⃣ REKAP PER SISWA + RANGE - Paling sering dipakai (Export Excel)
     */
    public function scopeStudentDateRange($query, $studentId, $startDate, $endDate)
    {
        return $query->where('student_id', $studentId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();
    }

    /**
     * 5️⃣ HITUNG TOTAL KEHADIRAN
     */
    public function scopeCountStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId)->count();
    }

    public function scopeCountDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate])->count();
    }
}