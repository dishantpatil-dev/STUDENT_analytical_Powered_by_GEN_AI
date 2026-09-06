<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'includes/session.php';
include 'includes/db.php';

$student_id = (int) ($_GET['id'] ?? 0);

if ($student_id <= 0) {
    header("Location: marks.php");
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
    header("Location: marks.php");
    exit();
}

/*
 * Fetch subject marks.
 * Since the student is already verified above,
 * these marks belong to the logged-in account.
 */
$marks_stmt = mysqli_prepare(
    $conn,
    "SELECT s.subject_name, m.marks
     FROM marks m
     JOIN subjects s ON m.subject_id = s.id
     WHERE m.student_id = ?
     ORDER BY s.subject_name ASC"
);

mysqli_stmt_bind_param(
    $marks_stmt,
    "i",
    $student_id
);

mysqli_stmt_execute($marks_stmt);

$marks_records = mysqli_stmt_get_result($marks_stmt);

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">

    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid pt-4">

        <div class="card p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h2>
                    Marks Report:
                    <?php echo htmlspecialchars($student['name']); ?>
                </h2>

                <a href="marks.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Marks
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
                            <th>Subject</th>
                            <th>Marks Obtained</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (mysqli_num_rows($marks_records) > 0): ?>

                            <?php while ($log = mysqli_fetch_assoc($marks_records)): ?>

                                <tr>

                                    <td>
                                        <?php echo htmlspecialchars($log['subject_name']); ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-info text-dark font-size-14">
                                            <?php echo htmlspecialchars($log['marks']); ?>
                                        </span>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="2" class="text-center text-muted">
                                    No marks recorded for this student yet.
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