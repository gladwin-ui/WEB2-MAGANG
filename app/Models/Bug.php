<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bug extends Model
{
    use HasFactory, SoftDeletes;


    protected $guarded = [];

    protected $casts = [
        'is_rework' => 'boolean',
        'sentiment_score' => 'float',
        'closed_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function serialNumber()
    {
        return $this->belongsTo(SerialNumber::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function importJob()
    {
        return $this->belongsTo(ImportJob::class);
    }
}
