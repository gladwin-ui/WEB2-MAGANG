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

}
