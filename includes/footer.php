<!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Mobile Sidebar Toggle Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toggleBtn = document.getElementById("sidebarToggle");
            const sidebar = document.getElementById("mobileSidebar");

            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener("click", function (e) {
                    e.stopPropagation();
                    sidebar.classList.toggle("show-mobile");
                });

                // Close sidebar when clicking outside on mobile
                document.addEventListener("click", function (e) {
                    if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                        sidebar.classList.remove("show-mobile");
                    }
                });
            }
        });
    </script>
</body>
</html>