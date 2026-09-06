<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'includes/session.php';
include 'includes/db.php';

if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']);

    // 1. Fetch photo filename to delete image from uploads folder
    $stmt = mysqli_prepare($conn, "SELECT photo FROM students WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($res)) {
        if (!empty($row['photo'])) {
            $file_path = __DIR__ . '/uploads/' . $row['photo'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
    mysqli_stmt_close($stmt);

    // 2. Delete student record from database
    $delete_stmt = mysqli_prepare($conn, "DELETE FROM students WHERE id = ?");
    mysqli_stmt_bind_param($delete_stmt, "i", $student_id);
    mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);
}

header("Location: students.php?msg=deleted");
exit();
?>