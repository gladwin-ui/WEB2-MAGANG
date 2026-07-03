<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $spamColumns = [
                'is_spam',
                'spam_reason',
                'spam_confidence',
                'spam_tier',
            ];

            foreach ($spamColumns as $column) {
                if (Schema::hasColumn('bugs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->boolean('is_spam')->default(false);
            $table->text('spam_reason')->nullable();
        });
    }
};
