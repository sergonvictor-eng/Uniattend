@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="card">
    <h2>Attendance Reports</h2>
    
    <form id="reportForm">
        <div class="form-group">
            <label for="report_type">Report Type</label>
            <select name="report_type" id="report_type">
                <option value="attendance">Attendance Report</option>
                <option value="courses">Course Report</option>
                <option value="students">Student Report</option>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date">
            </div>

            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Generate Report</button>
    </form>
</div>

<div class="card" id="reportResults" style="display: none;">
    <h3>Report Results</h3>
    <div id="reportData"></div>
</div>
@endsection

@section('extra-js')
<script>
document.getElementById('reportForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const params = new URLSearchParams(formData);
    
    try {
        const response = await fetch('/admin/reports/generate?' + params, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        document.getElementById('reportResults').style.display = 'block';
        document.getElementById('reportData').innerHTML = `
            <p style="margin-bottom: 1rem;"><strong>Total Records:</strong> ${data.count}</p>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f5f5f5;">
                        <th style="padding: 0.75rem; border: 1px solid #ddd; text-align: left;">Student</th>
                        <th style="padding: 0.75rem; border: 1px solid #ddd; text-align: left;">Course</th>
                        <th style="padding: 0.75rem; border: 1px solid #ddd; text-align: left;">Date</th>
                        <th style="padding: 0.75rem; border: 1px solid #ddd; text-align: left;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.data.map(item => `
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">${item.student.name}</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">${item.session.course.course_name}</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">${new Date(item.timestamp).toLocaleString()}</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">${item.status}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    } catch (error) {
        alert('Error generating report: ' + error.message);
    }
});
</script>
@endsection
