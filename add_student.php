<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'includes/session.php';
include 'includes/db.php';

$message = "";
$messageType = "";

if (isset($_POST['save'])) {

    // Logged-in account ID
    $user_id = (int) $_SESSION['user_id'];

    // Get form values
    $name    = trim($_POST['student_name'] ?? '');
    $roll    = trim($_POST['roll_no'] ?? '');
    $class   = trim($_POST['class'] ?? '');
    $gender  = trim($_POST['gender'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $photo = "";

    // Validate required fields
    if ($name === '' || $roll === '' || $class === '') {

        $message = "Please fill all required fields.";
        $messageType = "danger";

    } else {

        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {

            $uploadDir = __DIR__ . '/uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = $_FILES['photo']['name'];
            $tmpName = $_FILES['photo']['tmp_name'];

            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($extension, $allowedExtensions)) {

                $message = "Only JPG, JPEG, PNG, GIF, and WEBP images are allowed.";
                $messageType = "danger";

            } else {

                $fileNameClean = preg_replace(
                    "/[^a-zA-Z0-9\._-]/",
                    "_",
                    $fileName
                );

                $photo = time() . '_' . $fileNameClean;

                $targetFilePath = $uploadDir . $photo;

                if (!move_uploaded_file($tmpName, $targetFilePath)) {

                    $message = "Error uploading photo.";
                    $messageType = "danger";

                } else {

                    // Photo uploaded successfully
                }
            }
        }

        // Continue only if there is no validation error
        if ($message === "") {

            /*
             * IMPORTANT:
             * user_id is saved with the student.
             * This makes the student belong to the logged-in account.
             */

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO students
                (user_id, name, roll_no, class, gender, phone, email, address, photo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            if (!$stmt) {

                $message = "Database Error: " . mysqli_error($conn);
                $messageType = "danger";

            } else {

                mysqli_stmt_bind_param(
                    $stmt,
                    "issssssss",
                    $user_id,
                    $name,
                    $roll,
                    $class,
                    $gender,
                    $phone,
                    $email,
                    $address,
                    $photo
                );

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    header("Location: students.php");
                    exit();

                } else {

                    // Duplicate roll number
                    if (mysqli_errno($conn) == 1062) {

                        $message = "This roll number already exists in your account. Please use another roll number.";

                    } else {

                        $message = "Error adding student: " . mysqli_error($conn);
                    }

                    $messageType = "danger";

                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">

    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid">

        <div class="card p-4">

            <h2 class="mb-4">Add Student</h2>

            <?php if ($message !== ""): ?>

                <div class="alert alert-<?= $messageType ?>">
                    <?= htmlspecialchars($message) ?>
                </div>

            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>Name</label>
                        <input
                            type="text"
                            name="student_name"
                            class="form-control"
                            value="<?= htmlspecialchars($_POST['student_name'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Roll No</label>
                        <input
                            type="text"
                            name="roll_no"
                            class="form-control"
                            value="<?= htmlspecialchars($_POST['roll_no'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Class</label>
                        <input
                            type="text"
                            name="class"
                            class="form-control"
                            value="<?= htmlspecialchars($_POST['class'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Gender</label>

                        <select name="gender" class="form-control">

                            <option value="Male"
                                <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>
                                Male
                            </option>

                            <option value="Female"
                                <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>
                                Female
                            </option>

                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Phone</label>
                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                            value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        >
                    </div>

                    <div class="col-12 mb-3">
                        <label>Address</label>
                        <textarea
                            name="address"
                            class="form-control"
                        ><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                    </div>

                    <div class="col-12 mb-3">
                        <label>Student Photo</label>
                        <input
                            type="file"
                            name="photo"
                            class="form-control"
                            accept="image/*"
                        >
                    </div>

                    <div class="col-12">

                        <button
                            type="submit"
                            class="btn btn-info"
                            name="save"
                        >
                            <i class="bi bi-check-circle-fill"></i>
                            Save Student
                        </button>

                        <a
                            href="students.php"
                            class="btn btn-secondary"
                        >
                            Back
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>