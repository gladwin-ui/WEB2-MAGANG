<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isReporter(): bool
    {
        return $this->role === 'reporter';
    }

    public function isMekanik(): bool
    {
        return $this->role === 'mekanik';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Relationships
    public function reportedBugs()
    {
        return $this->hasMany(Bug::class, 'reported_by');
    }

    public function fixedBugs()
    {
        return $this->hasMany(Bug::class, 'fixed_by');
    }

    public function sentFeedback()
    {
        return $this->hasMany(BugFeedback::class, 'from_user_id');
    }

    public function receivedFeedback()
    {
        return $this->hasMany(BugFeedback::class, 'to_user_id');
    }
}
