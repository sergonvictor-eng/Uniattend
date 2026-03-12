@extends('layouts.app')

@section('title', 'Scan QR Code')

@section('content')
<div class="card">
    <h2>Scan QR Code to Mark Attendance</h2>
    
    <div id="scanner-container" style="margin-top: 2rem;">
        <div style="background-color: #f5f5f5; padding: 2rem; border-radius: 8px; text-align: center;">
            <video id="qr-video" style="max-width: 100%; height: auto; border-radius: 8px;"></video>
            
            <div style="margin-top: 1rem;">
                <button id="start-scan" class="btn btn-primary">Start Camera</button>
                <button id="stop-scan" class="btn btn-primary" style="background-color: #dc3545; display: none;">Stop Camera</button>
            </div>
        </div>

        <div id="manual-input" style="margin-top: 2rem; display: none;">
            <h3 style="margin-bottom: 1rem;">Or Enter Code Manually</h3>
            <textarea id="qr-data" rows="4" style="width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px;" placeholder="Paste QR code data here"></textarea>
            <button onclick="processManualCode()" class="btn btn-primary" style="margin-top: 1rem;">Submit</button>
        </div>
    </div>
              <!-- Attendance Code Entry -->
            <div style="margin-top:2rem; padding:1.5rem; border:1px solid #ddd; border-radius:8px;">
    
               <h3>Enter Attendance Code</h3>
                <p style="color:#666; font-size:0.9rem;">
                 If your camera cannot scan the QR code, enter the code shown by the lecturer.
               </p>

              <form id="attendanceCodeForm">
                 <input 
                 type="text"
                     id="attendance_code"
                        placeholder="Enter attendance code"
                style="padding:10px; width:200px; text-transform:uppercase;"
            required
        >

         <button type="submit" class="btn btn-primary">
            Submit Code
        </button>
      </form>

       </div>
    <div id="result" style="margin-top: 2rem; display: none;"></div>
   </div>
@endsection

@section('extra-js')
<script type="module">
import QrScanner from 'https://cdn.jsdelivr.net/npm/qr-scanner@1.4.2/qr-scanner.min.js';

let qrScanner = null;
const video = document.getElementById('qr-video');
const startBtn = document.getElementById('start-scan');
const stopBtn = document.getElementById('stop-scan');
const resultDiv = document.getElementById('result');

startBtn.addEventListener('click', async () => {
    try {
        qrScanner = new QrScanner(
            video,
            result => handleScan(result.data),
            {
                returnDetailedScanResult: true,
                highlightScanRegion: true,
            }
        );
        
        await qrScanner.start();
        startBtn.style.display = 'none';
        stopBtn.style.display = 'inline-block';
    } catch (error) {
        alert('Error starting camera: ' + error.message);
        document.getElementById('manual-input').style.display = 'block';
    }
});

stopBtn.addEventListener('click', () => {
    if (qrScanner) {
        qrScanner.stop();
        qrScanner = null;
    }
    startBtn.style.display = 'inline-block';
    stopBtn.style.display = 'none';
});

async function handleScan(data) {
    if (qrScanner) {
        qrScanner.stop();
        stopBtn.style.display = 'none';
        startBtn.style.display = 'inline-block';
    }

    try {
        const qrData = JSON.parse(data);
        await markAttendance(qrData);
    } catch (error) {
        showResult('error', 'Invalid QR code format');
    }
}

window.processManualCode = async function() {
    const data = document.getElementById('qr-data').value;
    try {
        const qrData = JSON.parse(data);
        await markAttendance(qrData);
    } catch (error) {
        showResult('error', 'Invalid QR code format');
    }
};

async function markAttendance(qrData) {
    if (!navigator.geolocation) {
        showResult('error', 'Geolocation is not supported by your browser');
        return;
    }

    showResult('info', 'Getting your location...');

    navigator.geolocation.getCurrentPosition(async (position) => {
        try {
            const response = await fetch('/api/attendance/scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    session_id: qrData.session_id,
                    token: qrData.token,
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                })
            });

            const data = await response.json();

            if (response.ok) {
                showResult('success', `
                    <h3>Attendance Marked Successfully!</h3>
                    <p>Course: ${data.attendance.session.course.course_name}</p>
                    <p>Status: ${data.attendance.status}</p>
                    <p>Distance from venue: ${data.distance.toFixed(2)} meters</p>
                    <p style="margin-top: 1rem;">
                        <a href="{{ route('student.dashboard') }}" class="btn btn-primary">Back to Dashboard</a>
                    </p>
                `);
            } else {
                showResult('error', data.message);
            }
        } catch (error) {
            showResult('error', 'Error marking attendance: ' + error.message);
        }
    }, (error) => {
        showResult('error', 'Error getting location: ' + error.message + '. Please enable location services.');
    }, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
    });
}

function showResult(type, message) {
    const colors = {
        success: { bg: '#d4edda', text: '#155724', border: '#c3e6cb' },
        error: { bg: '#f8d7da', text: '#721c24', border: '#f5c6cb' },
        info: { bg: '#d1ecf1', text: '#0c5460', border: '#bee5eb' }
    };

    const color = colors[type];
    resultDiv.innerHTML = message;
    resultDiv.style.display = 'block';
    resultDiv.style.padding = '1.5rem';
    resultDiv.style.borderRadius = '4px';
    resultDiv.style.backgroundColor = color.bg;
    resultDiv.style.color = color.text;
    resultDiv.style.border = `1px solid ${color.border}`;
}
document.getElementById('attendanceCodeForm').addEventListener('submit', async function(e){

    e.preventDefault();

    const code = document.getElementById('attendance_code').value;

    if (!navigator.geolocation) {
        showResult('error', 'Geolocation is not supported by your browser');
        return;
    }

    showResult('info', 'Getting your location...');

    navigator.geolocation.getCurrentPosition(async (position) => {

        try {

            const response = await fetch('/api/attendance/code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    attendance_code: code,
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                })
            });

            const data = await response.json();

            if(response.ok){
                showResult('success', data.message);
            } else {
                showResult('error', data.message);
            }

        } catch(error){
            showResult('error', 'Error submitting attendance');
        }

    });

});
</script>
@endsection
