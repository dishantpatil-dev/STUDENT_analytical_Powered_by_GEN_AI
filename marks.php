<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'includes/session.php';
include 'includes/db.php';

$message = "";

if (isset($_POST['save_marks'])) {

    $subject_id = (int) ($_POST['subject_id'] ?? 0);
    $marks_data = $_POST['marks'] ?? [];

    if ($subject_id > 0 && !empty($marks_data)) {

        foreach ($marks_data as $student_id => $score) {

            $student_id = (int) $student_id;

            if ($score === '') {
                continue;
            }

            $score = (float) $score;

            // Validate score
            if ($score < 0 || $score > 100) {
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

            // Check whether marks already exist
            $check_stmt = mysqli_prepare(
                $conn,
                "SELECT id
                 FROM marks
                 WHERE student_id = ? AND subject_id = ?"
            );

            mysqli_stmt_bind_param(
                $check_stmt,
                "ii",
                $student_id,
                $subject_id
            );

            mysqli_stmt_execute($check_stmt);
            $res = mysqli_stmt_get_result($check_stmt);

            if (mysqli_num_rows($res) > 0) {

                // Update existing marks
                $update_stmt = mysqli_prepare(
                    $conn,
                    "UPDATE marks
                     SET marks = ?
                     WHERE student_id = ? AND subject_id = ?"
                );

                mysqli_stmt_bind_param(
                    $update_stmt,
                    "dii",
                    $score,
                    $student_id,
                    $subject_id
                );

                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);

            } else {

                // Insert new marks
                $insert_stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO marks
                     (student_id, subject_id, marks)
                     VALUES (?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $insert_stmt,
                    "iid",
                    $student_id,
                    $subject_id,
                    $score
                );

                mysqli_stmt_execute($insert_stmt);
                mysqli_stmt_close($insert_stmt);
            }

            mysqli_stmt_close($check_stmt);
        }

        $message = "<div class='alert alert-success bg-success text-white border-0'>Marks saved successfully!</div>";

    } else {

        $message = "<div class='alert alert-danger bg-danger text-white border-0'>Please select a subject and enter valid marks.</div>";
    }
}

/*
 * Subjects are shared across the system.
 * Students are filtered by the logged-in account.
 */
$subjects = mysqli_query(
    $conn,
    "SELECT * FROM subjects ORDER BY subject_name ASC"
);

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
                <i class="bi bi-journal-bookmark me-2 text-info"></i>
                Academic Marks Portal
            </h2>

            <p class="text-white-50 mb-4">
                Click on a student's name to view their complete score report card.
            </p>

            <?php echo $message; ?>

            <form method="POST">

                <div class="mb-4 col-md-4">

                    <label class="form-label text-white-50 fw-bold">
                        SELECT SUBJECT
                    </label>

                    <select
                        name="subject_id"
                        class="form-control bg-dark text-white border-secondary"
                        required
                    >

                        <option value="">-- Choose Subject --</option>

                        <?php while ($sub = mysqli_fetch_assoc($subjects)): ?>

                            <option value="<?php echo $sub['id']; ?>">
                                <?php echo htmlspecialchars($sub['subject_name']); ?>
                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <div class="table-responsive">

                    <table class="table custom-table">

                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Student Name (Click for Marks Card)</th>
                                <th>Class</th>
                                <th>Score (0 - 100)</th>
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
                                                href="view_marks.php?id=<?php echo $row['id']; ?>"
                                                class="student-link"
                                            >
                                                <i class="bi bi-journal-text me-1"></i>
                                                <?php echo htmlspecialchars($row['name']); ?>
                                            </a>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($row['class']); ?>
                                        </td>

                                        <td>

                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                max="100"
                                                name="marks[<?php echo $row['id']; ?>]"
                                                class="form-control bg-dark text-white border-secondary"
                                                style="max-width: 150px;"
                                                placeholder="Score"
                                            >

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
                    name="save_marks"
                    class="btn btn-info text-dark fw-bold mt-3"
                >
                    <i class="bi bi-save me-1"></i>
                    Save Subject Marks
                </button>

            </form>

        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>