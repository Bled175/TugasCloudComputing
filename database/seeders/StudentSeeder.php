<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $siswaUsers = User::where('role', 'siswa')->get();

        $students = [
            ['nama' => 'Ahmad Fauzi', 'kelas' => 'X-1', 'nisn' => '0001234567'],
            ['nama' => 'Siti Nurhaliza', 'kelas' => 'X-2', 'nisn' => '0001234568'],
            ['nama' => 'Budi Santoso', 'kelas' => 'XI-1', 'nisn' => '0001234569'],
            ['nama' => 'Dewi Lestari', 'kelas' => 'XI-2', 'nisn' => '0001234570'],
            ['nama' => 'Eko Prasetyo', 'kelas' => 'XII-1', 'nisn' => '0001234571'],
        ];

        foreach ($siswaUsers as $index => $user) {
            if (isset($students[$index])) {
                Student::create([
                    'user_id' => $user->id,
                    'nisn' => $students[$index]['nisn'],
                    'nama' => $students[$index]['nama'],
                    'kelas' => $students[$index]['kelas'],
                    'qr_token' => Str::uuid(),
                ]);
            }
        }
    }
}
