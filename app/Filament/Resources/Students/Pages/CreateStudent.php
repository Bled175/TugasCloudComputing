<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract user data
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $nama = $data['nama'] ?? null;

        if ($email && $password) {
            // Create User with role siswa
            $user = User::create([
                'name' => $nama,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'siswa',
            ]);

            // Set user_id to data
            $data['user_id'] = $user->id;
        }

        // Remove user-related fields from student data
        unset($data['email'], $data['password']);

        return $data;
    }
}
