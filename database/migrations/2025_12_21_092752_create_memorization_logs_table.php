<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('memorization_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                  ->constrained('students')
                  ->cascadeOnDelete();

            $table->foreignId('halaqa_id')
                  ->nullable()
                  ->constrained('halaqas')
                  ->nullOnDelete();

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->enum('type', ['new', 'review']); // حفظ جديد / مراجعة

            $table->foreignId('surah_id')
                  ->nullable()
                  ->constrained('surahs')
                  ->nullOnDelete();

            $table->unsignedSmallInteger('from_ayah')->nullable();
            $table->unsignedSmallInteger('to_ayah')->nullable();

            $table->enum('rating', ['excellent','very_good','good','weak','redo']); 
            // ممتاز، جيد جدًا، جيد، ضعيف، يحتاج إعادة

            $table->text('notes')->nullable();
            $table->date('date'); // تاريخ التسميع

            $table->timestamps();

            $table->index(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memorization_logs');
    }
};
