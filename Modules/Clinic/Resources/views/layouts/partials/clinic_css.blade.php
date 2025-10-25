<style type="text/css">
    .mt-2 {
        margin-top: 5px;
    }

    .container-fluid {
        margin: 10px;
    }

    .collapse-header {
        cursor: pointer;
        font-weight: bold;
        background-color: #f8f9fa;
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }

    .search_btn {
        background: #5E6CDF;
        color: white;
    }

    .search_btn:hover {
        background: #5E6CDF;
        color: white;
    }

    .reset_btn {
        background: #EE2C64;
        color: white;
    }

    .reset_btn:hover {
        background: #EE2C64;
        color: white;
    }

    .download_btn {
        background: #FD9704;
        color: white;
    }

    .download_btn:hover {
        background: #FD9704;
        color: white;
    }

    .doctor-heading {
        background: #D7D7FB;
        margin: 10px;
        padding: 5px;
    }

    .custom-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 99%;
    }

    .make_app {
        background: #5E6CDF;
        color: white;
        margin-right: 10px;
    }

    .make_app:hover {
        color: white;
    }




    .custom-row2 {
        display: flex;
        justify-content: space-between;
        width: 100%;
    }

    .text-left {
        flex: 1;
        /* Allow left text to grow */
    }

    .separator {
        border-bottom: 1px solid #e7e2e2;
        /* Adjust the color as needed */
        margin: 3px 0;
        /* Adjust spacing */
    }

    .table thead th {
        background-color: #f0f0f0;
        /* Light gray color */
        color: #333;
        /* Darker text color for contrast */
    }

    .mt-1 {
        margin-top: 5px;
    }

    .custom-shadow {
        /* Adjust the values to get the desired shadow effect */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2), 0 6px 20px rgba(0, 0, 0, 0.19);
        padding: 10px;
    }

    .text-right {
        text-align: right;
        line-height: 20px;
    }

    .btn-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        /* Optional: centers vertically */
        width: 100%;
        background-color: #E8E8EA;
        padding: 4px;
    }

    .doctor_details {
        margin: 5px;
    }

    .report-heading {
        background: #D7D7FB;
        margin: 10px;
        padding: 10px;
    }

    .prescription-middle-widget {
        line-height: 5px;
        min-height: 60px;
    }

    .prescription-middle-widget2 {
        min-height: 456px;
    }

    .prescription-left-widget {
        min-height: 60px !important;
    }

    .prescription-left-widget2 {
        min-height: 142px !important;
    }

    .prescription-left-widget3 {
        min-height: 100px !important;
    }

    .prescription-right-widget {
        min-height: 60px !important;
    }

    .m-1 {
        margin: 1px;
    }

    .mr-1 {
        margin-right: 2px;
    }

    th,
    td {
        border: 1px solid #ddd;
    }

    td:hover {
        background-color: #f1f1f1;
    }

    .p-1 {
        padding-left: 10px;
        padding-right: 10px;
        padding-top: 5px;
        padding-bottom: 5px;
    }

    #testSection {
        min-height: 100px;
        padding-left: 10px;
        padding-right: 10px !important;
    }

    .add_new_dosage_btn {
        width: 16px;
        /* Button width */
        height: 16px;
        /* Button height */
        background-color: #000000;
        /* Background color */
        color: white;
        /* Text color */
        font-size: 12px;
        /* Font size adjusted for 20px button */
        border-radius: 50%;
        /* Circular button */
        line-height: 16px;
        /* Line-height equal to height for vertical centering */
        text-align: center;
        /* Horizontal centering */
        cursor: pointer;
        /* Pointer cursor on hover */
        transition: background-color 0.3s ease, transform 0.2s ease;
    }
    .medicine_section{
        min-height: 400px;
    }
    .star{
        color: red;
    }
        /* Wrapper for switch and text */
        .toggle-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Toggle switch wrapper */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        /* Hide checkbox */
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* Slider background */
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 26px;
        }

        /* Switch knob */
        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        /* When checked: green bg */
        .toggle-switch input:checked+.slider {
            background-color: #28a745;
        }

        /* When checked: move knob */
        .toggle-switch input:checked+.slider:before {
            transform: translateX(24px);
        }

        /* Status text styling */
        #doctor_status_text {
            font-size: 16px;
            color: #333;
            font-weight: 500;
            line-height: 1;
        }
        .therapist-treatment-plan-widget{
            min-height: 300px;
        }
    </style>