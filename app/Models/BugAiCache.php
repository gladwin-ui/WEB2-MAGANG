<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BugAiCache extends Model
{
    protected $table = 'bug_ai_cache';

    protected $fillable = [
        'bug_id', 'sentiment_label', 'sentiment_score',
        'severity_recommended', 'severity_recommendation_reason', 'content_hash',
    ];

    protected $casts = [
        'sentiment_score' => 'float',
    ];
}
