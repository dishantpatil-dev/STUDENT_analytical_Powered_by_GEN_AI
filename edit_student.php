<?php
include 'includes/session.php';
include 'includes/db.php';

$id=$_GET['id'];

$data=mysqli_query($conn,"SELECT * FROM students WHERE id='$id'");
$row=mysqli_fetch_assoc($data);

if(isset($_POST['update']))
{

$student_name=$_POST['student_name'];
$roll=$_POST['roll_no'];
$class=$_POST['class'];
$gender=$_POST['gender'];
$phone=$_POST['phone'];
$email=$_POST['email'];

mysqli_query($conn,"UPDATE students SET
student_name='$student_name',
roll_no='$roll',
class='$class',
gender='$gender',
phone='$phone',
email='$email'
WHERE id='$id'");

header("Location: students.php");
exit();

}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">

<?php include 'includes/navbar.php'; ?>

<div class="container-fluid">

<div class="card p-4">

<h3 class="mb-4">Edit Student</h3>

<form method="POST">

<div class="mb-3">
<label>Name</label>
<input type="text" name="student_name" class="form-control" value="<?php echo htmlspecialchars($row['student_name']); ?>" required>
</div>

<div class="mb-3">
<label>Roll No</label>
<input type="text" name="roll_no" class="form-control" value="<?php echo $row['roll_no']; ?>" required>
</div>

<div class="mb-3">
<label>Class</label>
<input type="text" name="class" class="form-control" value="<?php echo $row['class']; ?>" required>
</div>

<div class="mb-3">
<label>Gender</label>
<input type="text" name="gender" class="form-control" value="<?php echo $row['gender']; ?>">
</div>

<div class="mb-3">
<label>Phone</label>
<input type="text" name="phone" class="form-control" value="<?php echo $row['phone']; ?>">
</div>

<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" value="<?php echo $row['email']; ?>">
</div>

<button class="btn btn-success" name="update">
Update Student
</button>

<a href="students.php" class="btn btn-secondary">
Cancel
</a>

</form>

</div>

</div>

</div>

<?php include 'includes/footer.php'; ?>