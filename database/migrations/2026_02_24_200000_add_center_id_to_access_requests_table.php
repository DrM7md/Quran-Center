<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('access_requests', function (Blueprint $table) {
            // null = طلب من admin → يصل للسوبر أدمن
            // قيمة = طلب من محفظ → يصل لأدمن ذلك المركز
            $table->unsignedBigInteger('center_id')->nullable()->after('message');
            $table->foreign('center_id')->references('id')->on('centers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('access_requests', function (Blueprint $table) {
            $table->dropForeign(['center_id']);
            $table->dropColumn('center_id');
        });
    }
};
