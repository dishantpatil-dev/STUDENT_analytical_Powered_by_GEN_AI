<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'includes/session.php';
include 'includes/db.php';

$message = "";

$attendance_date = $_POST['attendance_date'] ?? date('Y-m-d');

if (isset($_POST['save_attendance'])) {

    $attendance_date = $_POST['attendance_date'] ?? '';
    $attendance = $_POST['attendance'] ?? [];

    if (!empty($attendance_date) && !empty($attendance)) {

        foreach ($attendance as $student_id => $status) {

            $student_id = (int) $student_id;

            // Allow only valid attendance values
            if ($status !== 'Present' && $status !== 'Absent') {
                continue;
            }

            /*
             * IMPORTANT:
             * Verify that this student belongs to the logged-in account.
             */
            $owner_stmt = mysqli_prepare(
                $conn,
                "SELECT id
                 FROM students
                 WHERE id = ? AND user_id = ?"
            );

            mysqli_stmt_bind_param(
                $owner_stmt,
                "ii",
                $student_id,
                $user_id
            );

            mysqli_stmt_execute($owner_stmt);
            $owner_result = mysqli_stmt_get_result($owner_stmt);

            if (mysqli_num_rows($owner_result) === 0) {
                mysqli_stmt_close($owner_stmt);
                continue;
            }

            mysqli_stmt_close($owner_stmt);

            // Check whether attendance already exists
            $check_stmt = mysqli_prepare(
                $conn,
                "SELECT id
                 FROM attendance
                 WHERE student_id = ? AND attendance_date = ?"
            );

            mysqli_stmt_bind_param(
                $check_stmt,
                "is",
                $student_id,
                $attendance_date
            );

            mysqli_stmt_execute($check_stmt);
            $res = mysqli_stmt_get_result($check_stmt);

            if (mysqli_num_rows($res) > 0) {

                // Update existing attendance
                $update_stmt = mysqli_prepare(
                    $conn,
                    "UPDATE attendance
                     SET status = ?
                     WHERE student_id = ? AND attendance_date = ?"
                );

                mysqli_stmt_bind_param(
                    $update_stmt,
                    "sis",
                    $status,
                    $student_id,
                    $attendance_date
                );

                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);

            } else {

                // Insert new attendance
                $insert_stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO attendance
                     (student_id, attendance_date, status)
                     VALUES (?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $insert_stmt,
                    "iss",
                    $student_id,
                    $attendance_date,
                    $status
                );

                mysqli_stmt_execute($insert_stmt);
                mysqli_stmt_close($insert_stmt);
            }

            mysqli_stmt_close($check_stmt);
        }

        $message = "<div class='alert alert-success bg-success text-white border-0'>Attendance record updated successfully!</div>";

    } else {

        $message = "<div class='alert alert-danger bg-danger text-white border-0'>Please select a date and mark attendance.</div>";
    }
}

/*
 * IMPORTANT:
 * Only students belonging to the logged-in account.
 */
$students_stmt = mysqli_prepare(
    $conn,
    "SELECT id, roll_no, name, class
     FROM students
     WHERE user_id = ?
     ORDER BY roll_no ASC"
);

mysqli_stmt_bind_param($students_stmt, "i", $user_id);
mysqli_stmt_execute($students_stmt);
$students = mysqli_stmt_get_result($students_stmt);

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<style>
    .page-container { padding-top: 1.5rem; }

    .glass-card {
        background: #111a2e;
        border: 1px solid rgba(0, 198, 255, 0.2);
        border-radius: 14px;
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
    }

    .custom-table {
        color: #ffffff !important;
        background-color: transparent !important;
    }

    .custom-table th {
        background-color: #0b1329 !important;
        color: #00c6ff !important;
        border-bottom: 2px solid rgba(0, 198, 255, 0.3) !important;
        border-top: none !important;
    }

    .custom-table td {
        background-color: #17233d !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
        vertical-align: middle;
    }

    .custom-table tr:nth-child(even) td {
        background-color: #1a2846 !important;
    }

    .student-link {
        color: #00c6ff !important;
        text-decoration: none;
        font-weight: 600;
    }

    .student-link:hover {
        color: #38ef7d !important;
        text-decoration: underline;
    }
</style>

<div class="main-content">

    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid page-container">

        <div class="glass-card p-4">

            <h2 class="text-white mb-1">
                <i class="bi bi-calendar-check me-2 text-info"></i>
                Attendance Tracker
            </h2>

            <p class="text-white-50 mb-4">
                Click on a student's name to view their complete attendance history.
            </p>

            <?php echo $message; ?>

            <form method="POST">

                <div class="mb-4 col-md-4">

                    <label class="form-label text-white-50 fw-bold">
                        SELECT DATE
                    </label>

                    <input
                        type="date"
                        name="attendance_date"
                        class="form-control bg-dark text-white border-secondary"
                        value="<?php echo htmlspecialchars($attendance_date); ?>"
                        required
                    >

                </div>

                <div class="table-responsive">

                    <table class="table custom-table">

                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Student Name (Click for History)</th>
                                <th>Class</th>
                                <th>Attendance Option</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (mysqli_num_rows($students) > 0): ?>

                                <?php while ($row = mysqli_fetch_assoc($students)): ?>

                                    <tr>

                                        <td>
                                            <span class="badge bg-primary fs-6">
                                                <?php echo htmlspecialchars($row['roll_no']); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <a
                                                href="view_attendance.php?id=<?php echo $row['id']; ?>"
                                                class="student-link"
                                            >
                                                <i class="bi bi-person-circle me-1"></i>
                                                <?php echo htmlspecialchars($row['name']); ?>
                                            </a>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($row['class']); ?>
                                        </td>

                                        <td>

                                            <div class="form-check form-check-inline">

                                                <input
                                                    class="form-check-input"
                                                    type="radio"
                                                    name="attendance[<?php echo $row['id']; ?>]"
                                                    value="Present"
                                                    checked
                                                >

                                                <label class="form-check-label text-success fw-bold">
                                                    Present
                                                </label>

                                            </div>

                                            <div class="form-check form-check-inline">

                                                <input
                                                    class="form-check-input"
                                                    type="radio"
                                                    name="attendance[<?php echo $row['id']; ?>]"
                                                    value="Absent"
                                                >

                                                <label class="form-check-label text-danger fw-bold">
                                                    Absent
                                                </label>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="4" class="text-center text-white-50">
                                        No students registered yet.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

                <button
                    type="submit"
                    name="save_attendance"
                    class="btn btn-info text-dark fw-bold mt-3"
                >
                    <i class="bi bi-check2-circle me-1"></i>
                    Save Daily Attendance
                </button>

            </form>

        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>