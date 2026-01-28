<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Get student dashboard stats
     * GET /api/dashboard
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        // Get attendance statistics
        $attendances = $student->attendances;

        $stats = [
            'total_hadir' => $attendances->where('status', 'hadir')->count(),
            'total_izin' => $attendances->where('status', 'izin')->count(),
            'total_sakit' => $attendances->where('status', 'sakit')->count(),
            'total_alpha' => $attendances->where('status', 'alpha')->count(),
            'total_attendance' => $attendances->count(),
            'attendance_percentage' => $attendances->count() > 0 
                ? round(($attendances->where('status', 'hadir')->count() / $attendances->count()) * 100, 2)
                : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ], 200);
    }

    /**
     * Get student attendance history
     * GET /api/attendances?month=3&year=2026
     */
    public function getAttendances(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $month = $request->query('month');
        $year = $request->query('year');

        $query = $student->attendances()->orderBy('tanggal', 'desc');

        if ($month && $year) {
            $query->whereYear('tanggal', $year)
                  ->whereMonth('tanggal', $month);
        }

        $attendances = $query->get()->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'tanggal' => $attendance->tanggal->format('Y-m-d'),
                'status' => $attendance->status,
                'keterangan' => $attendance->keterangan,
                'scanned_at' => $attendance->scanned_at?->format('Y-m-d H:i:s'),
                'scanner_name' => $attendance->scanner?->name ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $attendances,
        ], 200);
    }

    /**
     * Get student QR code
     * GET /api/qr-code
     */
    public function getQrCode(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(300)
            ->generate($student->qr_token);

        return response()->json([
            'success' => true,
            'data' => [
                'qr_code' => $qrCode,
                'qr_token' => $student->qr_token,
                'nama' => $student->nama,
                'kelas' => $student->kelas,
            ],
        ], 200);
    }

    /**
     * Get student profile
     * GET /api/profile
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nisn' => $student->nisn,
                'nama' => $student->nama,
                'kelas' => $student->kelas,
                'email' => $user->email,
            ],
        ], 200);
    }

    /**
     * Update student profile (limited fields)
     * PUT /api/profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        // Only allow updating certain fields
        $validated = $request->validate([
            'nama' => 'sometimes|string|max:255',
            'kelas' => 'sometimes|string|max:50',
        ]);

        $student->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'nisn' => $student->nisn,
                'nama' => $student->nama,
                'kelas' => $student->kelas,
            ],
        ], 200);
    }
}
