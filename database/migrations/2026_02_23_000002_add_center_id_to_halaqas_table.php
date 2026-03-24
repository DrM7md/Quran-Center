<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('halaqas', function (Blueprint $table) {
            $table->foreignId('center_id')->nullable()->after('id')->constrained('centers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('halaqas', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Center::class);
            $table->dropColumn('center_id');
        });
    }
};
