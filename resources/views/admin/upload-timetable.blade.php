@extends('layouts.app')

@section('title', 'Upload Timetable')

@section('content')
<div class="card">
    <h2>Upload Timetable</h2>
    
    <div style="background-color: #e7f3ff; padding: 1rem; border-radius: 4px; margin-bottom: 2rem;">
        <h3 style="margin-bottom: 0.5rem; font-size: 1rem;">File Format Requirements:</h3>
        <p style="font-size: 0.9rem;">
            <strong>Columns:</strong> course_name, lecturer_staff_id, day, start_time, end_time, venue_latitude, venue_longitude, allowed_radius
        </p>
        <p style="font-size: 0.85rem; margin-top: 0.5rem; color: #666;">
            Day format: Monday, Tuesday, etc. | Time format: HH:MM (24-hour) | Radius in meters
        </p>
    </div>

    <form action="{{ route('admin.upload-timetable.post') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="form-group">
            <label for="file">Upload File (Excel or CSV)</label>
            <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv" required>
            <small style="color: #666; display: block; margin-top: 0.5rem;">
                Maximum file size: 10MB. Supported formats: .xlsx, .xls, .csv
            </small>
        </div>

        <button type="submit" class="btn btn-primary">Upload Timetable</button>
    </form>
</div>

<div class="card">
    <h3>Template Download</h3>
    <p style="margin-bottom: 1rem;">Download sample template to ensure correct format:</p>
    
    <a href="#" onclick="downloadTemplate(); return false;" class="btn btn-primary" style="background-color: #28a745;">
        Download Timetable Template
    </a>
</div>
@endsection

@section('extra-js')
<script>
function downloadTemplate() {
    const csv = 'course_name,lecturer_staff_id,day,start_time,end_time,venue_latitude,venue_longitude,allowed_radius\nDatabase Systems,LEC001,Monday,08:00,10:00,-1.286389,36.817223,50\nWeb Development,LEC001,Tuesday,10:00,12:00,-1.286389,36.817223,50';
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'timetable_template.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
@endsection
