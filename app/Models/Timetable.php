<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'day',
        'start_time',
        'end_time',
        'venue',
        'venue_latitude',
        'venue_longitude',
        'allowed_radius',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'venue_latitude' => 'decimal:8',
        'venue_longitude' => 'decimal:8',
        'allowed_radius' => 'integer',
    ];

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
