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

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (config('app.mode') === 'readonly') {
            $this->table = 'bug';
            $this->primaryKey = 'idbug';
        }
    }

    protected static function booted()
    {
        if (config('app.mode') === 'readonly') {
            // Nonaktifkan soft-delete scope karena tabel bug kantor tidak punya kolom deleted_at
            static::addGlobalScope('withoutSoftDeleteReadonly', function ($builder) {
                $builder->withoutGlobalScope(\Illuminate\Database\Eloquent\SoftDeletingScope::class);
            });
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Column Mapping Accessors (Active ONLY in Read-Only Mode)
    |--------------------------------------------------------------------------
    */

    public function getTitleAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return $this->attributes['bug_title'] ?? null;
        }
        return $value;
    }

    public function getDescriptionAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return $this->attributes['bugdesc'] ?? null;
        }
        return $value;
    }

    public function getRootCauseAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return $this->attributes['rootcause'] ?? null;
        }
        return $value;
    }

    public function getStatusAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return strtoupper($this->attributes['bugstatus'] ?? '');
        }
        return $value;
    }

    public function getReportedByAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return $this->attributes['bugcreatedby'] ?? null;
        }
        return $value;
    }

    public function getFixedByAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return $this->attributes['bugfixby'] ?? null;
        }
        return $value;
    }

    public function getClosedAtAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return $this->attributes['bugclosesavedate'] ?? null;
        }
        return $value;
    }

    public function getProductVersionAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return $this->attributes['bugversion'] ?? null;
        }
        return $value;
    }

    public function getEnvironmentAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return $this->attributes['bugenvi'] ?? null;
        }
        return $value;
    }

    public function getReproduceStepsAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return $this->attributes['bugreproduce'] ?? null;
        }
        return $value;
    }

    public function getExpectedResultAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return $this->attributes['bugexpected'] ?? null;
        }
        return $value;
    }

    public function getAttachmentPathAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return $this->attributes['bugfile'] ?? null;
        }
        return $value;
    }

    public function getSnCodeSnapshotAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return $this->attributes['sn_code'] ?? null;
        }
        return $value;
    }

    public function getReporterTypeAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return $this->attributes['tipe_pelapor'] ?? null;
        }
        return $value;
    }

    public function getProjectIdAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return $this->attributes['idproject'] ?? null;
        }
        return $value;
    }

    public function getSerialNumberIdAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return $this->attributes['id_sn'] ?? null;
        }
        return $value;
    }

    public function getDeviceIdAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return $this->attributes['iddevice'] ?? null;
        }
        return $value;
    }

    /*
    |--------------------------------------------------------------------------
    | AI Column Guard Accessors (Return null in Read-Only Mode)
    |--------------------------------------------------------------------------
    */

    public function getSentimentScoreAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return null;
        }
        return $value;
    }

    public function getSentimentLabelAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return null;
        }
        return $value;
    }

    public function getSeverityRecommendedAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return null;
        }
        return $value;
    }

    public function getImportJobIdAttribute($value)
    {
        if (config('app.mode') === 'readonly') {
            return null;
        }
        return $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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
