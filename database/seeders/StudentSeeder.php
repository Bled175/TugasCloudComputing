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
        $siswaUser = User::where('role', 'siswa')->first();

        Student::create([
            'nama' => 'Ahmad Fauzi',
            'kelas' => 'XI RPL',
            'qr_token' => Str::uuid(),
            'user_id' => $siswaUser->id,
        ]);
    }
}
