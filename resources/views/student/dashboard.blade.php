@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
<div class="card">
    <h2>Student Dashboard</h2>
    <p>Welcome, {{ auth()->user()->name }}</p>
    @if(auth()->user()->course)
        <p style="color: #666;">Course: {{ auth()->user()->course }}</p>
    @endif
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="stat-card">
        <h3>Total Classes</h3>
        <p class="stat-number">{{ $stats['total_attendances'] }}</p>
    </div>
    
    <div class="stat-card">
        <h3>Present</h3>
        <p class="stat-number" style="color: #28a745;">{{ $stats['present_count'] }}</p>
    </div>
    
    <div class="stat-card">
        <h3>Late</h3>
        <p class="stat-number" style="color: #ffc107;">{{ $stats['late_count'] }}</p>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h3>Recent Attendance</h3>
        <a href="{{ route('student.scan') }}" class="btn btn-primary">Scan QR Code</a>
    </div>
    
    @if($attendances->count() > 0)
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f5f5f5;">
                        <th style="padding: 0.75rem; border: 1px solid #ddd; text-align: left;">Course</th>
                        <th style="padding: 0.75rem; border: 1px solid #ddd; text-align: left;">Date & Time</th>
                        <th style="padding: 0.75rem; border: 1px solid #ddd; text-align: left;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $attendance)
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">{{ $attendance->session->course->course_name }}</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">{{ $attendance->timestamp->format('M d, Y H:i') }}</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">
                                <span style="
                                    padding: 0.25rem 0.75rem; 
                                    border-radius: 12px; 
                                    font-size: 0.85rem;
                                    background-color: {{ $attendance->status === 'present' ? '#d4edda' : '#fff3cd' }};
                                    color: {{ $attendance->status === 'present' ? '#155724' : '#856404' }};
                                ">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p style="color: #666;">No attendance records yet. Scan a QR code to mark your first attendance!</p>
    @endif
</div>
@endsection

@section('extra-css')
<style>
    .stat-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
    }

    .stat-card h3 {
        color: #666;
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .stat-number {
        color: #001f3f;
        font-size: 2rem;
        font-weight: 700;
    }
</style>
@endsection
