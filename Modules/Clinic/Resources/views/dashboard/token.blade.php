@extends('layouts.clinic_token')

@section('title', __('lang_v1.token_display'))

@section('content')
<!-- Main content -->
<section class="content min-height-90hv no-print">
    <div class="container-fluid">
        <!-- Zoom Controls -->
        <div class="zoom-controls" style="position: fixed; bottom: 60px; right: 30px; z-index: 1000; display: flex; align-items: center; background: transparent; padding: 5px 10px; border-radius: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
            <button class="btn btn-sm btn-outline-secondary zoom-out" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; background: transparent;">-</button>
            <span class="zoom-level mx-2" style="min-width: 50px; text-align: center;">100%</span>
            <button class="btn btn-sm btn-outline-secondary zoom-in" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; background: transparent;">+</button>
        </div>
        
        <div id="token-data">
            <!-- Data will be inserted here by Ajax -->
        </div>
    </div>
</section>
@endsection

@section('javascript')
<!-- Screenfull.js for fullscreen functionality -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/screenfull.js/5.1.0/screenfull.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    $(document).ready(function () {
        // Zoom functionality
        let zoomLevel = localStorage.getItem('pageZoom') || 100;
        zoomLevel = parseInt(zoomLevel);
        
        function applyZoom(level) {
            // Limit zoom between 1% and 200%
            level = Math.max(1, Math.min(200, level));
            
            // Apply zoom transform to the entire content
            document.body.style.transform = `scale(${level / 100})`;
            document.body.style.transformOrigin = 'top left';
            
            // Adjust width to prevent horizontal scrolling
            const scale = level / 100;
            document.body.style.width = `${100 / scale}%`;
            
            // Update display
            $('.zoom-level').text(`${level}%`);
            
            // Store in localStorage
            localStorage.setItem('pageZoom', level);
            zoomLevel = level;
        }
        
        // Initialize zoom
        applyZoom(zoomLevel);
        
        // Zoom in button (1% increment)
        $('.zoom-in').click(function() {
            applyZoom(zoomLevel + 1);
        });
        
        // Zoom out button (1% decrement)
        $('.zoom-out').click(function() {
            applyZoom(zoomLevel - 1);
        });

        // Rest of your existing code...
        function updateTime() {
            const now = new Date();
            const dateStr = now.toLocaleDateString('en-GB', {
                weekday: 'long',
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });

            let hours = now.getHours();
            const minutes = now.getMinutes();
            const seconds = now.getSeconds();
            const ampm = hours >= 12 ? ' PM' : ' AM';
            hours = hours % 12 || 12;

            const timeStr = hours + ':' + String(minutes).padStart(2, '0') + ampm;
            $('#current-date').text(dateStr);
            $('#current-time').text(timeStr);
        }

        setInterval(updateTime, 60000);
        updateTime();

        function fetchTokenData() {
            $.ajax({
                url: '/clinic/token',
                type: 'GET',
                success: function (response) {
                    $('#token-data').html(response);
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching token data: ", error);
                    $('#token-data').html('<p>Error fetching data.</p>');
                }
            });
        }

        setInterval(fetchTokenData, 60000);
        fetchTokenData();

        // Fullscreen toggle
        if (screenfull.isEnabled) {
            $('#fullscreen-btn').on('click', function () {
                screenfull.toggle();
                localStorage.setItem('fullscreen', screenfull.isFullscreen ? 'true' : 'false');
                updateFullscreenButton();
            });

            screenfull.on('change', updateFullscreenButton);

            function updateFullscreenButton() {
                const btn = $('#fullscreen-btn');
                btn.html(screenfull.isFullscreen
                    ? '<i title="Exit Fullscreen" class="fas fa-compress"></i>'
                    : '<i title="Fullscreen" class="fas fa-expand"></i>');
            }
        }

        // Call polling
        let currentCallingId = null;
        let lastCalledAt = null;

        function pollCallingStatus() {
            $.ajax({
                url: '/clinic/sl/get-calling-status',
                method: 'GET',
                success: function (response) {
                    if (response.success && response.calling) {
                        const calling = response.calling;

                        // Check if this is a new call or same ID but a newer call time
                        if (
                            calling.id !== currentCallingId ||
                            (calling.id === currentCallingId && calling.called_at !== lastCalledAt)
                        ) {
                            currentCallingId = calling.id;
                            lastCalledAt = calling.called_at;
                            showCallPopup(calling);
                        }
                    }
                },
                complete: function () {
                    setTimeout(pollCallingStatus, 1000);
                }
            });
        }

        function showCallPopup(callingData) {
            const slNo = callingData.sl_no;
            const roomNo = callingData.doctor_room;
            const patientName = callingData.patient_name;
            const doctorName = callingData.doctor_name;

            Swal.fire({
                title: '📣 CALLING PATIENT',
                html: `<div class="call-popup-container">
                        <div class="highlight-box call-serial">${slNo}</div>
                        <div class="call-details">
                            <p><strong>NAME:</strong> ${patientName}</p>
                            <p><strong>DOCTOR:</strong> ${doctorName}</p>
                            <div class="highlight-box call-room">ROOM: ${roomNo}</div>
                        </div>
                    </div>`,
                timer: 25000,
                timerProgressBar: false,
                width: '75%',
                background: '#f0f8ff',
                customClass: {
                    popup: 'swal-call-popup',
                    title: 'swal-call-title',
                    timerProgressBar: 'swal-progress-bar'
                },
                showConfirmButton: false,
                allowOutsideClick: false,
                willClose: () => {
                    $.ajax({
                        url: '/clinic/sl/update-call-status',
                        method: 'POST',
                        data: {
                            sl_id: callingData.id,
                            status: 'called'
                        },
                        success: function() {
                            console.log('Status updated to called');
                        },
                        error: function() {
                            console.error('Failed to update call status');
                        }
                    });
                }
            });
        }

        pollCallingStatus();

        setTimeout(function () {
            location.reload();
        }, 30 * 60 * 1000); // 30 minutes
    });
