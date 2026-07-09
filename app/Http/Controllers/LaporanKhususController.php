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
        $writer->setIncludeCharts(true);

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
                $clusters = $response->json('clusters', []);
                if (!empty($clusters)) {
                    return $clusters;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Clustering FastAPI gagal: ' . $e->getMessage());
        }

        return $this->fallbackClustering($texts);
    }

    private function fallbackClustering(array $texts): array
    {
        $stopwords = [
            'di','ke','dari','pada','untuk','dengan','dalam','atas','oleh','saat','ketika',
            'yang','dan','atau','juga','akan','telah','sudah','ini','itu','ada','adalah',
            'sebuah','suatu','para','nya','sebagai','karena','agar','supaya','namun','tetapi',
            'tapi','jika','kalau','maka','bila','terjadi','terdapat','secara','hingga','sampai',
            'setelah','sebelum','selama','antara','unit','per','tiap','setiap','bisa','dapat',
            'tidak','bukan','kurang','belum','lagi','akibat','disebabkan','menyebabkan',
            'mengakibatkan','akibatnya','mengalami','terhadap','berupa',
            'adanya','terjadinya','penyebab','laporan','dilaporkan','melaporkan',
            'membaca','terbaca','mendeteksi','terdeteksi','menampilkan','tampil',
            'mengirim','menerima','berjalan','bekerja','beroperasi','berfungsi',
            'melakukan','memberikan','menghasilkan','menjadi',
            'the','a','an','in','on','at','to','for','of','with','and','or','is','are','was',
            'were','be','been','this','that','it','as','by','from','when','while','after','before',
            'due','caused','causing','failure','issue','problem','result','resulting','not','no',
            'read','reading','detect','detecting','display','work','working',
        ];

        $synonyms = [
            'termal' => 'thermal',
            'mekanis' => 'mekanik',
            'solderan' => 'solder',
            'retakan' => 'retak',
            'menua' => 'aging',
            'korsleting' => 'short',
            'korslet' => 'short',
        ];

        $stopMap = array_flip($stopwords);
        $groups = [];

        foreach ($texts as $text) {
            $clean = trim($text);
            if ($clean === '') continue;

            $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($clean), -1, PREG_SPLIT_NO_EMPTY);
            $filtered = [];
            foreach ($words as $w) {
                $w = $synonyms[$w] ?? $w;
                if (isset($stopMap[$w])) continue;
                if (mb_strlen($w) <= 2 && !is_numeric($w)) continue;
                $filtered[] = $w;
            }

            $unique = array_values(array_unique($filtered));
            $label = 'Tanpa Keterangan';
            if (count($unique) >= 2) {
                $label = ucwords($unique[0] . ' ' . $unique[1]);
            } elseif (count($unique) === 1) {
                $label = ucwords($unique[0]);
            }

            if (!isset($groups[$label])) {
                $groups[$label] = 0;
            }
            $groups[$label]++;
        }

        arsort($groups);
        $result = [];
        foreach (array_slice($groups, 0, 5, true) as $label => $count) {
            $result[] = ['label' => $label, 'count' => $count];
        }

        return $result;
    }
}

