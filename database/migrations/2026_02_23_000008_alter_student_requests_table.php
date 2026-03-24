<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add a standalone index on guardian_id so FK no longer depends
        //         on the compound unique index
        Schema::table('student_requests', function (Blueprint $table) {
            $table->index('guardian_id', 'student_requests_guardian_id_idx');
        });

        // Step 2: Now we can safely drop the compound unique index
        Schema::table('student_requests', function (Blueprint $table) {
            $table->dropUnique('student_requests_guardian_id_student_id_unique');
        });

        // Step 3: Make student_id nullable + add guardian-entered child fields
        Schema::table('student_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->nullable()->change();
            $table->string('student_name')->after('center_id');
            $table->unsignedTinyInteger('student_age')->nullable()->after('student_name');
            $table->text('student_notes')->nullable()->after('student_age');
        });
    }

    public function down(): void
    {
        Schema::table('student_requests', function (Blueprint $table) {
            $table->dropColumn(['student_name', 'student_age', 'student_notes']);
            $table->unsignedBigInteger('student_id')->nullable(false)->change();
        });

        Schema::table('student_requests', function (Blueprint $table) {
            $table->dropIndex('student_requests_guardian_id_idx');
            $table->unique(['guardian_id', 'student_id']);
        });
    }
};
