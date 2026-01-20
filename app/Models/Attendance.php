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
}