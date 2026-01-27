<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
    $table->id();

    $table->foreignId('student_id')
        ->constrained('students')
        ->cascadeOnDelete();

    $table->date('tanggal');

    $table->enum('status', ['hadir'])->default('hadir');

    $table->foreignId('scanned_by')
        ->constrained('users')
        ->restrictOnDelete();

    $table->timestamp('scanned_at')->useCurrent();

    $table->timestamps();

    $table->unique(['student_id', 'tanggal']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
