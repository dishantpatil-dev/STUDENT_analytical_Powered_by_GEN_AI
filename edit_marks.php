<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$id = (int)$_GET['id'];

$data = mysqli_query($conn,"
SELECT *
FROM marks
WHERE id='$id'
");

$row = mysqli_fetch_assoc($data);

if(isset($_POST['update']))
{
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

    mysqli_query($conn,"
    UPDATE marks
    SET
    marks='$total',
    percentage='$percentage',
    grade='$grade'
    WHERE id='$id'
    ");

    header("Location: marks.php");
    exit();
}
?>

<div class="main-content">

<?php include 'includes/navbar.php'; ?>

<div class="container-fluid">

<div class="card p-4">

<h3 class="mb-4">Edit Marks</h3>

<form method="POST">

<div class="mb-3">

<label>Total Marks</label>

<input
type="number"
name="total_marks"
class="form-control"
min="0"
max="100"
value="<?php echo $row['marks']; ?>"
required>

</div>

<button
name="update"
class="btn btn-success">

Update Marks

</button>

<a href="marks.php" class="btn btn-secondary">

Cancel

</a>

</form>

</div>

</div>

</div>

<?php include 'includes/footer.php'; ?>