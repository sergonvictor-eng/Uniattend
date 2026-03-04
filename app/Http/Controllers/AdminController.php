<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Timetable;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Imports\TimetableImport;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_students' => User::students()->count(),
            'total_lecturers' => User::lecturers()->count(),
            'total_courses' => Course::count(),
            'total_attendances' => Attendance::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function uploadUsersForm()
    {
        return view('admin.upload-users');
    }

    public function uploadUsers(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'user_type' => 'required|in:student,lecturer',
        ]);

        try {
            $file = $request->file('file');
            $data = Excel::toArray([], $file)[0];

            // Skip header row
            $headers = array_shift($data);

            $imported = 0;
            $errors = [];

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 because of header and 0-index

                // Map columns based on user type
                if ($request->user_type === 'student') {
                    $userData = [
                        'identifier' => $row[0] ?? null,
                        'name' => $row[1] ?? null,
                        'email' => $row[2] ?? null,
                        'course' => $row[3] ?? null,
                        'role' => 'student',
                    ];
                } else {
                    $userData = [
                        'identifier' => $row[0] ?? null,
                        'name' => $row[1] ?? null,
                        'email' => $row[2] ?? null,
                        'department' => $row[3] ?? null,
                        'role' => 'lecturer',
                    ];
                }

                // Validate
                $validator = Validator::make($userData, [
                    'identifier' => 'required|string|unique:users,identifier',
                    'name' => 'required|string|max:255',
                    'email' => 'nullable|email',
                ]);

                if ($validator->fails()) {
                    $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // Create user with default password
                $userData['password'] = Hash::make('password123');
                $userData['is_active'] = true;

                User::create($userData);
                $imported++;
            }

            $message = "{$imported} users imported successfully.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode('; ', $errors);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    public function uploadTimetableForm()
    {
        return view('admin.upload-timetable');
    }

    public function uploadTimetable(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $data = Excel::toArray([], $file)[0];

            // Skip header row
            $headers = array_shift($data);

            $imported = 0;
            $errors = [];

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2;

                $courseName = $row[0] ?? null;
                $lecturerIdentifier = $row[1] ?? null;
                $day = $row[2] ?? null;
                $startTime = $row[3] ?? null;
                $endTime = $row[4] ?? null;
                $venueLat = $row[5] ?? null;
                $venueLng = $row[6] ?? null;
                $radius = $row[7] ?? 50;

                // Find or create course
                $lecturer = User::where('identifier', $lecturerIdentifier)
                    ->where('role', 'lecturer')
                    ->first();

                if (!$lecturer) {
                    $errors[] = "Row {$rowNumber}: Lecturer not found";
                    continue;
                }

                $course = Course::firstOrCreate(
                    ['course_name' => $courseName],
                    [
                        'course_code' => strtoupper(substr(str_replace(' ', '', $courseName), 0, 6)) . rand(100, 999),
                        'lecturer_id' => $lecturer->id,
                    ]
                );

                // Create timetable entry
                $timetableData = [
                    'course_id' => $course->id,
                    'day' => $day,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'venue_latitude' => $venueLat,
                    'venue_longitude' => $venueLng,
                    'allowed_radius' => $radius,
                ];

                $validator = Validator::make($timetableData, [
                    'course_id' => 'required|exists:courses,id',
                    'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                    'start_time' => 'required',
                    'end_time' => 'required',
                    'venue_latitude' => 'required|numeric|between:-90,90',
                    'venue_longitude' => 'required|numeric|between:-180,180',
                    'allowed_radius' => 'required|integer|min:10',
                ]);

                if ($validator->fails()) {
                    $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                Timetable::create($timetableData);
                $imported++;
            }

            $message = "{$imported} timetable entries imported successfully.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode('; ', $errors);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    public function reports()
    {
        return view('admin.reports');
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:attendance,courses,students',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = Attendance::with(['student', 'session.course']);

        if ($request->start_date) {
            $query->whereDate('timestamp', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('timestamp', '<=', $request->end_date);
        }

        $attendances = $query->get();

        return response()->json([
            'data' => $attendances,
            'count' => $attendances->count(),
        ]);
    }
}
