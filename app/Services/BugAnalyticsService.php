<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BugAnalyticsService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('PYTHON_ANALYTICS_SERVICE_URL', 'http://127.0.0.1:8001');
    }

    /**
     * Analyze a new bug report (Stage 1).
     */
    public function analyzeBugReport(\App\Models\Bug $bug): array
    {
        try {
            $fullText = implode(' ', array_filter([
                $bug->description,
                $bug->reproduce_steps,
                $bug->expected_result,
                $bug->environment,
            ]));

            $response = Http::timeout(10)
                ->post("{$this->baseUrl}/analyze-bug-report", [
                    'text' => $fullText,
                    'title' => $bug->title,
                ]);

            $result = [];
            if ($response->successful()) {
                $result = $response->json();
            } else {
                Log::error('Bug Analytics service returned non-successful response: ' . $response->status() . ' - ' . $response->body());
            }



            return $result;

        } catch (\Exception $e) {
            Log::error('Bug Analytics service failed (analyzeBugReport): ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get AI analysis from cache (bug_ai_cache) if available,
     * otherwise call FastAPI and store the result in cache.
     */
    public function getOrAnalyze(\App\Models\Bug $bug): array
    {
        $mode = config('app.mode');

        // Mode import: pakai behavior lama (tanpa cache)
        if ($mode !== 'readonly') {
            return $this->analyzeBugReport($bug);
        }

        // Mode readonly: pakai cache tabel bug_ai_cache
        $bugId = $bug->idbug;

        $cache = \App\Models\BugAiCache::where('bug_id', $bugId)->first();
        if ($cache) {
            return [
                'sentiment_label' => $cache->sentiment_label,
                'sentiment_score' => $cache->sentiment_score,
                'severity_recommended' => $cache->severity_recommended,
                'severity_recommendation_reason' => $cache->severity_recommendation_reason,
            ];
        }

        // Belum ada cache -> panggil FastAPI
        $result = $this->analyzeBugReport($bug);

        // Simpan ke cache jika berhasil
        if (!empty($result)) {
            \App\Models\BugAiCache::updateOrCreate(
                ['bug_id' => $bugId],
                [
                    'sentiment_label' => $result['sentiment_label'] ?? null,
                    'sentiment_score' => $result['sentiment_score'] ?? null,
                    'severity_recommended' => $result['severity_recommended'] ?? null,
                    'severity_recommendation_reason' => $result['severity_recommendation_reason'] ?? null,
                    'content_hash' => hash('sha256', ($bug->title ?? '') . ($bug->description ?? '')),
                ]
            );
        }

        return $result;
    }

}
