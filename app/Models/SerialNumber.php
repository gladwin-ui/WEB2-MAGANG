<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SerialNumber extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'sn_code', 'type'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function bugs()
    {
        return $this->hasMany(Bug::class);
    }
}
