<?php

namespace App\Http\Controllers;
use App\Models\Session;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
class LecturerController extends Controller
{
    public function dashboard()
   {
      $lecturer = Auth::user();

      $now = now();
      $today = $now->format('l'); // Monday, Tuesday etc
      $currentTime = $now->format('H:i');

      // Get today's classes for this lecturer
      $todayClasses = $lecturer->coursesAsLecturer()
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
                'can_start_session' => $startTime && $currentTime >= $startTime && (!$endTime || $currentTime <= $endTime),
                'is_active' => Session::where('course_id', $course->id)
                    ->where('status', 'active')
                    ->exists(),
            ];
        });

      $activeSessions = \App\Models\Session::where('lecturer_id', $lecturer->id)
        ->where('status', 'active')
        ->with('course')
        ->get();

       return view('lecturer.dashboard', compact('todayClasses', 'activeSessions'));
   }

     public function startSession(Request $request)
   {
      $request->validate([
        'course_id' => 'required|exists:courses,id',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
      ]);

      $lecturer = Auth::user();
      $now = now();
      $today = $now->format('l');
      $currentTime = $now->format('H:i');

      // Check if course has timetable for today and current time
      $course = \App\Models\Course::find($request->course_id);
      $timetable = \App\Models\Timetable::where('course_id', $request->course_id)
          ->where('day', $today)
          ->first();

      if (!$timetable) {
        return response()->json([
            'message' => 'No class scheduled for this course today'
        ], 400);
      }

      // Check if current time is within class time
      $startTime = $timetable->start_time;
      $endTime = $timetable->end_time;

      if ($currentTime < $startTime) {
        return response()->json([
            'message' => 'Class time has not started yet. Start time: ' . $startTime
        ], 400);
      }

      if ($endTime && $currentTime > $endTime) {
        return response()->json([
            'message' => 'Class time has ended. End time: ' . $endTime
        ], 400);
      }

      // Check if an active session already exists
      $existingSession = Session::where('course_id', $request->course_id)
        ->where('status', 'active')
        ->first();

      if ($existingSession) {
        return response()->json([
            'message' => 'Session already active'
        ], 400);
      }

      $session = Session::create([
    'course_id' => $request->course_id,
    'lecturer_id' => $lecturer->id,
    'start_time' => $now,
    'end_time' => $endTime ? \Carbon\Carbon::parse($today . ' ' . $endTime) : $now->addHours(2),
    'qr_token' => Str::uuid(), // generates unique QR token
    'attendance_code' => strtoupper(Str::random(6)), // manual attendance code
    'status' => 'active',
    'venue_latitude' => $request->latitude,
    'venue_longitude' => $request->longitude,
    'allowed_radius' => $timetable->allowed_radius ?? 100,
   ]);

      return response()->json([
        'message' => 'Session started successfully',
        'session' => $session
      ]);
   }

    public function endSession(Session $session)
    {
        $lecturer = Auth::user();

        if ($session->lecturer_id !== $lecturer->id) {
            abort(403);
        }

        $session->update([
            'status' => 'ended',
            'end_time' => now()
        ]);

        return response()->json([
        'message' => 'Session ended successfully'
        ]);
    }
    public function showQRCode(Session $session)
    { 
        return view('lecturer.qr-code', compact('session'));
    }

    public function getQRCodeImage(Session $session)
  {
    $qrData = route('student.scan', ['token' => $session->qr_token]);

    return response(
        \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(300)
            ->generate($qrData),
        200
    )->header('Content-Type', 'image/svg+xml');
 }
  public function courses()
  {
    $lecturer = Auth::user();

    $courses = Course::where('lecturer_id', $lecturer->id)->get();

    return view('lecturer.courses', compact('courses'));
  }
    public function sessionDetails($session)
  {
    $session = \App\Models\Session::with('attendances')->findOrFail($session);

    return response()->json([
        'id' => $session->id,
        'course' => $session->course->course_name,
        'attendances' => $session->attendances
    ]);
   }
}