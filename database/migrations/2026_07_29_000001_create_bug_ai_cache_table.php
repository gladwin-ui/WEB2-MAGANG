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
        Schema::create('bug_ai_cache', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bug_id')->index();  // merujuk idbug tabel bug
            $table->string('sentiment_label')->nullable();
            $table->float('sentiment_score')->nullable();
            $table->string('severity_recommended')->nullable();
            $table->text('severity_recommendation_reason')->nullable();
            $table->string('content_hash', 64)->nullable(); // hash text+title, deteksi perubahan
            $table->timestamps();

            $table->unique('bug_id'); // 1 cache per bug
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bug_ai_cache');
    }
};
