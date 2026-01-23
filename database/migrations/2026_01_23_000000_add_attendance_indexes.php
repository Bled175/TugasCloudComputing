<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Performance index untuk queries umum
            $table->index(['student_id', 'tanggal']);
            // Index untuk queries berdasarkan tanggal saja
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['student_id', 'tanggal']);
            $table->dropIndex(['tanggal']);
        });
    }
};
