<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'includes/session.php';
include 'includes/db.php';

$selected_student_id = $_GET['student_id'] ?? '';

// Fetch all students for the dropdown
$students_list = mysqli_query($conn, "SELECT id, roll_no, name, class FROM students ORDER BY roll_no ASC");

// Variables for selected report
$student_info = null;
$attendance_summary = ['present' => 0, 'absent' => 0, 'total' => 0, 'percentage' => 0];
$student_marks = [];

if (!empty($selected_student_id)) {
    // 1. Fetch Student Details
    $s_stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
    mysqli_stmt_bind_param($s_stmt, "i", $selected_student_id);
    mysqli_stmt_execute($s_stmt);
    $student_info = mysqli_fetch_assoc(mysqli_stmt_get_result($s_stmt));
    mysqli_stmt_close($s_stmt);

    if ($student_info) {
        // 2. Calculate Attendance Percentage
        $att_stmt = mysqli_prepare($conn, "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent
            FROM attendance WHERE student_id = ?
        ");
        mysqli_stmt_bind_param($att_stmt, "i", $selected_student_id);
        mysqli_stmt_execute($att_stmt);
        $att_res = mysqli_fetch_assoc(mysqli_stmt_get_result($att_stmt));
        mysqli_stmt_close($att_stmt);

        $total_att = $att_res['total'] ?? 0;
        $present_att = $att_res['present'] ?? 0;
        $absent_att = $att_res['absent'] ?? 0;
        $percentage = $total_att > 0 ? round(($present_att / $total_att) * 100, 1) : 0;

        $attendance_summary = [
            'present' => $present_att,
            'absent' => $absent_att,
            'total' => $total_att,
            'percentage' => $percentage
        ];

        // 3. Fetch Subject Marks
        $marks_stmt = mysqli_prepare($conn, "
            SELECT s.subject_name, m.marks 
            FROM marks m 
            JOIN subjects s ON m.subject_id = s.id 
            WHERE m.student_id = ?
            ORDER BY s.subject_name ASC
        ");
        mysqli_stmt_bind_param($marks_stmt, "i", $selected_student_id);
        mysqli_stmt_execute($marks_stmt);
        $marks_res = mysqli_stmt_get_result($marks_stmt);
        while ($row = mysqli_fetch_assoc($marks_res)) {
            $student_marks[] = $row;
        }
        mysqli_stmt_close($marks_stmt);
    }
}

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
    }
    .custom-table td {
        background-color: #17233d !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
        vertical-align: middle;
    }
    @media print {
        body { background: #fff !important; color: #000 !important; }
        .sidebar, .navbar, .no-print { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; }
        .glass-card { border: none !important; box-shadow: none !important; background: #fff !important; color: #000 !important; }
        .custom-table td, .custom-table th { background: #fff !important; color: #000 !important; border: 1px solid #ccc !important; }
        .text-white, .text-white-50 { color: #000 !important; }
    }
</style>

<div class="main-content">
    <?php include 'includes/navbar.php'; ?>
    <div class="container-fluid page-container">
        
        <!-- Student Selection Box -->
        <div class="glass-card p-4 mb-4 no-print">
            <h2 class="text-white mb-3"><i class="bi bi-file-earmark-pdf me-2 text-info"></i>Generate Student Report Card</h2>
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label text-white-50 fw-bold">SELECT STUDENT</label>
                    <select name="student_id" class="form-control bg-dark text-white border-secondary" required>
                        <option value="">-- Select Student --</option>
                        <?php while ($s = mysqli_fetch_assoc($students_list)): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo $selected_student_id == $s['id'] ? 'selected' : ''; ?>>
                                [Roll: <?php echo htmlspecialchars($s['roll_no']); ?>] <?php echo htmlspecialchars($s['name']); ?> (Class: <?php echo htmlspecialchars($s['class']); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-info text-dark fw-bold w-100">
                        <i class="bi bi-search me-1"></i> Generate Report
                    </button>
                </div>
                <?php if ($student_info): ?>
                <div class="col-md-3">
                    <button type="button" onclick="window.print()" class="btn btn-success fw-bold w-100">
                        <i class="bi bi-printer me-1"></i> Print / Download PDF
                    </button>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Generated Report Output -->
        <?php if ($student_info): ?>
            <div class="glass-card p-4">
                <!-- Header Info -->
                <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-3 mb-4">
                    <div>
                        <h2 class="text-white mb-1"><?php echo htmlspecialchars($student_info['name']); ?></h2>
                        <p class="text-white-50 mb-0">Class: <strong><?php echo htmlspecialchars($student_info['class']); ?></strong> | Roll No: <strong><?php echo htmlspecialchars($student_info['roll_no']); ?></strong></p>
                    </div>
                    <div>
                        <span class="badge bg-info text-dark fs-6">Gender: <?php echo htmlspecialchars($student_info['gender']); ?></span>
                    </div>
                </div>

                <!-- Stats Overview -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-dark rounded border border-secondary text-center">
                            <h6 class="text-white-50 mb-1">Total Attendance</h6>
                            <h3 class="text-info fw-bold mb-0"><?php echo $attendance_summary['percentage']; ?>%</h3>
                            <small class="text-white-50">(<?php echo $attendance_summary['present']; ?> Present / <?php echo $attendance_summary['total']; ?> Days)</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-dark rounded border border-secondary text-center">
                            <h6 class="text-white-50 mb-1">Phone</h6>
                            <h5 class="text-white mb-0"><?php echo htmlspecialchars($student_info['phone'] ?: 'N/A'); ?></h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-dark rounded border border-secondary text-center">
                            <h6 class="text-white-50 mb-1">Email</h6>
                            <h5 class="text-white mb-0"><?php echo htmlspecialchars($student_info['email'] ?: 'N/A'); ?></h5>
                        </div>
                    </div>
                </div>

                <!-- Subject Marks Table -->
                <h4 class="text-white mb-3"><i class="bi bi-journal-text me-2 text-info"></i>Academic Scores</h4>
                <div class="table-responsive">
                    <table class="table custom-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Marks Obtained</th>
                                <th>Grade/Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($student_marks)): ?>
                                <?php foreach ($student_marks as $m): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($m['subject_name']); ?></td>
                                        <td><span class="badge bg-primary fs-6"><?php echo htmlspecialchars($m['marks']); ?></span></td>
                                        <td>
                                            <?php if ($m['marks'] >= 40): ?>
                                                <span class="badge bg-success">Pass</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Needs Improvement</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-white-50">No subject marks recorded for this student yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif (!empty($selected_student_id)): ?>
            <div class="alert alert-warning">Student record not found.</div>
        <?php else: ?>
            <div class="glass-card p-5 text-center">
                <i class="bi bi-file-earmark-person display-3 text-info mb-3"></i>
                <h4 class="text-white">Select a Student Above</h4>
                <p class="text-white-50">Choose a student from the dropdown list to generate and print their full report card.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include 'includes/footer.php'; ?>