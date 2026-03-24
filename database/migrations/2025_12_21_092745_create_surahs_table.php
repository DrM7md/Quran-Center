<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('surahs', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('number'); // رقم السورة 1..114
            $table->string('name');                 // اسم السورة بالعربي
         $table->unsignedSmallInteger('ayahs_count')->nullable();

            $table->timestamps();

            $table->unique('number');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surahs');
    }
};
