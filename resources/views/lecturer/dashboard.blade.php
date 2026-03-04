@extends('layouts.app')

@section('title', 'Lecturer Dashboard')

@section('content')
<div class="card">
    <h2>Lecturer Dashboard</h2>
    <p>Welcome, {{ auth()->user()->name }}</p>
</div>

<div class="card">
    <h3>My Courses</h3>
    @if($courses->count() > 0)
        <div style="display: grid; gap: 1rem; margin-top: 1rem;">
            @foreach($courses as $course)
                <div style="border: 1px solid #e0e0e0; padding: 1rem; border-radius: 4px;">
                    <h4 style="margin-bottom: 0.5rem;">{{ $course->course_name }}</h4>
                    <p style="color: #666; font-size: 0.9rem;">{{ $course->course_code }}</p>
                    <button onclick="startSession({{ $course->id }})" class="btn btn-primary" style="margin-top: 1rem;">
                        Start Attendance Session
                    </button>
                </div>
            @endforeach
        </div>
    @else
        <p style="color: #666;">No courses assigned yet.</p>
    @endif
</div>

<div class="card">
    <h3>Active Sessions</h3>
    <div id="activeSessions">
        @if($activeSessions->count() > 0)
            @foreach($activeSessions as $session)
                <div style="border: 1px solid #e0e0e0; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                    <h4>{{ $session->course->course_name }}</h4>
                    <p style="color: #666; font-size: 0.9rem;">Started: {{ $session->start_time->format('M d, Y H:i') }}</p>
                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <a href="{{ route('lecturer.qr-code', $session->id) }}" class="btn btn-primary">
                            View QR Code
                        </a>
                        <button onclick="endSession({{ $session->id }})" class="btn btn-primary" style="background-color: #dc3545;">
                            End Session
                        </button>
                    </div>
                </div>
            @endforeach
        @else
            <p style="color: #666;">No active sessions.</p>
        @endif
    </div>
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
