<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                  ->constrained('students')
                  ->cascadeOnDelete();

            $table->date('date');                   // تاريخ الغياب
            $table->foreignId('created_by')         // من سجل الغياب
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'date']); // مهم عشان ما يتكرر الغياب بنفس اليوم
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
