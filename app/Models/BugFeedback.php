<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BugFeedback extends Model
{
    use HasFactory;

    protected $table = 'bug_feedback';

    protected $fillable = ['bug_id', 'from_user_id', 'to_user_id', 'message', 'is_read'];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function bug()
    {
        return $this->belongsTo(Bug::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
