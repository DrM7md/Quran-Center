<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('halaqas', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // اسم الحلقة
            $table->foreignId('teacher_id')         // المحفظ (User)
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('halaqas');
    }
};
