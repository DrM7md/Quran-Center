<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('memorizations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('halaqa_id')->constrained('halaqas')->cascadeOnDelete();

            // المحفّظ الذي سجّل التسميع
            $table->foreignId('muhafidh_id')->constrained('users')->cascadeOnDelete();

            // نوع التسميع
            $table->enum('type', ['new', 'review']);

            // السورة
            $table->foreignId('surah_id')->constrained('surahs')->cascadeOnDelete();

            // من آية إلى آية
            $table->unsignedSmallInteger('from_ayah');
            $table->unsignedSmallInteger('to_ayah');

            // التقييم
            $table->enum('rating', ['excellent', 'very_good', 'good', 'weak', 'repeat']);

            $table->text('notes')->nullable();

            // تاريخ التسميع (مو لازم يكون created_at)
            $table->date('heard_at');

            $table->timestamps();

            $table->index(['halaqa_id', 'heard_at']);
            $table->index(['student_id', 'heard_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memorizations');
    }
};
