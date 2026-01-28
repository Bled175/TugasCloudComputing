<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'nisn',
        'nama',
        'kelas',
        'qr_token',
        'user_id',
    ];

    // ================= RELATIONS =================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // ================= SCOPES =================

    public function scopeByQrToken($query, $token)
    {
        return $query->where('qr_token', $token);
    }

    // ================= ACCESSORS =================

    public function getQrCodeAttribute()
    {
        return \SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)
            ->generate($this->qr_token);
    }
}