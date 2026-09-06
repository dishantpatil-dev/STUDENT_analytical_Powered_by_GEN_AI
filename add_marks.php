<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';
if(isset($_POST['save']))
{
    $student_id = $_POST['student_id'];
    $total = $_POST['total_marks'];

    $percentage = $total;

    if($percentage>=90)
        $grade="A+";
    elseif($percentage>=80)
        $grade="A";
    elseif($percentage>=70)
        $grade="B";
    elseif($percentage>=60)
        $grade="C";
    elseif($percentage>=35)
        $grade="D";
    else
        $grade="F";

    $check = mysqli_query($conn,"
    SELECT id
    FROM marks
    WHERE student_id='$student_id'
    AND subject_id='1'
    ");

    if(mysqli_num_rows($check) > 0)
    {
        mysqli_query($conn,"
        UPDATE marks
        SET
        marks='$total',
        percentage='$percentage',
        grade='$grade'
        WHERE student_id='$student_id'
        AND subject_id='1'
        ");
    }
    else
    {
        mysqli_query($conn,"
        INSERT INTO marks(student_id,subject_id,marks,percentage,grade)
        VALUES('$student_id','1','$total','$percentage','$grade')
        ");
    }

    header("Location: marks.php");
    exit();
}
?>

<div class="main-content">

<?php include 'includes/navbar.php'; ?>

<div class="container-fluid">

<div class="card p-4">

<h3 class="mb-4">Add Marks</h3>

<form method="POST">

<div class="mb-3">

<label>Student</label>

<select name="student_id" class="form-control" required>

<option value="">Select Student</option>

<?php

$students=mysqli_query($conn,"SELECT * FROM students");

while($s=mysqli_fetch_assoc($students))
{
?>

<option value="<?php echo $s['id']; ?>">
<?php echo $s['student_name']; ?> (<?php echo $s['roll_no']; ?>)
</option>

<?php } ?>

</select>

</div>

<div class="mb-3">

<label>Total Marks (%)</label>

<input type="number"
name="total_marks"
class="form-control"
min="0"
max="100"
required>

</div>

<button name="save" class="btn btn-success">
Save Marks
</button>

</form>

</div>

</div>

</div>

<?php include 'includes/footer.php'; ?>