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
        Schema::table('bugs', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('import_jobs', function (Blueprint $table) {
            $table->unsignedInteger('deleted_count')->default(0)->after('skipped_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('import_jobs', function (Blueprint $table) {
            $table->dropColumn('deleted_count');
        });
    }
};
