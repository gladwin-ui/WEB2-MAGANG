<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $actorNames = DB::table('bugs')
            ->leftJoin('users as reporters', 'bugs.reported_by', '=', 'reporters.id')
            ->leftJoin('users as fixers', 'bugs.fixed_by', '=', 'fixers.id')
            ->select([
                'bugs.id',
                'bugs.reported_by',
                'bugs.fixed_by',
                'reporters.name as reporter_name',
                'fixers.name as fixer_name',
            ])
            ->get()
            ->mapWithKeys(function ($bug) {
                return [
                    $bug->id => [
                        'reported_by' => $bug->reporter_name ?? ($bug->reported_by ? (string) $bug->reported_by : null),
                        'fixed_by' => $bug->fixer_name ?? ($bug->fixed_by ? (string) $bug->fixed_by : null),
                    ],
                ];
            });

        Schema::table('bugs', function (Blueprint $table) {
            $table->dropForeign(['reported_by']);
            $table->dropForeign(['fixed_by']);
            $table->dropColumn(['reported_by', 'fixed_by']);
        });

        Schema::table('bugs', function (Blueprint $table) {
            $table->string('reported_by')->nullable()->after('expected_result');
            $table->string('fixed_by')->nullable()->after('status');
        });

        foreach ($actorNames as $bugId => $names) {
            DB::table('bugs')
                ->where('id', $bugId)
                ->update([
                    'reported_by' => $names['reported_by'],
                    'fixed_by' => $names['fixed_by'],
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // kosongkan saja
    }
};
