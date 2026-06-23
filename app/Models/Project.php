<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function serialNumbers()
    {
        return $this->hasMany(SerialNumber::class);
    }

    public function bugs()
    {
        return $this->hasMany(Bug::class);
    }
}
