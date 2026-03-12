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

       $courses = $lecturer->coursesAsLecturer()
        ->whereHas('timetables', function ($query) use ($today, $now) {
            $query->where('day', $today)
                  ->whereTime('start_time', '<=', $now)
                  ->whereTime('end_time', '>=', $now);
        })
        ->with('timetables')
        ->get();

      $activeSessions = \App\Models\Session::where('lecturer_id', $lecturer->id)
        ->where('status', 'active')
        ->with('course')
        ->get();

       return view('lecturer.dashboard', compact('courses', 'activeSessions'));
    }

     public function startSession(Request $request)
   {
      $request->validate([
        'course_id' => 'required|exists:courses,id',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
      ]);

      $lecturer = Auth::user();

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
    'start_time' => now(),
    'end_time' => now()->addHours(2), // class duration
    'qr_token' => Str::uuid(), // generates unique QR token
    'attendance_code' => strtoupper(Str::random(6)), // manual attendance code
    'status' => 'active',
    'venue_latitude' => $request->latitude,
    'venue_longitude' => $request->longitude,
    'allowed_radius' => 100,
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