<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'includes/session.php';
include 'includes/db.php';

$student_id = (int) ($_GET['id'] ?? 0);

if ($student_id <= 0) {
    header("Location: attendance.php");
    exit();
}

/*
 * Fetch student information.
 * IMPORTANT: user_id ensures this student belongs
 * to the currently logged-in account.
 */
$stmt = mysqli_prepare(
    $conn,
    "SELECT name, roll_no, class
     FROM students
     WHERE id = ? AND user_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $student_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$student = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

mysqli_stmt_close($stmt);

if (!$student) {
    header("Location: attendance.php");
    exit();
}

/*
 * Fetch attendance records.
 * Since the student is already verified above,
 * these records belong to the logged-in account.
 */
$att_stmt = mysqli_prepare(
    $conn,
    "SELECT attendance_date, status
     FROM attendance
     WHERE student_id = ?
     ORDER BY attendance_date DESC"
);

mysqli_stmt_bind_param(
    $att_stmt,
    "i",
    $student_id
);

mysqli_stmt_execute($att_stmt);

$attendance_records = mysqli_stmt_get_result($att_stmt);

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">

    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid pt-4">

        <div class="card p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h2>
                    Attendance Report:
                    <?php echo htmlspecialchars($student['name']); ?>
                </h2>

                <a href="attendance.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Attendance
                </a>

            </div>

            <div class="mb-3">

                <p class="mb-1">
                    <strong>Roll No:</strong>
                    <?php echo htmlspecialchars($student['roll_no']); ?>
                </p>

                <p class="mb-0">
                    <strong>Class:</strong>
                    <?php echo htmlspecialchars($student['class']); ?>
                </p>

            </div>

            <div class="table-responsive">

                <table class="table table-bordered table-striped">

                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (mysqli_num_rows($attendance_records) > 0): ?>

                            <?php while ($log = mysqli_fetch_assoc($attendance_records)): ?>

                                <tr>

                                    <td>
                                        <?php echo htmlspecialchars($log['attendance_date']); ?>
                                    </td>

                                    <td>

                                        <span class="badge bg-<?php echo $log['status'] === 'Present' ? 'success' : 'danger'; ?>">

                                            <?php echo htmlspecialchars($log['status']); ?>

                                        </span>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="2" class="text-center text-muted">
                                    No attendance logs found for this student.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>