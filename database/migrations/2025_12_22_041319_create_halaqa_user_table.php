<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('halaqa_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('halaqa_id')->constrained('halaqas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // حلقة أساسية للمحفظ (عادة وحدة)
            $table->boolean('is_primary')->default(false);

            // لو تبي تغطية مؤقتة (اختياري الآن)
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->timestamps();

            $table->unique(['halaqa_id', 'user_id']);
            $table->index(['user_id', 'is_primary']);
            $table->index(['halaqa_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('halaqa_user');
    }
};
