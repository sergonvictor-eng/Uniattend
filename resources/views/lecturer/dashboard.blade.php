@extends('layouts.app')

@section('title', 'Lecturer Dashboard')

@section('content')
<div class="card">
    <h2>Lecturer Dashboard</h2>
    <p>Welcome, {{ auth()->user()->name }}</p>
</div>

<div class="card">
    <h3>Today's Classes - {{ now()->format('l, F j, Y') }}</h3>
    @if($todayClasses->count() > 0)
        <div style="display: grid; gap: 1rem; margin-top: 1rem;">
            @foreach($todayClasses as $classData)
                @php
                    $course = $classData['course'];
                    $timetable = $classData['timetable'];
                    $canStart = $classData['can_start_session'];
                    $isActive = $classData['is_active'];
                @endphp
                <div style="border: 1px solid #e0e0e0; padding: 1.5rem; border-radius: 8px; {{ $canStart ? 'background-color: #f8f9fa;' : '' }}">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <div>
                            <h4 style="margin-bottom: 0.5rem; color: #001f3f;">{{ $course->course_name }}</h4>
                            <p style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">{{ $course->course_code }}</p>
                            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 0.5rem;">
                                <span style="font-size: 0.85rem; color: #666;">
                                    <strong>Time:</strong> {{ $timetable->start_time }} - {{ $timetable->end_time }}
                                </span>
                                <span style="font-size: 0.85rem; padding: 0.25rem 0.5rem; border-radius: 12px; background-color: {{ $canStart ? '#d4edda' : '#f8d7da' }}; color: {{ $canStart ? '#155724' : '#721c24' }};">
                                    {{ $canStart ? 'Active Now' : 'Not Yet Time' }}
                                </span>
                            </div>
                        </div>
                        @if($isActive)
                            <span style="padding: 0.5rem 1rem; background-color: #28a745; color: white; border-radius: 4px; font-size: 0.85rem;">
                                Session Active
                            </span>
                        @endif
                    </div>
                    
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        @if($canStart && !$isActive)
                            <button onclick="startSession({{ $course->id }})" class="btn btn-primary">
                                Start Attendance Session
                            </button>
                        @elseif($isActive)
                            <a href="{{ route('lecturer.qr-code', $course->id) }}" class="btn btn-primary">
                                View QR Code
                            </a>
                            <button onclick="endSession({{ $course->id }})" class="btn btn-primary" style="background-color: #dc3545;">
                                End Session
                            </button>
                        @else
                            <button disabled class="btn btn-primary" style="background-color: #6c757d; cursor: not-allowed;">
                                Start Attendance Session
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p style="color: #666;">No classes scheduled for today.</p>
    @endif
</div>

<div class="card">
    <h3>All Courses</h3>
    @if(auth()->user()->coursesAsLecturer()->count() > 0)
        <div style="display: grid; gap: 1rem; margin-top: 1rem;">
            @foreach(auth()->user()->coursesAsLecturer as $course)
                <div style="border: 1px solid #e0e0e0; padding: 1rem; border-radius: 4px;">
                    <h4 style="margin-bottom: 0.5rem;">{{ $course->course_name }}</h4>
                    <p style="color: #666; font-size: 0.9rem;">{{ $course->course_code }}</p>
                </div>
            @endforeach
        </div>
    @else
        <p style="color: #666;">No courses assigned yet.</p>
    @endif
</div>
@endsection

@section('extra-js')
<script>
async function startSession(courseId) {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser');
        return;
    }

    navigator.geolocation.getCurrentPosition(async (position) => {
        try {
            const response = await fetch('/lecturer/sessions/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    course_id: courseId,
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                })
            });

            const data = await response.json();

            if (response.ok) {
                alert('Session started successfully!');
                location.reload();
            } else {
                alert(data.message || 'Failed to start session');
            }
        } catch (error) {
            alert('Error starting session: ' + error.message);
        }
    }, (error) => {
        alert('Error getting location: ' + error.message);
    });
}

async function endSession(sessionId) {
    if (!confirm('Are you sure you want to end this session?')) {
        return;
    }

    try {
        const response = await fetch(`/lecturer/sessions/${sessionId}/end`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok) {
            alert('Session ended successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to end session');
        }
    } catch (error) {
        alert('Error ending session: ' + error.message);
    }
}
</script>
@endsection
