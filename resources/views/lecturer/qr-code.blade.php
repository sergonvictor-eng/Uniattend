@extends('layouts.app')

@section('title', 'QR Code')

@section('content')
<div class="card" style="text-align: center;">
    <h2>{{ $session->course->course_name }}</h2>
    <p style="color: #666; margin-bottom: 2rem;">Session QR Code</p>
    
    <div style="background-color: #f5f5f5; padding: 2rem; border-radius: 8px; display: inline-block;">
        <img src="{{ route('lecturer.qr-image', $session->id) }}" alt="QR Code" style="max-width: 300px; height: auto;">
    </div>

    <div style="margin-top: 2rem; padding: 1rem; background-color: #e7f3ff; border-radius: 4px;">
        <p style="font-size: 0.9rem; margin-bottom: 0.5rem;"><strong>Session Started:</strong> {{ $session->start_time->format('M d, Y H:i') }}</p>
        <p style="font-size: 0.9rem;"><strong>Status:</strong> <span style="color: #28a745; font-weight: 600;">Active</span></p>
        <p style="font-size: 0.9rem; margin-top: 0.5rem;"><strong>Attendances:</strong> <span id="attendanceCount">{{ $session->getAttendanceCount() }}</span></p>
    </div>

    <div style="margin-top: 2rem;">
        <a href="{{ route('lecturer.dashboard') }}" class="btn btn-primary">Back to Dashboard</a>
    </div>
</div>
@endsection

@section('extra-js')
<script>
// Auto-refresh attendance count every 5 seconds
setInterval(async () => {
    try {
        const response = await fetch('/api/sessions/{{ $session->id }}', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            document.getElementById('attendanceCount').textContent = data.attendances.length;
        }
    } catch (error) {
        console.error('Error fetching attendance count:', error);
    }
}, 5000);
</script>
@endsection
