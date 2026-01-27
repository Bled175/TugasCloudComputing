<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AttendanceService;
use Exception;

class QrScanController extends Controller
{
    public function scan(Request $request, AttendanceService $service)
    {
        $request->validate([
            'qr_token' => 'required|string',
        ]);

        if (! auth()->user() || ! auth()->user()->isSekretaris()) {
            abort(403);
        }

        try {
            $attendance = $service->scanQr($request->qr_token);

            return response()->json([
                'status' => 'success',
                'message' => 'Absensi berhasil',
                'nama' => $attendance->student->nama,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
