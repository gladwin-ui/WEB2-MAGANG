<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ImportJob;
use App\Models\Bug;
use Illuminate\Support\Facades\DB;

echo "=== IMPORT JOBS (with trashed) ===\n";
$jobs = ImportJob::withTrashed()->orderByDesc('id')->limit(10)->get();
foreach ($jobs as $j) {
    $trashed = $j->deleted_at ? 'TRASHED' : 'active';
    echo "#{$j->id} | {$j->filename} | status={$j->status} | total={$j->total_rows} inserted={$j->inserted_count} skipped={$j->skipped_count} failed={$j->failed_count} | {$trashed}\n";
}

echo "\n=== BUGS BY import_job_id ===\n";
$rows = DB::table('bugs')
    ->select('import_job_id', DB::raw('count(*) as total'), DB::raw('sum(case when deleted_at is not null then 1 else 0 end) as trashed_count'))
    ->groupBy('import_job_id')
    ->get();
foreach ($rows as $r) {
    $jid = $r->import_job_id ?? 'NULL';
    echo "import_job_id={$jid} | total={$r->total} | trashed={$r->trashed_count}\n";
}

echo "\n=== TOTAL BUGS ===\n";
echo "Active (non-trashed): " . Bug::count() . "\n";
echo "With trashed: " . Bug::withTrashed()->count() . "\n";
echo "Only trashed: " . Bug::onlyTrashed()->count() . "\n";

echo "\n=== CHECK FOR DUPLICATE IDs IN bug.sql FILE ===\n";
$sqlFile = file_get_contents(__DIR__ . '/bug.sql');
preg_match_all('/INSERT\s+INTO\s+`?(\w+)`?\s*\(([^)]+)\)\s*VALUES\s*([\s\S]+?);/i', $sqlFile, $insertMatches, PREG_SET_ORDER);
$totalTuples = 0;
$allIds = [];
foreach ($insertMatches as $match) {
    $cols = array_map('trim', explode(',', $match[2]));
    $idIdx = null;
    foreach ($cols as $i => $col) {
        $col = trim($col, " \t\r\n`'\"");
        if ($col === 'idbug') { $idIdx = $i; break; }
    }
    if ($idIdx === null) continue;
    
    // Count tuples
    $tuples = [];
    $depth = 0; $inStr = false; $escape = false; $start = null;
    $valuesRaw = $match[3];
    $len = strlen($valuesRaw);
    for ($i = 0; $i < $len; $i++) {
        $ch = $valuesRaw[$i];
        if ($escape) { $escape = false; continue; }
        if ($ch === '\\' && $inStr) { $escape = true; continue; }
        if ($ch === "'" && !$escape) { $inStr = !$inStr; continue; }
        if ($inStr) continue;
        if ($ch === '(') { if ($depth === 0) $start = $i; $depth++; }
        elseif ($ch === ')') { $depth--; if ($depth === 0 && $start !== null) { $tuples[] = substr($valuesRaw, $start, $i - $start + 1); $start = null; } }
    }
    
    foreach ($tuples as $tuple) {
        $totalTuples++;
        // Extract the idbug value (first value in the tuple)
        $inner = substr(trim($tuple), 1, -1);
        // Get first value before comma
        $firstComma = 0; $inS = false;
        for ($i = 0; $i < strlen($inner); $i++) {
            if ($inner[$i] === "'") $inS = !$inS;
            if (!$inS && $inner[$i] === ',') { $firstComma = $i; break; }
        }
        $idVal = trim(substr($inner, 0, $firstComma));
        $idVal = trim($idVal, "'\"");
        $allIds[] = $idVal;
    }
}

echo "Total tuples in bug.sql: {$totalTuples}\n";
$idCounts = array_count_values($allIds);
$dups = array_filter($idCounts, fn($c) => $c > 1);
echo "Duplicate IDs: " . count($dups) . "\n";
if (count($dups) > 0) {
    echo "Duplicate ID values:\n";
    foreach ($dups as $id => $count) {
        echo "  id={$id} appears {$count} times\n";
    }
}
echo "Unique IDs: " . count(array_unique($allIds)) . "\n";
