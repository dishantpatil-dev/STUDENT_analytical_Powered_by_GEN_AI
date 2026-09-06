<?php
include 'includes/db.php';

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=Student_Report.csv");
header("Pragma: no-cache");
header("Expires: 0");

$output = fopen("php://output", "w");

// Column headings
fputcsv($output, array(
    "Roll No",
    "Name",
    "Class",
    "Marks",
    "Percentage",
    "Grade"
));

$result = mysqli_query($conn,"
SELECT
students.roll_no,
students.student_name,
students.class,
COALESCE(marks.marks,0) AS marks,
COALESCE(marks.percentage,0) AS percentage,
COALESCE(marks.grade,'N/A') AS grade
FROM students
LEFT JOIN marks
ON students.id = marks.student_id
ORDER BY students.roll_no
");

while($row = mysqli_fetch_assoc($result))
{
    fputcsv($output, $row);
}

fclose($output);
exit();
?>