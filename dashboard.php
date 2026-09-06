<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'includes/session.php';
include 'includes/db.php';

// Fetch Counts for Top Stat Cards
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM students"))['total'] ?? 0;
$total_attendance = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance"))['total'] ?? 0;
$total_subjects = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM subjects"))['total'] ?? 0;
$total_teachers = 0; // Set to 0 or query teachers table if present

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<style>
    .dashboard-container {
        padding-top: 1.5rem;
    }
    
    .glass-card {
        background: #111a2e !important;
        border: 1px solid rgba(0, 198, 255, 0.2) !important;
        border-radius: 14px !important;
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5) !important;
        color: #ffffff !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .glass-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 35px 0 rgba(0, 198, 255, 0.2) !important;
    }

    /* Top glowing accents on metric cards */
    .card-accent-info { border-top: 4px solid #00c6ff !important; }
    .card-accent-success { border-top: 4px solid #28a745 !important; }
    .card-accent-warning { border-top: 4px solid #ffc107 !important; }
    .card-accent-primary { border-top: 4px solid #0d6efd !important; }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #ffffff !important;
    }

    .chart-wrapper {
        position: relative;
        height: 320px;
        width: 100%;
    }
</style>

<div class="main-content">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid dashboard-container">
        
        <!-- Top Metrics Stat Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="glass-card card-accent-info p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-white-50 fw-bold">Total Students</span>
                        <i class="bi bi-people-fill text-info fs-4"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_students; ?></div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="glass-card card-accent-success p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-white-50 fw-bold">Attendance Records</span>
                        <i class="bi bi-calendar-check-fill text-success fs-4"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_attendance; ?></div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="glass-card card-accent-warning p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-white-50 fw-bold">Subjects</span>
                        <i class="bi bi-journal-bookmark-fill text-warning fs-4"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_subjects; ?></div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="glass-card card-accent-primary p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-white-50 fw-bold">Teachers</span>
                        <i class="bi bi-person-badge-fill text-primary fs-4"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_teachers; ?></div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="glass-card p-4">
                    <h5 class="text-white mb-3"><i class="bi bi-pie-chart-fill me-2 text-info"></i>Student Distribution</h5>
                    <div class="chart-wrapper">
                        <canvas id="distributionChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="glass-card p-4">
                    <h5 class="text-white mb-3"><i class="bi bi-bar-chart-fill me-2 text-info"></i>System Overview</h5>
                    <div class="chart-wrapper">
                        <canvas id="overviewChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Global dark theme defaults for Chart.js
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.08)';

    // 1. Doughnut Distribution Chart
    const distCtx = document.getElementById('distributionChart').getContext('2d');
    new Chart(distCtx, {
        type: 'doughnut',
        data: {
            labels: ['Students', 'Teachers', 'Subjects'],
            datasets: [{
                data: [<?php echo $total_students; ?>, <?php echo $total_teachers; ?>, <?php echo $total_subjects; ?>],
                backgroundColor: ['#00c6ff', '#28a745', '#ffc107'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            }
        }
    });

    // 2. Bar Overview Chart
    const overviewCtx = document.getElementById('overviewChart').getContext('2d');
    new Chart(overviewCtx, {
        type: 'bar',
        data: {
            labels: ['Students', 'Teachers', 'Subjects', 'Attendance'],
            datasets: [{
                label: 'System Totals',
                data: [<?php echo $total_students; ?>, <?php echo $total_teachers; ?>, <?php echo $total_subjects; ?>, <?php echo $total_attendance; ?>],
                backgroundColor: 'rgba(0, 198, 255, 0.6)',
                borderColor: '#00c6ff',
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>