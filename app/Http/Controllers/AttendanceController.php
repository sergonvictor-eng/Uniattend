<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Calculate distance between two GPS coordinates using Haversine formula
     * Returns distance in meters
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $deltaLat = $lat2 - $lat1;
        $deltaLon = $lon2 - $lon1;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1) * cos($lat2) *
             sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distance in meters
    }

    public function scanQRCode(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:sessions,id',
            'token' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $student = Auth::user();

        // Verify user is a student
        if (!$student->isStudent()) {
            return response()->json([
                'message' => 'Only students can mark attendance'
            ], 403);
        }

        // Get session using session ID and QR token
        $session = Session::where('id', $request->session_id)
          ->where('qr_token', $request->token)
          ->first();

          if (!$session) {
            return response()->json([
               'message' => 'Invalid or expired QR code'
            ], 400);
}

        // Check if session is active
         if ($session->status !== 'active') {
            return response()->json([
        'message' => 'This session has ended'
            ], 400);
}

         // Check if session has expired
         if ($session->end_time && now()->greaterThan($session->end_time)) {
        return response()->json([
              'message' => 'Attendance time has expired'
          ], 400);
}
 
        // Check if student has already marked attendance
        $existingAttendance = Attendance::where('student_id', $student->id)
            ->where('session_id', $session->id)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'message' => 'You have already marked attendance for this session',
                'attendance' => $existingAttendance,
            ], 400);
        }

        // Calculate distance from venue
        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $session->venue_latitude,
            $session->venue_longitude
        );

        // Check if student is within allowed radius
        if ($distance > $session->allowed_radius) {
            return response()->json([
                'message' => 'You are too far from the venue to mark attendance',
                'distance' => round($distance, 2),
                'allowed_radius' => $session->allowed_radius,
            ], 400);
        }

        // Determine status (present or late)
        $sessionStartTime = $session->start_time;
        $currentTime = now();
        $minutesLate = $currentTime->diffInMinutes($sessionStartTime);
        $status = $minutesLate > 15 ? 'late' : 'present';

        // Create attendance record
        $attendance = Attendance::create([
            'student_id' => $student->id,
            'session_id' => $session->id,
            'timestamp' => now(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'distance_from_venue' => round($distance, 2),
            'status' => $status,
        ]);

        return response()->json([
            'message' => 'Attendance marked successfully',
            'attendance' => $attendance->load('session.course'),
            'distance' => round($distance, 2),
        ]);
    }

    public function studentAttendance()
    {
        $student = Auth::user();

        if (!$student->isStudent()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $attendances = $student->attendances()
            ->with('session.course')
            ->orderBy('timestamp', 'desc')
            ->get();

        return response()->json($attendances);
    }

    public function sessionAttendance($sessionId)
    {
        $session = Session::with(['attendances.student', 'course'])
            ->findOrFail($sessionId);

        $user = Auth::user();

        // Check authorization
        if ($user->isLecturer() && $session->lecturer_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($user->isStudent()) {
            return response()->json([
                'message' => 'Students cannot view session attendance'
            ], 403);
        }

        return response()->json([
            'session' => $session,
            'attendance_count' => $session->attendances->count(),
            'attendances' => $session->attendances,
        ]);
    }

    public function studentDashboard()
    {
        $student = Auth::user();
        
        $now = now();
        $today = $now->format('l'); // Monday, Tuesday etc
        $currentTime = $now->format('H:i');

        // Get today's classes for this student
        $todayClasses = [];
        if ($student->course) {
            $todayClasses = \App\Models\Course::where('course_name', $student->course)
                ->whereHas('timetables', function ($query) use ($today) {
                    $query->where('day', $today);
                })
                ->with(['timetables' => function ($query) use ($today) {
                    $query->where('day', $today)->orderBy('start_time');
                }])
                ->get()
                ->map(function ($course) use ($currentTime) {
                    $timetable = $course->timetables->first();
                    $startTime = $timetable ? $timetable->start_time : null;
                    $endTime = $timetable ? $timetable->end_time : null;
                    
                    return [
                        'course' => $course,
                        'timetable' => $timetable,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'can_scan' => $startTime && $currentTime >= $startTime && (!$endTime || $currentTime <= $endTime),
                        'has_active_session' => \App\Models\Session::where('course_id', $course->id)
                            ->where('status', 'active')
                            ->exists(),
                    ];
                });
        }
        
        // Get student's attendance history
        $attendances = $student->attendances()
            ->with('session.course')
            ->orderBy('timestamp', 'desc')
            ->limit(10)
            ->get();

        // Get active sessions (could be expanded to show available sessions)
        $activeSessions = Session::active()
            ->with('course')
            ->get();

        $stats = [
            'total_attendances' => $student->attendances()->count(),
            'present_count' => $student->attendances()->where('status', 'present')->count(),
            'late_count' => $student->attendances()->where('status', 'late')->count(),
        ];

        return view('student.dashboard', compact('todayClasses', 'attendances', 'activeSessions', 'stats'));
    }

    public function scanPage()
    {
        return view('student.scan');
    }
    public function submitAttendanceCode(Request $request)
{
    $request->validate([
        'attendance_code' => 'required|string',
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
    ]);

    $student = Auth::user();

    // Verify user is a student
    if (!$student->isStudent()) {
        return response()->json([
            'message' => 'Only students can mark attendance'
        ], 403);
    }

    // Find active session using attendance code
    $session = Session::where('attendance_code', $request->attendance_code)
        ->where('status', 'active')
        ->first();

    if (!$session) {
        return response()->json([
            'message' => 'Invalid or expired attendance code'
        ], 400);
    }

    // Check if session has expired
    if ($session->end_time && now()->greaterThan($session->end_time)) {
        return response()->json([
            'message' => 'Attendance time has expired'
        ], 400);
    }

    // Check if student already marked attendance
    $existingAttendance = Attendance::where('student_id', $student->id)
        ->where('session_id', $session->id)
        ->first();

    if ($existingAttendance) {
        return response()->json([
            'message' => 'You have already marked attendance for this session',
            'attendance' => $existingAttendance,
        ], 400);
    }

    // Calculate distance from venue
    $distance = $this->calculateDistance(
        $request->latitude,
        $request->longitude,
        $session->venue_latitude,
        $session->venue_longitude
    );

    // Check allowed radius
    if ($distance > $session->allowed_radius) {
        return response()->json([
            'message' => 'You are too far from the venue to mark attendance',
            'distance' => round($distance, 2),
            'allowed_radius' => $session->allowed_radius,
        ], 400);
    }

    // Determine status (present or late)
    $sessionStartTime = $session->start_time;
    $currentTime = now();
    $minutesLate = $currentTime->diffInMinutes($sessionStartTime);
    $status = $minutesLate > 15 ? 'late' : 'present';

    // Create attendance
    $attendance = Attendance::create([
        'student_id' => $student->id,
        'session_id' => $session->id,
        'timestamp' => now(),
        'latitude' => $request->latitude,
        'longitude' => $request->longitude,
        'distance_from_venue' => round($distance, 2),
        'status' => $status,
    ]);

    return response()->json([
        'message' => 'Attendance marked successfully using code',
        'attendance' => $attendance->load('session.course'),
        'distance' => round($distance, 2),
    ]);
}
}

