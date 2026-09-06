<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'includes/session.php';
include 'includes/db.php';

/*
|--------------------------------------------------------------------------
| Logged-in user's ID
|--------------------------------------------------------------------------
| session.php already creates $user_id.
*/
$user_id = (int) $user_id;


/*
|--------------------------------------------------------------------------
| 1. Total Students Count
|--------------------------------------------------------------------------
*/
$total_students = 0;

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM students
     WHERE user_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

$total_students = (int) ($row['total'] ?? 0);

mysqli_stmt_close($stmt);


/*
|--------------------------------------------------------------------------
| 2. Attendance Summary
|--------------------------------------------------------------------------
| Attendance is counted only for students belonging to this user.
*/
$total_present = 0;
$total_absent = 0;

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS present_count,
        SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS absent_count
     FROM attendance a
     INNER JOIN students st ON a.student_id = st.id
     WHERE st.user_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

$total_present = (int) ($row['present_count'] ?? 0);
$total_absent = (int) ($row['absent_count'] ?? 0);

mysqli_stmt_close($stmt);


/*
|--------------------------------------------------------------------------
| 3. Overall Average Marks
|--------------------------------------------------------------------------
| Marks are counted only for this user's students.
*/
$average_marks = 0;

$stmt = mysqli_prepare(
    $conn,
    "SELECT AVG(m.marks) AS avg_marks
     FROM marks m
     INNER JOIN students st ON m.student_id = st.id
     WHERE st.user_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

$average_marks = $row['avg_marks'] !== null
    ? round((float) $row['avg_marks'], 1)
    : 0;

mysqli_stmt_close($stmt);


/*
|--------------------------------------------------------------------------
| 4. Subject-wise Average Marks
|--------------------------------------------------------------------------
*/
$subject_names = [];
$subject_averages = [];

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        s.subject_name,
        AVG(m.marks) AS avg_score
     FROM marks m
     INNER JOIN subjects s ON m.subject_id = s.id
     INNER JOIN students st ON m.student_id = st.id
     WHERE st.user_id = ?
     GROUP BY s.id, s.subject_name
     ORDER BY s.subject_name ASC"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $subject_names[] = $row['subject_name'];
    $subject_averages[] = round((float) $row['avg_score'], 1);
}

mysqli_stmt_close($stmt);


/*
|--------------------------------------------------------------------------
| 5. Attendance Defaulters
|--------------------------------------------------------------------------
| Only this user's students are displayed.
| Defaulters have attendance below 60%.
*/
$defaulters = [];

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        st.id,
        st.roll_no,
        st.name,
        st.class,
        st.phone,
        COUNT(a.id) AS total_days,
        SUM(
            CASE
                WHEN a.status = 'Present' THEN 1
                ELSE 0
            END
        ) AS present_days
     FROM students st
     LEFT JOIN attendance a ON st.id = a.student_id
     WHERE st.user_id = ?
     GROUP BY
        st.id,
        st.roll_no,
        st.name,
        st.class,
        st.phone
     HAVING
        COUNT(a.id) > 0
        AND
        (
            SUM(
                CASE
                    WHEN a.status = 'Present' THEN 1
                    ELSE 0
                END
            ) / COUNT(a.id)
        ) * 100 < 60
     ORDER BY
        (
            SUM(
                CASE
                    WHEN a.status = 'Present' THEN 1
                    ELSE 0
                END
            ) / COUNT(a.id)
        ) ASC"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $defaulters[] = $row;
}

mysqli_stmt_close($stmt);

$defaulter_count = count($defaulters);


