@extends('layouts.app')

@section('title', 'Upload Users')

@section('content')
<div class="card">
    <h2>Upload Users (Students & Lecturers)</h2>
    
    <div style="background-color: #e7f3ff; padding: 1rem; border-radius: 4px; margin-bottom: 2rem;">
        <h3 style="margin-bottom: 0.5rem; font-size: 1rem;">File Format Requirements:</h3>
        <p style="margin-bottom: 0.5rem; font-size: 0.9rem;"><strong>For Students:</strong> admission_number, name, email, course</p>
        <p style="font-size: 0.9rem;"><strong>For Lecturers:</strong> staff_id, name, email, department</p>
    </div>

    <form action="{{ route('admin.upload-users.post') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="form-group">
            <label for="user_type">User Type</label>
            <select name="user_type" id="user_type" required>
                <option value="">Select user type</option>
                <option value="student">Students</option>
                <option value="lecturer">Lecturers</option>
            </select>
        </div>

        <div class="form-group">
            <label for="file">Upload File (Excel or CSV)</label>
            <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv" required>
            <small style="color: #666; display: block; margin-top: 0.5rem;">
                Maximum file size: 10MB. Supported formats: .xlsx, .xls, .csv
            </small>
        </div>

        <button type="submit" class="btn btn-primary">Upload Users</button>
    </form>
</div>

<div class="card">
    <h3>Template Download</h3>
    <p style="margin-bottom: 1rem;">Download sample templates to ensure correct format:</p>
    
    <div style="display: flex; gap: 1rem;">
        <a href="#" onclick="downloadStudentTemplate(); return false;" class="btn btn-primary" style="background-color: #28a745;">
            Download Student Template
        </a>
        <a href="#" onclick="downloadLecturerTemplate(); return false;" class="btn btn-primary" style="background-color: #28a745;">
            Download Lecturer Template
        </a>
    </div>
</div>
@endsection

@section('extra-js')
<script>
function downloadStudentTemplate() {
    const csv = 'admission_number,name,email,course\nSTU001,John Doe,john@example.com,Computer Science\nSTU002,Jane Smith,jane@example.com,Engineering';
    downloadCSV(csv, 'student_template.csv');
}

function downloadLecturerTemplate() {
    const csv = 'staff_id,name,email,department\nLEC001,Dr. John Smith,john@example.com,IT Department\nLEC002,Dr. Jane Doe,jane@example.com,Engineering';
    downloadCSV(csv, 'lecturer_template.csv');
}

function downloadCSV(content, filename) {
    const blob = new Blob([content], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
@endsection
