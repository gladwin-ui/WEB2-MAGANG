<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bug extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_rework' => 'boolean',
        'is_spam' => 'boolean',
        'sentiment_score' => 'float',
        'closed_at' => 'datetime',
        'assigned_at' => 'datetime',
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

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function fixer()
    {
        return $this->belongsTo(User::class, 'fixed_by');
    }

    public function assignee() {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function chats() {
        return $this->hasMany(BugChat::class)->orderBy('created_at', 'asc');
    }
}
