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
                ]);

            $result = [];
            if ($response->successful()) {
                $result = $response->json();
            } else {
                Log::error('Bug Analytics service returned non-successful response: ' . $response->status() . ' - ' . $response->body());
            }

            if (!empty($result['is_spam']) && $result['is_spam'] === true) {
                $result['severity_recommended'] = 'Spam';
                $result['severity_recommendation_reason'] = 'Laporan terdeteksi spam berdasarkan analisis teks keseluruhan. Rekomendasi severity tidak berlaku.';
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Bug Analytics service failed (analyzeBugReport): ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Analyze root cause and repair action to categorize damage (Stage 2).
     */
    public function analyzeDamageCause(string $rootCause, string $repairAction): array
    {
        try {
            $response = Http::timeout(10)
                ->post("{$this->baseUrl}/analyze-damage-cause", [
                    'root_cause' => $rootCause,
                    'repair_action' => $repairAction,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Bug Analytics service returned non-successful response (damage): ' . $response->status() . ' - ' . $response->body());
            return [];

        } catch (\Exception $e) {
            Log::error('Bug Analytics service failed (analyzeDamageCause): ' . $e->getMessage());
            return [];
        }
    }
}