</script>

<style>
    /* Add this to your existing styles */
    body {
        overflow-x: hidden;
        transition: transform 0.3s ease;
    }
    
    .zoom-controls {
        transition: all 0.3s ease;
    }
    
    .zoom-controls:hover {
        background: white !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3) !important;
    }

    /* Rest of your existing styles remain the same... */
    .swal-call-popup {
        font-size: 2rem;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .swal-call-title {
        font-size: 4rem;
        margin-bottom: 1.5rem;
        color: #2c3e50;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }

    .call-popup-container {
        text-align: center;
        padding: 2rem;
    }

    .highlight-box {
        display: inline-block;
        padding: 1rem 3rem;
        border-radius: 15px;
        margin: 1.5rem 0;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .call-serial {
        font-size: 20rem;
        font-weight: bold;
        color: #ffffff;
        background-color: #e74c3c;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        animation: pulse 1.5s infinite;
    }

    .call-room {
        font-size: 8rem;
        font-weight: bold;
        color: #ffffff;
        background-color: #3498db;
        margin-top: 2rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
    }

    .call-details {
        font-size: 6rem;
        margin-top: 1rem;
        color: #34495e;
    }

    .call-details p {
        margin: 1.5rem 0;
        padding: 0.5rem;
        border-radius: 8px;
    }

    .swal-progress-bar {
        background: linear-gradient(90deg, #3498db, #2ecc71);
        height: 8px;
        border-radius: 0 0 20px 20px;
        display: none;
    }

    @keyframes pulse {
        0% { transform: scale(1); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        50% { transform: scale(1.03); box-shadow: 0 6px 12px rgba(0,0,0,0.3); }
        100% { transform: scale(1); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
    }
    .header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    }

    .title-container {
    display: flex;
    flex-direction: column;
    }

    .system-title {
    font-size: 8rem;
    font-weight: 600;
    color: #0056b3;
    margin: 0;
    line-height: 1;
    }

    .website-url {
    font-size: 3rem;
    color: #666;
    margin: 0.5rem 0 0 0;
    font-weight: 400;
    font-style: italic;
    letter-spacing: 0.2rem;
    display: none;
    }

    .clock-container {
    background-color: #474747;
    margin-top: 5px;
    padding: 1rem;
    border-radius: 10px;
    display: inline-block;
    text-align: center;
    box-shadow: 0 4px 10px rgba(32, 31, 31, 0.3);
    }

    .system-date {
        font-size: 2.5rem;
        color: #fff;
        font-weight: 400;
        margin: 0;
    }

    .system-time {
        font-size: 7rem;
        color: #fff;
        font-weight: 600;
        margin: 0;
        font-family: 'Courier New', Courier, monospace; /* Mimics the monospaced look of flip clocks */
    }
    /* General Styles */
    .bg-gradient-primary {
        background: linear-gradient(90deg, #007bff, #00c6ff);
    }

    .min-height-90vh {
        min-height: 90vh;
    }

    .shadow-sm {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .table {
        border-collapse: separate;
    }

    .table th, .table td {
        position: sticky;
        vertical-align: middle;
        border: none !important;
        padding: 5px !important;
    }
    /* Custom Header Row */
    .doctor-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(90deg, #343a40, #6c757d);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }

    .header-doctor-name,
    .header-current-served,
    .header-waiting-queue {
        flex: 1.1;
        display: flex;
        justify-content: center;
    }

    .header-doctor-name {
        flex: 0.7; /* Equal width */
    }

    .header-current-served {
        flex: 0.4; /* Equal width */
    }

    .header-box {
        width: 100%;
        background: rgba(255, 255, 255, 0.1);
    }

    .header-title {
        font-size: 2rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #ffffff;
        letter-spacing: 1px;
    }

    /* Styles for the token display */
    .token-display {
        display: flex;
        flex-direction: column;
        height: 70vh; /* full screen height */
        overflow: hidden; /* prevent scroll */
    }

    .doctor-row {
        display: flex;
        align-items: stretch;
        background: #fff;
        border-radius: 10px;
    }

    .doctor-name-col,
    .current-served,
    .waiting-queue {
        flex: 1.1; /* Equal width */
        padding: 10px;
        display: flex; /* Flex to let doctor-card fill full height */
    }

    .doctor-name-col {
        flex: 0.7; /* Equal width */
    }

    .current-served {
        flex: 0.4; /* Equal width */
    }

    .doctor-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        padding: 5px;
        border-radius: 10px;
        background: linear-gradient(145deg, #f8f9fa, #e9ecef);
        text-align: center;
    }

    .doctor-name-col .doctor-card {
        align-items: flex-start !important; /* Align content to the start (left) */
        text-align: left !important; /* Ensure text is left-aligned */
    }

    .doctor-name {
        font-size: 2.8rem;
        font-weight: 600;
        align-self: flex-start !important;
        text-align: left !important;
        color: #007bff;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .doctor-room {
        font-size: 3rem;
        font-weight: 600;
        color: #1f2f27;
        text-transform: uppercase;
    }

    /* Currently Being Served */
    .token-current {
        font-size: 6rem;
        font-weight: bold;
        color: #28a745;
        display: block; /* Changed from inline-block */
        text-align: center;
    }

    /* Waiting Queue */
    .waiting-tokens {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 15px;
    }

    .token-waiting {
        font-size: 6rem;
        font-weight: 600;
        color: #2d4739; /* Deep olive text */
        background-color: #d9f2e6; /* Soft mint/olive background */
        padding: 6px 15px;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        display: inline-block;
    }

    .waiting-count {
        font-size: 2rem;
        color: #343a40;
        margin-top: 10px;
        display: block;
    }

    .skipped-label {
        font-size: 1.5rem;
        color: #dc3545;
        font-weight: bold;
        margin-bottom: 5px;
        display: block;
    }

    .skipped-token-list {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .token-row {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        gap: 5px;
    }

    .token-skipped {
        font-size: 1.5rem;
        color: #dc3545;
        background-color: #f8d7da;
        padding: 6px 10px;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Alert Section */
    .alert-info {
        background: linear-gradient(90deg, #17a2b8, #00c6ff);
        border: none;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .alert-info p {
        font-size: 2rem;
        font-weight: 500;
        margin: 0;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .doctor-name {
            font-size: 2rem;
        }

        .token-current {
            font-size: 2rem;
            padding: 5px 10px;
        }

        .token-waiting {
            font-size: 2rem;
            padding: 4px 8px;
        }

        .token-skipped {
            font-size: 1rem;
            padding: 2px 4px;
        }

        .waiting-count {
            font-size: 1rem;
        }

        .alert-info p {
            font-size: 1.4rem;
        }
    }
</style>
@endsection