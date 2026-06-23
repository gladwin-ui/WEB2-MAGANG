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
        Schema::create('bugs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('title');
            $table->enum('severity', ['Critical', 'Major', 'Minor'])->default('Major');
            
            $table->foreignId('serial_number_id')->nullable()->constrained('serial_numbers')->nullOnDelete();
            $table->string('sn_code_snapshot')->nullable();
            
            $table->enum('reporter_type', ['produk', 'sub'])->default('produk');
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            
            $table->text('description')->nullable();
            $table->string('product_version')->nullable();
            $table->string('environment')->nullable();
            
            $table->text('reproduce_steps')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('repair_action')->nullable();
            
            $table->boolean('is_rework')->default(false);
            $table->string('attachment_path')->nullable();
            $table->text('expected_result')->nullable();
            
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['OPEN', 'CLOSED'])->default('OPEN');
            $table->foreignId('fixed_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->dateTime('closed_at')->nullable();
            
            // AI Stage 1: Submitted Report Analytics
            $table->enum('sentiment_label', ['positive', 'neutral', 'negative', 'spam'])->nullable();
            $table->float('sentiment_score')->nullable();
            $table->boolean('is_spam')->default(false);
            $table->string('spam_reason')->nullable();
            $table->enum('severity_recommended', ['Critical', 'Major', 'Minor'])->nullable();
            $table->string('severity_recommendation_reason')->nullable();
            
            // AI Stage 2: Closing/Damage Categorization
            $table->string('damage_category')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bugs');
    }
};
