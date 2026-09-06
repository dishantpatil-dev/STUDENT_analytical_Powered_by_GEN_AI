<style>
    .sidebar {
        width: 240px;
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        background: #0b1329;
        border-right: 1px solid rgba(0, 198, 255, 0.15);
        z-index: 1020;
        padding-top: 80px;
        transition: all 0.3s ease;
    }

    .sidebar .nav-link {
        color: #94a3b8;
        padding: 12px 20px;
        font-size: 15px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.2s ease;
    }

    .sidebar .nav-link:hover, 
    .sidebar .nav-link.active {
        color: #00c6ff;
        background: rgba(0, 198, 255, 0.1);
        border-left: 4px solid #00c6ff;
    }

    /* Mobile off-canvas behavior */
    @media (max-width: 768px) {
        .sidebar {
            left: -240px;
        }
        .sidebar.show-mobile {
            left: 0;
            box-shadow: 10px 0 30px rgba(0,0,0,0.8);
        }
    }
</style>

<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar" id="mobileSidebar">
    <div class="px-3 mb-3 text-info fw-bold d-flex align-items-center gap-2">
        <i class="bi bi-mortarboard-fill fs-4"></i>
        <span class="fs-5">SAS</span>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $currentPage == 'students.php' ? 'active' : ''; ?>" href="students.php">
                <i class="bi bi-people-fill"></i> Students
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $currentPage == 'attendance.php' ? 'active' : ''; ?>" href="attendance.php">
                <i class="bi bi-calendar-check-fill"></i> Attendance
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $currentPage == 'marks.php' ? 'active' : ''; ?>" href="marks.php">
                <i class="bi bi-bar-chart-fill"></i> Marks
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $currentPage == 'analytics.php' ? 'active' : ''; ?>" href="analytics.php">
                <i class="bi bi-pie-chart-fill"></i> Analytics
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $currentPage == 'ai_assistant.php' ? 'active' : ''; ?>" href="ai_assistant.php">
                <i class="bi bi-robot"></i> AI Assistant
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $currentPage == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                <i class="bi bi-file-earmark-text-fill"></i> Reports
            </a>
        </li>
    </ul>
</div>