<?php
include 'includes/session.php';
include 'includes/db.php';

$id = (int)$_GET['id'];

mysqli_query($conn,"
DELETE FROM marks
WHERE id='$id'
");

header("Location: marks.php");
exit();
?>