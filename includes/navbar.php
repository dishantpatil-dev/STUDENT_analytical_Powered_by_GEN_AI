<nav class="navbar navbar-expand-lg fixed-top px-3" style="background: #111a2e; border-bottom: 1px solid rgba(0, 198, 255, 0.2); z-index: 1030;">
    <div class="container-fluid">
        <!-- Mobile Sidebar Toggle Button -->
        <button class="btn btn-outline-info d-md-none me-2" type="button" id="sidebarToggle">
            <i class="bi bi-list fs-4"></i>
        </button>

        <a class="navbar-brand fw-bold text-white fs-5" href="dashboard.php">
            Student Analytics System
        </a>

        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="text-white-50 d-none d-sm-inline">
                Welcome, <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Administrator'); ?></strong>
            </span>
            <a href="logout.php" class="btn btn-danger btn-sm fw-bold">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>
</nav>