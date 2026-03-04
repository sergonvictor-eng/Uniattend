@extends('layouts.app')

@section('title', 'My Courses')

@section('content')
<div class="card">
    <h2>My Courses</h2>
    
    @if($courses->count() > 0)
        <div style="display: grid; gap: 1.5rem; margin-top: 1.5rem;">
            @foreach($courses as $course)
                <div style="border: 1px solid #e0e0e0; padding: 1.5rem; border-radius: 8px;">
                    <h3 style="color: #001f3f; margin-bottom: 0.5rem;">{{ $course->course_name }}</h3>
                    <p style="color: #666; margin-bottom: 1rem;">{{ $course->course_code }}</p>
                    
                    @if($course->timetables->count() > 0)
                        <h4 style="font-size: 1rem; margin-bottom: 0.5rem;">Schedule:</h4>
                        <div style="display: grid; gap: 0.5rem;">
                            @foreach($course->timetables as $timetable)
                                <div style="background-color: #f5f5f5; padding: 0.75rem; border-radius: 4px; font-size: 0.9rem;">
                                    <strong>{{ $timetable->day }}</strong> - 
                                    {{ \Carbon\Carbon::parse($timetable->start_time)->format('H:i') }} to 
                                    {{ \Carbon\Carbon::parse($timetable->end_time)->format('H:i') }}
                                    @if($timetable->venue)
                                        | {{ $timetable->venue }}
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <p style="color: #666; margin-top: 1rem;">No courses assigned yet.</p>
    @endif
</div>
@endsection
