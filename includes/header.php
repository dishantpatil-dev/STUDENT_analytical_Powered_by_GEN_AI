<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Student Analytics System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background-color: #0b1329;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        /* Main Content Layout */
        .main-content {
            margin-left: 240px;
            padding: 20px;
            padding-top: 80px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* Responsive Mobile Styles */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
                padding: 12px !important;
                padding-top: 75px !important;
            }
            .glass-card {
                padding: 15px !important;
                border-radius: 10px !important;
                margin-bottom: 15px !important;
            }
            .stat-number {
                font-size: 1.8rem !important;
            }
            .table-responsive {
                border: 0;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .custom-table th, 
            .custom-table td {
                white-space: nowrap;
                padding: 8px 10px !important;
                font-size: 13px !important;
            }
            .chart-wrapper {
                height: 250px !important;
            }
        }
    </style>
</head>
<body>