/*
|--------------------------------------------------------------------------
| Include page layout
|--------------------------------------------------------------------------
*/
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .analytics-container {
        padding-top: 1.5rem;
    }

    .glass-card {
        background: #111a2e;
        border: 1px solid rgba(0, 198, 255, 0.2);
        border-radius: 14px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .glass-card:hover {
        box-shadow: 0 12px 35px rgba(0, 198, 255, 0.15);
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
    }

    .chart-wrapper {
        position: relative;
        height: 320px;
        width: 100%;
    }

    .custom-table {
        color: #ffffff !important;
        background-color: transparent !important;
    }

    .custom-table th {
        background-color: #0b1329 !important;
        color: #ff4d4d !important;
        border-bottom: 2px solid rgba(255, 77, 77, 0.3) !important;
        border-top: none !important;
        white-space: nowrap;
    }

    .custom-table td {
        background-color: #17233d !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
        vertical-align: middle;
    }

    .student-link {
        color: #00c6ff !important;
        text-decoration: none;
        font-weight: 600;
    }

    .student-link:hover {
        color: #ff4d4d !important;
        text-decoration: underline;
    }
</style>

<div class="main-content">

    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid analytics-container mb-5">

        <h2 class="text-white mb-4">
            <i class="bi bi-graph-up-arrow me-2 text-info"></i>
            Analytics Dashboard
        </h2>

        <!-- Key Metrics -->
        <div class="row g-3 mb-4">

            <div class="col-md-3">
                <div class="glass-card p-4 text-center">
                    <p class="text-white-50 mb-1 fw-bold">
                        TOTAL STUDENTS
                    </p>

                    <div class="stat-number text-info">
                        <?php echo $total_students; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="glass-card p-4 text-center">
                    <p class="text-white-50 mb-1 fw-bold">
                        TOTAL PRESENT
                    </p>

                    <div class="stat-number text-success">
                        <?php echo $total_present; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="glass-card p-4 text-center">
                    <p class="text-white-50 mb-1 fw-bold">
                        TOTAL ABSENT
                    </p>

                    <div class="stat-number text-danger">
                        <?php echo $total_absent; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="glass-card p-4 text-center">
                    <p class="text-white-50 mb-1 fw-bold">
                        AVG MARKS SCORE
                    </p>

                    <div class="stat-number text-warning">
                        <?php echo $average_marks; ?>
                    </div>
                </div>
            </div>

        </div>


        <!-- Charts -->
        <div class="row g-3 mb-4">

            <div class="col-lg-8">
                <div class="glass-card p-4">

                    <h5 class="text-white mb-3">
                        <i class="bi bi-bar-chart-line me-2 text-info"></i>
                        Subject Average Performance
                    </h5>

                    <div class="chart-wrapper">
                        <canvas id="subjectChart"></canvas>
                    </div>

                </div>
            </div>


            <div class="col-lg-4">
                <div class="glass-card p-4">

                    <h5 class="text-white mb-3">
                        <i class="bi bi-pie-chart me-2 text-info"></i>
                        Attendance Distribution
                    </h5>

                    <div class="chart-wrapper">
                        <canvas id="attendanceChart"></canvas>
                    </div>

                </div>
            </div>

        </div>


        <!-- Defaulters List -->
        <div class="row">

            <div class="col-12">

                <div
                    class="glass-card p-4"
                    style="border-color: rgba(255, 77, 77, 0.3);"
                >

                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                        <h4 class="text-danger m-0">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Attendance Defaulter List (&lt; 60%)
                        </h4>

                        <span class="badge bg-danger fs-6">
                            <?php echo $defaulter_count; ?> Student(s) at Risk
                        </span>

                    </div>

                    <p class="text-white-50 mb-3">
                        Students listed below have an overall attendance below
                        the mandatory threshold of 60%.
                    </p>


                    <div class="table-responsive">

                        <table class="table custom-table">

                            <thead>
                                <tr>
                                    <th>Roll No</th>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Present / Total Days</th>
                                    <th>Attendance %</th>
                                    <th>Phone</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if ($defaulter_count > 0): ?>

                                    <?php foreach ($defaulters as $def): ?>

                                        <?php
                                        $total_days = (int) $def['total_days'];
                                        $present_days = (int) $def['present_days'];

                                        $attendance_percentage = $total_days > 0
                                            ? round(($present_days / $total_days) * 100, 1)
                                            : 0;
                                        ?>

                                        <tr>

                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    <?php echo htmlspecialchars($def['roll_no']); ?>
                                                </span>
                                            </td>

                                            <td>
                                                <a
                                                    href="view_attendance.php?id=<?php echo (int) $def['id']; ?>"
                                                    class="student-link"
                                                >
                                                    <i class="bi bi-person-exclamation me-1 text-danger"></i>

                                                    <?php echo htmlspecialchars($def['name']); ?>
                                                </a>
                                            </td>

                                            <td>
                                                <?php echo htmlspecialchars($def['class']); ?>
                                            </td>

                                            <td>
                                                <?php
                                                echo $present_days . " / " . $total_days;
                                                ?>
                                                Days
                                            </td>

                                            <td>
                                                <span class="badge bg-danger fs-6">
                                                    <?php echo $attendance_percentage; ?>%
                                                </span>
                                            </td>

                                            <td>
                                                <?php
                                                echo htmlspecialchars(
                                                    !empty($def['phone'])
                                                        ? $def['phone']
                                                        : 'N/A'
                                                );
                                                ?>
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>
                                        <td colspan="6" class="text-center text-success py-3">
                                            <i class="bi bi-check-circle-fill me-1"></i>
                                            No defaulters! All students currently have
                                            attendance of 60% or higher.
                                        </td>
                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<script>
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.08)';

    /*
    |--------------------------------------------------------------------------
    | Subject Average Bar Chart
    |--------------------------------------------------------------------------
    */
    const subjectLabels = <?php echo json_encode($subject_names); ?>;
    const subjectData = <?php echo json_encode($subject_averages); ?>;

    const subjectCanvas = document.getElementById('subjectChart');

    new Chart(subjectCanvas, {
        type: 'bar',

        data: {
            labels: subjectLabels,

            datasets: [
                {
                    label: 'Average Score',
                    data: subjectData,
                    backgroundColor: 'rgba(0, 198, 255, 0.6)',
                    borderColor: '#00c6ff',
                    borderWidth: 2,
                    borderRadius: 6
                }
            ]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            },

            plugins: {
                legend: {
                    display: true
                }
            }
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Attendance Doughnut Chart
    |--------------------------------------------------------------------------
    */
    const totalPresent = <?php echo $total_present; ?>;
    const totalAbsent = <?php echo $total_absent; ?>;

    const attendanceCanvas = document.getElementById('attendanceChart');

    new Chart(attendanceCanvas, {
        type: 'doughnut',

        data: {
            labels: ['Present', 'Absent'],

            datasets: [
                {
                    data: [totalPresent, totalAbsent],
                    backgroundColor: ['#198754', '#dc3545'],
                    borderWidth: 0
                }
            ]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>