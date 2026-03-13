@extends('layouts.app')

@section('title', 'Scan QR Code')

@section('content')

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: white;
    min-height: 100vh;
    font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    font-weight: 500;
}

.scan-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 1rem;
}

.page-header {
    text-align: center;
    margin-bottom: 2rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #001f3f;
    margin-bottom: 0.5rem;
    text-align: center;
    font-family: 'Montserrat', sans-serif;
}

.page-subtitle {
    font-size: 1rem;
    color: #666;
    text-align: center;
    font-family: 'Montserrat', sans-serif;
    font-weight: 400;
}

.scan-grid {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.scan-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,31,63,0.08);
    padding: 2rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.scan-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,31,63,0.12);
}

.card-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.card-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #001f3f;
    margin-bottom: 0.5rem;
    text-align: center;
    font-family: 'Montserrat', sans-serif;
}

.card-description {
    font-size: 0.9rem;
    color: #666;
    text-align: center;
    line-height: 1.4;
    font-family: 'Montserrat', sans-serif;
    font-weight: 400;
}

.scanner-section {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.scanner-wrapper {
    position: relative;
    width: 250px;
    height: 250px;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(0,31,63,0.1);
    margin-bottom: 1.5rem;
}

#qr-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 16px;
}

.scanner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(0,31,63,0.03) 25%, transparent 25%, transparent 75%, rgba(0,31,63,0.03) 75%, rgba(0,31,63,0.03)),
                linear-gradient(45deg, rgba(0,31,63,0.03) 25%, transparent 25%, transparent 75%, rgba(0,31,63,0.03) 75%, rgba(0,31,63,0.03));
    background-size: 20px 20px;
    background-position: 0 0, 10px 10px;
    pointer-events: none;
    border-radius: 16px;
}

.scanner-corners {
    position: absolute;
    top: 15px;
    left: 15px;
    right: 15px;
    bottom: 15px;
    pointer-events: none;
}

.corner {
    position: absolute;
    width: 25px;
    height: 25px;
    border: 3px solid #001f3f;
}

.corner-tl {
    top: 0;
    left: 0;
    border-right: none;
    border-bottom: none;
    border-radius: 6px 0 0 0;
}

.corner-tr {
    top: 0;
    right: 0;
    border-left: none;
    border-bottom: none;
    border-radius: 0 6px 0 0;
}

.corner-bl {
    bottom: 0;
    left: 0;
    border-right: none;
    border-top: none;
    border-radius: 0 0 0 6px;
}

.corner-br {
    bottom: 0;
    right: 0;
    border-left: none;
    border-top: none;
    border-radius: 0 0 6px 0;
}

.camera-controls {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-family: 'Montserrat', sans-serif;
}

.btn-primary {
    background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
    color: white;
    box-shadow: 0 3px 12px rgba(0,31,63,0.15);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #003366 0%, #001f3f 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0,31,63,0.25);
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    box-shadow: 0 3px 12px rgba(220,53,69,0.15);
}

