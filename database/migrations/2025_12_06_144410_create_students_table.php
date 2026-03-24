<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // اسم الطالب
            $table->unsignedTinyInteger('age')->nullable(); // العمر (مثل ما طلبت)
            $table->string('phone')->nullable();    // رقم ولي الأمر أو الطالب
            $table->foreignId('halaqa_id')
                  ->nullable()
                  ->constrained('halaqas')
                  ->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['halaqa_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

