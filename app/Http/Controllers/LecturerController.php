<?php

namespace App\Http\Controllers;
use App\Models\Session;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
class LecturerController extends Controller
{
    public function dashboard()
    {
        $lecturer = Auth::user();

        $courses = $lecturer->coursesAsLecturer;

        $activeSessions = Session::where('lecturer_id', $lecturer->id)
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

        $session = Session::create([
            'course_id' => $request->course_id,
            'lecturer_id' => $lecturer->id,
            'start_time' => now(),
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
}