<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ================= RELATIONS =================

    // Jika user adalah siswa
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    // Jika user adalah sekretaris (yang scan)
    public function scannedAttendances()
    {
        return $this->hasMany(Attendance::class, 'scanned_by');
    }

    // ================= HELPERS =================

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isSekretaris()
    {
        return $this->role === 'sekretaris';
    }

    public function isSiswa()
    {
        return $this->role === 'siswa';
    }
}