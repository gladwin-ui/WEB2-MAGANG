<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\Project;
use App\Exports\LaporanKhususExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanKhususController extends Controller
{
    public function index(Request $request)
    {
        // Konstanta sampling — MUDAH disetel
        $SAMPLING_LIMIT = 2000;

        // Daftar produk untuk dropdown (hanya yang punya minimal 1 bug aktif)
        $products = Project::whereIn('id', function ($q) {
            $q->select('project_id')->from('bugs')
              ->whereNull('deleted_at')
              ->whereNotNull('project_id')
              ->distinct();
        })->orderBy('id')->get();

        // Default "all" (Semua Produk)
        $selectedProductId = $request->query('product_id', 'all');

        // BAGIAN 1: Rework rate antar produk (top 5) — independent dari dropdown.
        // Minimal 3 bug agar rate tidak menyesatkan (1 bug rework = 100%).
        $reworkRates = DB::table('bugs')
            ->join('projects', 'bugs.project_id', '=', 'projects.id')
            ->whereNull('bugs.deleted_at')
            ->groupBy('projects.id', 'projects.name')
            ->select(
                'projects.id',
                'projects.name',
                DB::raw('COUNT(*) as total_bugs'),
                DB::raw('SUM(CASE WHEN bugs.is_rework = 1 THEN 1 ELSE 0 END) as rework_count'),
                DB::raw('ROUND(SUM(CASE WHEN bugs.is_rework = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) as rework_rate')
            )
            ->havingRaw('COUNT(*) >= 3')
            ->orderByDesc('rework_rate')
            ->orderByDesc('rework_count')
            ->limit(5)
            ->get();

        // Ambil laporan sesuai pilihan
        $bugsQuery = Bug::whereNull('deleted_at');
        if ($selectedProductId !== 'all') {
            $bugsQuery->where('project_id', $selectedProductId);
        }

        $totalLaporan = (clone $bugsQuery)->count();
        $totalBugs = $totalLaporan; // backward compatibility untuk view

        // ===== SAMPLING SAFETY LIMIT =====
        $isSampled = false;
        $sampleSize = $totalLaporan;

        if ($totalLaporan > $SAMPLING_LIMIT) {
            // Catatan: inRandomOrder()->limit() di MySQL untuk data sangat besar bisa lambat (karena random sort).
            // Untuk sekarang cukup, tapi jika nanti data puluhan ribu & terasa lambat, bisa dioptimasi (misal random via ID range).
            $bugs = (clone $bugsQuery)->inRandomOrder()->limit($SAMPLING_LIMIT)->get();
            $isSampled = true;
            $sampleSize = $SAMPLING_LIMIT;
        } else {
            $bugs = $bugsQuery->get();
        }

        // Siapkan teks untuk clustering
        $masalahTexts = $bugs->map(fn($b) => trim(($b->title ?? '') . ' ' . ($b->description ?? '')))
                             ->filter()
                             ->values()
                             ->toArray();
        $rootCauseTexts = $bugs->map(fn($b) => trim($b->root_cause ?? ''))
                               ->filter()
                               ->values()
                               ->toArray();

        // Clustering via FastAPI (fungsi existing)
        $masalahTop5 = $this->clusterViaFastApi($masalahTexts);
        $rootCauseTop5 = $this->clusterViaFastApi($rootCauseTexts);

        // Severity mix (dari $bugs — sampel atau penuh)
        $severityMix = collect(['Critical' => 0, 'Major' => 0, 'Minor' => 0]);
        foreach ($bugs->groupBy('severity') as $sev => $group) {
            if ($severityMix->has($sev)) {
                $severityMix[$sev] = $group->count();
            }
        }

        // Mini-stat: rata-rata rework semua produk (di-filter)
        $avgRework = $reworkRates->avg('rework_rate') ?? 0;
        $avgRework = round($avgRework, 1);

        return view('laporan-khusus.index', compact(
            'products', 'selectedProductId', 'masalahTop5', 'rootCauseTop5', 'totalBugs', 'totalLaporan',
            'isSampled', 'sampleSize', 'reworkRates', 'severityMix', 'avgRework'
        ));
    }

    public function exportExcel(Request $request)
    {
        $SAMPLING_LIMIT = 2000;
        $selectedProductId = $request->query('product_id', 'all');

        $reworkRates = DB::table('bugs')
            ->join('projects', 'bugs.project_id', '=', 'projects.id')
            ->whereNull('bugs.deleted_at')
            ->groupBy('projects.id', 'projects.name')
            ->select(
                'projects.id',
                'projects.name',
                DB::raw('COUNT(*) as total_bugs'),
                DB::raw('SUM(CASE WHEN bugs.is_rework = 1 THEN 1 ELSE 0 END) as rework_count'),
                DB::raw('ROUND(SUM(CASE WHEN bugs.is_rework = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) as rework_rate')
            )
            ->havingRaw('COUNT(*) >= 3')
            ->orderByDesc('rework_rate')
            ->orderByDesc('rework_count')
            ->limit(5)
            ->get();

        $bugsQuery = Bug::with(['project', 'serialNumber'])->whereNull('deleted_at');
        if ($selectedProductId !== 'all') {
            $bugsQuery->where('project_id', $selectedProductId);
        }

        $totalLaporan = (clone $bugsQuery)->count();

        if ($totalLaporan > $SAMPLING_LIMIT) {
            $bugs = (clone $bugsQuery)->inRandomOrder()->limit($SAMPLING_LIMIT)->get();
        } else {
            $bugs = $bugsQuery->orderBy('created_at', 'desc')->get();
        }

        $masalahTexts = $bugs->map(fn($b) => trim(($b->title ?? '') . ' ' . ($b->description ?? '')))->filter()->values()->toArray();
        $rootCauseTexts = $bugs->map(fn($b) => trim($b->root_cause ?? ''))->filter()->values()->toArray();

        $masalahTop5 = $this->clusterViaFastApi($masalahTexts);
        $rootCauseTop5 = $this->clusterViaFastApi($rootCauseTexts);

        $severityMix = collect(['Critical' => 0, 'Major' => 0, 'Minor' => 0]);
        foreach ($bugs->groupBy('severity') as $sev => $group) {
            if ($severityMix->has($sev)) {
                $severityMix[$sev] = $group->count();
            }
        }

        $avgRework = round($reworkRates->avg('rework_rate') ?? 0, 1);

        $productName = 'Semua Produk';
        if ($selectedProductId !== 'all') {
            $proj = Project::find($selectedProductId);
            $productName = $proj ? $proj->name : "Project #{$selectedProductId}";
        }

        $export = new LaporanKhususExport($bugs, $reworkRates, $severityMix, $avgRework, $masalahTop5, $rootCauseTop5, $selectedProductId, $productName);
        $spreadsheet = $export->generate();
        $writer = new Xlsx($spreadsheet);

        return response()->stream(function() use ($writer) {
            $writer->save('php://output');
        }, 200, [
            "Content-Type"        => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "Content-Disposition" => "attachment; filename=Laporan_Khusus_ManufakTrack_" . date('Ymd_His') . ".xlsx",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ]);
    }

    private function clusterViaFastApi(array $texts): array
    {
        if (empty($texts)) return [];

        try {
            $baseUrl = env('PYTHON_ANALYTICS_SERVICE_URL', 'http://127.0.0.1:8001');
            $response = Http::timeout(30)->post("{$baseUrl}/cluster-reports", [
                'texts' => array_values($texts),
            ]);

            if ($response->successful()) {
                return $response->json('clusters', []);
            }
        } catch (\Exception $e) {
            // FastAPI tidak tersedia → return kosong (jangan crash halaman)
            Log::warning('Clustering FastAPI gagal: ' . $e->getMessage());
        }

        return [];
    }
}