.btn-danger:hover {
    background: linear-gradient(135deg, #c82333 0%, #dc3545 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(220,53,69,0.25);
}

.code-form {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.2rem;
}

.code-input {
    width: 100%;
    max-width: 300px;
    padding: 0.9rem;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 2px;
    transition: all 0.3s ease;
    background: #f8f9fa;
    font-family: 'Montserrat', sans-serif;
}

.code-input:focus {
    outline: none;
    border-color: #001f3f;
    background: white;
    box-shadow: 0 0 0 3px rgba(0,31,63,0.1);
}

.result-container {
    margin-top: 1.5rem;
}

.alert {
    padding: 0.9rem 1.2rem;
    border-radius: 10px;
    font-weight: 500;
    text-align: center;
    animation: slideIn 0.3s ease;
    font-family: 'Montserrat', sans-serif;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-info {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    color: #0c5460;
    border: 1px solid #bee5eb;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .scan-container {
        padding: 0.8rem;
    }
    
    .page-title {
        font-size: 1.8rem;
    }
    
    .scan-card {
        padding: 1.5rem;
    }
    
    .scanner-wrapper {
        width: 220px;
        height: 220px;
    }
    
    .card-title {
        font-size: 1.2rem;
    }
    
    .card-description {
        font-size: 0.85rem;
    }
}

@media (max-width: 480px) {
    .scan-container {
        padding: 0.5rem;
    }
    
    .page-header {
        margin-bottom: 1.5rem;
    }
    
    .page-title {
        font-size: 1.6rem;
    }
    
    .scan-card {
        padding: 1.2rem;
    }
    
    .scanner-wrapper {
        width: 200px;
        height: 200px;
    }
    
    .btn {
        padding: 0.6rem 1.2rem;
        font-size: 0.85rem;
    }
    
    .code-input {
        max-width: 280px;
        padding: 0.8rem;
        font-size: 0.9rem;
    }
}
</style>

<div class="scan-container">
    <div class="page-header">
        <h1 class="page-title">Attendance Scanner</h1>
        <p class="page-subtitle">Scan QR code or enter attendance code to mark your presence</p>
    </div>

    <div class="scan-grid">
        <!-- QR Scanner Card -->
        <div class="scan-card">
            <div class="card-header">
                <h2 class="card-title">Scan QR Code</h2>
                <p class="card-description">Position the QR code within the frame to automatically mark your attendance</p>
            </div>

            <div class="scanner-section">
                <div class="scanner-wrapper">
                    <video id="qr-video"></video>
                    <div class="scanner-overlay"></div>
                    <div class="scanner-corners">
                        <div class="corner corner-tl"></div>
                        <div class="corner corner-tr"></div>
                        <div class="corner corner-bl"></div>
                        <div class="corner corner-br"></div>
                    </div>
                </div>

                <div class="camera-controls">
                    <button id="start-scan" class="btn btn-primary">
                        Start Camera
                    </button>
                    <button id="stop-scan" class="btn btn-danger" style="display: none;">
                        Stop Camera
                    </button>
                </div>
            </div>
        </div>

        <!-- Manual Code Entry Card -->
        <div class="scan-card">
            <div class="card-header">
                <h2 class="card-title">Enter Attendance Code</h2>
                <p class="card-description">If your camera cannot scan the QR code, manually enter the attendance code provided by your lecturer</p>
            </div>

            <form id="attendanceCodeForm" class="code-form">
                <input
                    type="text"
                    id="attendance_code"
                    class="code-input"
                    placeholder="ENTER ATTENDANCE CODE"
                    maxlength="10"
                    required
                >
                <button type="submit" class="btn btn-primary">
                    Submit Code
                </button>
            </form>
        </div>
    </div>

    <div id="result" class="result-container"></div>
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
                highlightScanRegion: false,
                highlightCodeOutline: false,
            }
        );

        await qrScanner.start();
        
        startBtn.style.display = 'none';
        stopBtn.style.display = 'inline-block';
        
        showAlert('info', 'Camera started. Position QR code within the frame.');
        
    } catch (error) {
        showAlert('error', 'Camera permission denied or unavailable. Please check your browser settings.');
    }
});

stopBtn.addEventListener('click', () => {
    if (qrScanner) {
        qrScanner.stop();
        qrScanner = null;
    }
    
    startBtn.style.display = 'inline-block';
    stopBtn.style.display = 'none';
    
    showAlert('info', 'Camera stopped.');
});

async function handleScan(data) {
    if (qrScanner) {
        qrScanner.stop();
    }
    
    try {
        const qrData = JSON.parse(data);
        await markAttendance(qrData);
    } catch (error) {
        showAlert('error', 'Invalid QR Code format. Please try again.');
        startBtn.style.display = 'inline-block';
        stopBtn.style.display = 'none';
    }
}

async function markAttendance(qrData) {
    if (!navigator.geolocation) {
        showAlert('error', 'Location services are not supported by your browser.');
        return;
    }
    
    showAlert('info', 'Getting your location...');
    
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
                showAlert('success', '✓ Attendance marked successfully! Your presence has been recorded.');
                startBtn.style.display = 'inline-block';
                stopBtn.style.display = 'none';
            } else {
                showAlert('error', data.message || 'Failed to mark attendance. Please try again.');
                startBtn.style.display = 'inline-block';
                stopBtn.style.display = 'none';
            }
            
        } catch (error) {
            showAlert('error', 'Network error. Please check your connection and try again.');
            startBtn.style.display = 'inline-block';
            stopBtn.style.display = 'none';
        }
    }, (error) => {
        showAlert('error', 'Unable to get your location. Please enable location services and try again.');
        startBtn.style.display = 'inline-block';
        stopBtn.style.display = 'none';
    });
}

// Handle manual code submission
document.getElementById('attendanceCodeForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const code = document.getElementById('attendance_code').value.trim();
    
    if (!code) {
        showAlert('error', 'Please enter an attendance code.');
        return;
    }
    
    showAlert('info', 'Processing attendance code...');
    
    try {
        const response = await fetch('/api/attendance/code', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                code: code.toUpperCase()
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showAlert('success', '✓ Attendance marked successfully! Your presence has been recorded.');
            document.getElementById('attendance_code').value = '';
        } else {
            showAlert('error', data.message || 'Invalid attendance code. Please check and try again.');
        }
        
    } catch (error) {
        showAlert('error', 'Network error. Please check your connection and try again.');
    }
});

function showAlert(type, message) {
    let alertClass = 'alert-info';
    if (type === 'error') alertClass = 'alert-danger';
    if (type === 'success') alertClass = 'alert-success';
    
    resultDiv.innerHTML = `
        <div class="alert ${alertClass}">
            ${message}
        </div>
    `;
    
    // Auto-hide success messages after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            resultDiv.innerHTML = '';
        }, 5000);
    }
}
</script>

@endsection