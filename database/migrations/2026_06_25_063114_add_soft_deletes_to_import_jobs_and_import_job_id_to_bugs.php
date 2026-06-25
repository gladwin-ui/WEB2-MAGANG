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
        Schema::table('import_jobs', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('bugs', function (Blueprint $table) {
            $table->unsignedInteger('import_job_id')->nullable()->after('id');
            
            // Note: Since import_jobs.id is unsignedInteger/unsignedBigInteger, we match it.
            // We don't enforce a hard foreign key constraint if database seeders or legacy data might lack it,
            // but we can index it for performance.
            $table->index('import_job_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_jobs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('bugs', function (Blueprint $table) {
            $table->dropIndex(['import_job_id']);
            $table->dropColumn('import_job_id');
        });
    }
};
