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
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }


    public function serialNumber()
    {
        return $this->belongsTo(SerialNumber::class, 'serial_number_id', 'id');
    }

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id', 'id');
    }

    public function importJob()
    {
        return $this->belongsTo(ImportJob::class, 'import_job_id', 'id');
    }
}

