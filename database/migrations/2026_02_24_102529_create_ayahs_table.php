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
        Schema::create('ayahs', function (Blueprint $table) {
            $table->unsignedSmallInteger('surah_number');      // 1-114
            $table->unsignedSmallInteger('number_in_surah');   // 1-286
            $table->text('text');                              // النص العثماني

            // Primary key مركّب — لا نحتاج id تسلسلي
            $table->primary(['surah_number', 'number_in_surah']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ayahs');
    }
};
