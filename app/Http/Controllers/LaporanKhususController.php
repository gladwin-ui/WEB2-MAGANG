<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKhususController extends Controller
{
    public function index(Request $request)
    {
        // Daftar produk untuk dropdown (hanya yang punya minimal 1 bug aktif)
        $products = Project::whereHas('bugs')->orderBy('name')->get();

        $selectedProductId = $request->query('product_id', $products->first()->id ?? null);

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

        $masalahTop5 = [];
        $rootCauseTop5 = [];
        $totalBugs = 0;
        $severityMix = collect(['Critical' => 0, 'Major' => 0, 'Minor' => 0]);

        if ($selectedProductId) {
            // SoftDeletes pada model Bug otomatis mengecualikan data terhapus
            $bugs = Bug::where('project_id', $selectedProductId)->get();
            $totalBugs = $bugs->count();

            // Visualisasi 1: Masalah Tersering (title + description)
            $masalahTexts = $bugs->map(fn($b) => trim(($b->title ?? '') . ' ' . ($b->description ?? '')))
                                 ->filter()
                                 ->values()
                                 ->toArray();
            $masalahTop5 = $this->groupByTrigram($masalahTexts);

            // Visualisasi 2: Root Cause Tersering
            $rootCauseTexts = $bugs->map(fn($b) => trim($b->root_cause ?? ''))
                                   ->filter()
                                   ->values()
                                   ->toArray();
            $rootCauseTop5 = $this->groupByTrigram($rootCauseTexts);

            // BAGIAN 2: Severity mix produk terpilih
            $mix = $bugs->countBy('severity');
            foreach ($mix as $sev => $total) {
                if ($severityMix->has($sev)) {
                    $severityMix[$sev] = $total;
                }
            }
        }

        return view('laporan-khusus.index', compact(
            'products', 'selectedProductId', 'masalahTop5', 'rootCauseTop5', 'totalBugs',
            'reworkRates', 'severityMix'
        ));
    }

    /**
     * Kelompokkan teks berbasis trigram bermakna pertama (anti-chaining).
     * Tiap laporan hanya punya 1 signature sehingga tidak ada transitive
     * chaining yang bisa membentuk kelompok raksasa.
     *
     * @return array [ ['label' => string, 'count' => int], ... ] top 5 desc
     */
    private function groupByTrigram(array $texts): array
    {
        $groups = [];

        foreach ($texts as $text) {
            $signature = $this->extractSignature($text);
            if ($signature === '') {
                continue;
            }
            $groups[$signature] = ($groups[$signature] ?? 0) + 1;
        }

        arsort($groups);
        $top5 = array_slice($groups, 0, 5, true);

        $result = [];
        foreach ($top5 as $label => $count) {
            $result[] = ['label' => $label, 'count' => $count];
        }

        return $result;
    }

    /**
     * Ekstrak signature = trigram bermakna pertama (3 kata pertama setelah
     * buang stopword). Jika teks punya < 3 kata bermakna, pakai apa adanya.
     */
    private function extractSignature(string $text): string
    {
        static $stopwords = [
            // Indonesia
            'di', 'ke', 'dari', 'pada', 'untuk', 'dengan', 'dalam', 'atas', 'oleh', 'saat',
            'ketika', 'yang', 'dan', 'atau', 'juga', 'akan', 'telah', 'sudah', 'ini', 'itu',
            'ada', 'adalah', 'sebuah', 'suatu', 'para', 'nya', 'sebagai', 'karena', 'agar',
            'supaya', 'namun', 'tetapi', 'tapi', 'jika', 'kalau', 'maka', 'bila', 'terjadi',
            'terdapat', 'secara', 'hingga', 'sampai', 'setelah', 'sebelum', 'selama', 'antara',
            'unit', 'per', 'tiap', 'setiap',
            // Inggris
            'the', 'a', 'an', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'and', 'or', 'is',
            'are', 'was', 'were', 'be', 'been', 'this', 'that', 'it', 'as', 'by', 'from', 'when',
        ];

        // Bersihkan: non-alfanumerik jadi spasi
        $cleaned = preg_replace('/[^\w\s]/u', ' ', $text);
        $words = preg_split('/\s+/u', trim($cleaned), -1, PREG_SPLIT_NO_EMPTY);

        $meaningful = [];
        foreach ($words as $word) {
            $wl = mb_strtolower($word);
            if (in_array($wl, $stopwords, true)) {
                continue;
            }
            // buang kata sangat pendek kecuali mengandung angka (5V, 3A)
            if (mb_strlen($word) <= 2 && !preg_match('/\d/', $word)) {
                continue;
            }
            // buang angka murni
            if (ctype_digit($word)) {
                continue;
            }

            $meaningful[] = $wl;
            if (count($meaningful) >= 3) {
                break; // trigram pertama = signature
            }
        }

        if (empty($meaningful)) {
            return '';
        }

        // Title Case agar label rapi & case-insensitive grouping konsisten
        return implode(' ', array_map(
            fn($w) => mb_strtoupper(mb_substr($w, 0, 1)) . mb_substr($w, 1),
            $meaningful
        ));
    }
}
