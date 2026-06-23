<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BugAnalyticsService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('PYTHON_ANALYTICS_SERVICE_URL', 'http://127.0.0.1:8000');
    }

    /**
     * Analyze a new bug report description (Stage 1).
     */
    public function analyzeBugReport(string $description): array
    {
        $defaultFallback = [
            'sentiment_label' => 'neutral',
            'sentiment_score' => 0.0,
            'is_spam' => false,
            'spam_reason' => null,
            'severity_recommended' => 'Major',
            'severity_recommendation_reason' => 'AI Service unavailable. Default recommendation to Major.',
        ];

        try {
            $response = Http::timeout(3) // 3 seconds timeout to avoid holding up the request too long
                ->post("{$this->baseUrl}/analyze-bug-report", [
                    'text' => $description,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Bug Analytics service returned non-successful response: ' . $response->status() . ' - ' . $response->body());
            return $defaultFallback;

        } catch (\Exception $e) {
            Log::error('Bug Analytics service failed (analyzeBugReport): ' . $e->getMessage());
            return $defaultFallback;
        }
    }

    /**
     * Analyze root cause and repair action to categorize damage (Stage 2).
     */
    public function analyzeDamageCause(string $rootCause, string $repairAction): array
    {
        $defaultFallback = [
            'damage_category' => 'Lain-lain',
        ];

        try {
            $response = Http::timeout(3)
                ->post("{$this->baseUrl}/analyze-damage-cause", [
                    'root_cause' => $rootCause,
                    'repair_action' => $repairAction,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Bug Analytics service returned non-successful response (damage): ' . $response->status() . ' - ' . $response->body());
            return $defaultFallback;

        } catch (\Exception $e) {
            Log::error('Bug Analytics service failed (analyzeDamageCause): ' . $e->getMessage());
            return $defaultFallback;
        }
    }
}
