<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$query = "SELECT * FROM students WHERE user_id = $user_id ORDER BY id DESC";
$result = mysqli_query($conn, $query);

$message = "";
if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $message = "<div class='alert alert-success'>Student deleted successfully.</div>";
}
?>

<div class="main-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Students</h2>
            <a href="add_student.php" class="btn btn-primary">
                Add Student
            </a>
        </div>

        <?= $message ?>

        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Roll No</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Gender</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= htmlspecialchars($row['roll_no']) ?></td>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= htmlspecialchars($row['class']) ?></td>
                                        <td><?= htmlspecialchars($row['gender']) ?></td>
                                        <td><?= htmlspecialchars($row['phone']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td>
                                            <a href="edit_student.php?id=<?= $row['id'] ?>"
                                               class="btn btn-sm btn-warning">
                                                Edit
                                            </a>

                                            <a href="delete_student.php?id=<?= $row['id'] ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure?')">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">
                                        No students found.
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

<?php include 'includes/footer.php'; ?>