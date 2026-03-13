@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="card">
    <h2>Administrator Dashboard</h2>
    <p>Welcome, {{ auth()->user()->name }}</p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
    <div class="stat-card">
        <h3>Total Students</h3>
        <p class="stat-number">{{ $stats['total_students'] }}</p>
    </div>
    
    <div class="stat-card">
        <h3>Total Lecturers</h3>
        <p class="stat-number">{{ $stats['total_lecturers'] }}</p>
    </div>
    
    <div class="stat-card">
        <h3>Total Courses</h3>
        <p class="stat-number">{{ $stats['total_courses'] }}</p>
    </div>
    
    <div class="stat-card">
        <h3>Total Attendances</h3>
        <p class="stat-number">{{ $stats['total_attendances'] }}</p>
    </div>
</div>

<div class="card" style="margin-top: 2rem;">
    <h2>Quick Actions</h2>
    <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;">
        <a href="{{ route('admin.upload-users') }}" class="btn btn-primary">Upload Users</a>
        <a href="{{ route('admin.upload-timetable') }}" class="btn btn-primary">Upload Timetable</a>
        <a href="{{ route('admin.reports') }}" class="btn btn-primary">View Reports</a>
    </div>
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
        font-family: 'Montserrat', sans-serif;
    }

    .stat-number {
        color: #001f3f;
        font-size: 2.5rem;
        font-weight: 700;
        font-family: 'Montserrat', sans-serif;
    }
</style>
@endsection
