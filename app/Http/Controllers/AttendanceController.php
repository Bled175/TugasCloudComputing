<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    /**
     * 1️⃣ REKAP HARIAN - Dashboard Sekretaris
     * Menampilkan semua siswa yang hadir hari ini
     */
    public function dailyRecap()
    {
        $rekapHarian = Attendance::with('student')
            ->whereDate('tanggal', Carbon::today())
            ->orderBy('tanggal', 'desc')
            ->get();

        return Inertia::render('Attendance/DailyRecap', [
            'attendances' => $rekapHarian,
            'totalToday' => $rekapHarian->count(),
            'date' => Carbon::today()->format('d-m-Y'),
        ]);
    }

    /**
     * 2️⃣ REKAP PER SISWA - Halaman Profil Siswa
     * Menampilkan riwayat absensi siswa tertentu
     */
    public function studentHistory($studentId)
    {
        $student = Student::with(['attendances' => function ($q) {
            $q->orderByDesc('tanggal');
        }])->findOrFail($studentId);

        return Inertia::render('Attendance/StudentHistory', [
            'student' => $student,
            'attendances' => $student->attendances,
        ]);
    }

    /**
     * 3️⃣ REKAP RANGE TANGGAL - Laporan Bulanan/Mingguan
     */
    public function dateRangeReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $rekapRange = Attendance::with('student')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();

        return Inertia::render('Attendance/DateRangeReport', [
            'attendances' => $rekapRange,
            'startDate' => $startDate->format('d-m-Y'),
            'endDate' => $endDate->format('d-m-Y'),
            'total' => $rekapRange->count(),
        ]);
    }

    /**
     * 4️⃣ REKAP PER SISWA + RANGE - Export Excel
     */
    public function studentDateRangeReport(Request $request, $studentId)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $student = Student::findOrFail($studentId);
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $attendances = Attendance::where('student_id', $studentId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();

        $totalHadir = $attendances->where('status', 'Hadir')->count();
        $totalIzin = $attendances->where('status', 'Izin')->count();
        $totalAlpha = $attendances->where('status', 'Alpha')->count();

        return Inertia::render('Attendance/StudentDateRangeReport', [
            'student' => $student,
            'attendances' => $attendances,
            'startDate' => $startDate->format('d-m-Y'),
            'endDate' => $endDate->format('d-m-Y'),
            'summary' => [
                'total' => $attendances->count(),
                'hadir' => $totalHadir,
                'izin' => $totalIzin,
                'alpha' => $totalAlpha,
            ],
        ]);
    }

    /**
     * 5️⃣ API - Statistik Kehadiran Siswa
     */
    public function studentStats($studentId)
    {
        $student = Student::findOrFail($studentId);

        $totalAttendance = Attendance::where('student_id', $studentId)->count();
        $thisMonthAttendance = Attendance::where('student_id', $studentId)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();

        $attendanceData = Attendance::where('student_id', $studentId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        return [
            'student' => $student,
            'totalAttendance' => $totalAttendance,
            'thisMonthAttendance' => $thisMonthAttendance,
            'byStatus' => $attendanceData,
        ];
    }

    /**
     * 6️⃣ API - Statistik Kehadiran Range
     */
    public function rangeStats(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $total = Attendance::whereBetween('tanggal', [$startDate, $endDate])->count();
        $byStatus = Attendance::whereBetween('tanggal', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $byStudent = Attendance::with('student')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->selectRaw('student_id, COUNT(*) as count')
            ->groupBy('student_id')
            ->orderByDesc('count')
            ->get();

        return [
            'total' => $total,
            'byStatus' => $byStatus,
            'topStudents' => $byStudent->take(10),
        ];
    }
}
