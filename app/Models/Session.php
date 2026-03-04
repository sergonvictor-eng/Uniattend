<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Session extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'lecturer_id',
        'start_time',
        'end_time',
        'qr_token',
        'status',
        'venue_latitude',
        'venue_longitude',
        'allowed_radius',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'venue_latitude' => 'decimal:8',
        'venue_longitude' => 'decimal:8',
        'allowed_radius' => 'integer',
    ];

    // Auto-generate QR token on creation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            if (empty($session->qr_token)) {
                $session->qr_token = Str::random(64);
            }
        });
    }

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeEnded($query)
    {
        return $query->where('status', 'ended');
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function endSession()
    {
        $this->update([
            'status' => 'ended',
            'end_time' => now(),
        ]);
    }

    public function getAttendanceCount(): int
    {
        return $this->attendances()->count();
    }
}
