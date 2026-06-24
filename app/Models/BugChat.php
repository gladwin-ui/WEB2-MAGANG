<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BugChat extends Model
{
    protected $fillable = ['bug_id', 'sender_id', 'message'];

    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function bug() {
        return $this->belongsTo(Bug::class);
    }
}
