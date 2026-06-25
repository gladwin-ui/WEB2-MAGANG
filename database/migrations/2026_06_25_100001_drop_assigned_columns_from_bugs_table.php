<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop assigned_to and assigned_at from bugs table.
     *
     * These columns were added for the Mechanic Assignment feature (v0.6),
     * which has since been removed along with the entire Mechanic role (v0.7).
     * They serve no purpose in the current Admin-only read-only analytics system
     * and are cleaned up here to keep the schema unambiguous.
     */
    public function up(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            // Drop FK constraint first, then the columns themselves.
            // Use dropForeignIfExists to be safe against environments
            // where the migration was partially applied.
            if (Schema::hasColumn('bugs', 'assigned_to')) {
                $table->dropForeign(['assigned_to']);
                $table->dropColumn(['assigned_to', 'assigned_at']);
            }
        });
    }

    /**
     * Reverse not supported — this is a forward-only migration.
     */
    public function down(): void
    {
        // forward-only
    }
};
