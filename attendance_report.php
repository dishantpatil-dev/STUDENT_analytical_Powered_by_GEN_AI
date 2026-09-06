<?php
include 'includes/session.php';
include 'includes/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$id = (int)$_GET['id'];

$student = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT * FROM students WHERE id=$id")
);

$total = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) total FROM attendance WHERE student_id=$id")
)['total'];

$present = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) total FROM attendance WHERE student_id=$id AND status='Present'")
)['total'];

$absent = $total - $present;

$percentage = ($total>0) ? round(($present/$total)*100,2) : 0;

$history = mysqli_query($conn,"
SELECT attendance_date,status
FROM attendance
WHERE student_id=$id
ORDER BY attendance_date ASC
");

$months = mysqli_query($conn,"
SELECT DATE_FORMAT(attendance_date,'%b') month,
COUNT(*) working,
SUM(status='Present') present
FROM attendance
WHERE student_id=$id
GROUP BY MONTH(attendance_date)
");
?>

<div class="main-content">

<?php include 'includes/navbar.php'; ?>

<div class="container-fluid">

<div class="row mb-4 g-4">
<div class="row mb-4">

<div class="col-md-12">

<div class="alert alert-info d-flex justify-content-between align-items-center">

<div>

<h5 class="mb-1">
<i class="bi bi-person-check-fill"></i>
<?php echo htmlspecialchars($student['student_name']); ?>
</h5>

<small>
Attendance Report Overview
</small>

</div>

<div>

<?php
if($percentage>=90)
{
echo '<span class="badge bg-success fs-6">Excellent</span>';
}
elseif($percentage>=75)
{
echo '<span class="badge bg-warning text-dark fs-6">Good</span>';
}
else
{
echo '<span class="badge bg-danger fs-6">Needs Improvement</span>';
}
?>

</div>

</div>

</div>

</div>
<div class="col-md-3">
<div class="card stat-card total-card text-center p-4">
<i class="bi bi-calendar-check card-icon text-info"></i>
<h6 class="mt-3">Total Days</h6>
<h2><?php echo $total; ?></h2>
</div>
</div>

<div class="col-md-3">
<div class="card stat-card attendance-card text-center p-4">
<i class="bi bi-check-circle-fill card-icon text-success"></i>
<h6 class="mt-3">Present</h6>
<h2><?php echo $present; ?></h2>
</div>
</div>

<div class="col-md-3">
<div class="card stat-card marks-card text-center p-4">
<i class="bi bi-x-circle-fill card-icon text-danger"></i>
<h6 class="mt-3">Absent</h6>
<h2><?php echo $absent; ?></h2>
</div>
</div>

<div class="col-md-3">
<div class="card stat-card teacher-card text-center p-4">
<i class="bi bi-bar-chart-fill card-icon text-warning"></i>
<h6 class="mt-3">Attendance</h6>
<h2><?php echo $percentage; ?>%</h2>
</div>
</div>

</div>

<div class="card p-4 mb-4">

<h3><?php echo htmlspecialchars($student['student_name']); ?></h3>

<div class="row align-items-center">

<div class="col-md-2 text-center">

<?php
if(!empty($student['photo']))
{
?>
<img src="uploads/<?php echo $student['photo']; ?>"
style="width:130px;height:130px;border-radius:50%;object-fit:cover;border:4px solid #00d4ff;box-shadow:0 0 25px rgba(0,212,255,.6);">
<?php
}
else
{
?>
<img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
style="width:130px;height:130px;border-radius:50%;">
<?php
}
?>

</div>

<div class="col-md-10">

<h2 class="text-info fw-bold">
<?php echo htmlspecialchars($student['student_name']); ?>
</h2>

<hr>

<div class="row">

<div class="col-md-5">
<p><strong>Roll No :</strong> <?php echo $student['roll_no']; ?></p>
<p><strong>Class :</strong> <?php echo $student['class']; ?></p>
<p><strong>Gender :</strong> <?php echo $student['gender']; ?></p>
<p><strong>Phone :</strong> <?php echo $student['phone']; ?></p>
<p><strong>Email :</strong> <?php echo $student['email']; ?></p>
</div>

<div class="col-md-7">
<div class="d-flex justify-content-center align-items-center h-100">

<div class="progress-circle">

<svg width="130" height="130">

<circle
cx="65"
cy="65"
r="55"
stroke="#334155"
stroke-width="10"
fill="none"/>

<circle
cx="65"
cy="65"
r="55"
stroke="#00d4ff"
stroke-width="10"
fill="none"
stroke-linecap="round"
stroke-dasharray="345"
stroke-dashoffset="<?php echo 345-(345*$percentage/100); ?>"
transform="rotate(-90 65 65)"/>

</svg>

<div class="progress-text">
<?php echo $percentage; ?>%
</div>

</div>

</div>
</div>

</div>

</div>

</div>

<hr>

<div class="row mt-4">

<div class="col-md-8">
<canvas id="attendanceChart" height="110"></canvas>
</div>

<div class="col-md-4">
<canvas id="pieChart"></canvas>
</div>

</div>

</div>

<div class="card p-4">

<div class="d-flex justify-content-between align-items-center mb-3">
<div class="card p-4 mb-4">

<h4 class="text-info fw-bold mb-4">
<i class="bi bi-calendar3"></i>
Last 30 Days Attendance
</h4>

<div class="row g-2">

<?php

$calendar = mysqli_query($conn,"
SELECT attendance_date,status
FROM attendance
WHERE student_id=$id
ORDER BY attendance_date DESC
LIMIT 30
");

while($cal=mysqli_fetch_assoc($calendar))
{

$bg = ($cal['status']=="Present") ? "bg-success" : "bg-danger";

?>

<div class="col-md-2 col-3">

<div class="card <?php echo $bg; ?> text-center p-2">

<div class="fw-bold">
<?php echo date("d",strtotime($cal['attendance_date'])); ?>
</div>

<small>
<?php echo date("M",strtotime($cal['attendance_date'])); ?>
</small>

</div>

</div>

<?php } ?>

</div>

</div>
<h4 class="text-info fw-bold">
<i class="bi bi-calendar-check-fill"></i>
Attendance History
</h4>

<span class="badge bg-info fs-6">
<?php echo $percentage; ?>%
</span>

</div>

<table class="table table-dark">

<tr>

<th>Date</th>

<th>Status</th>

</tr>

<?php while($row=mysqli_fetch_assoc($history)){ ?>

<tr>

<td><?php echo $row['attendance_date']; ?></td>

<td>

<?php
if($row['status']=="Present")
echo "<span class='badge bg-success'>Present</span>";
else
echo "<span class='badge bg-danger'>Absent</span>";
?>

</td>

</tr>

<?php } ?>

</table>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const labels=[];

const values=[];

<?php

mysqli_data_seek($months,0);

while($m=mysqli_fetch_assoc($months))
{

$percent=round(($m['present']/$m['working'])*100,2);

echo "labels.push('".$m['month']."');";

echo "values.push(".$percent.");";

}

?>

new Chart(document.getElementById('attendanceChart'),{


type:'bar',

data:{

labels:labels,

datasets:[{

label:'Attendance %',

data:values,

backgroundColor:[
'#00d4ff',
'#22c55e',
'#3b82f6',
'#f59e0b',
'#8b5cf6',
'#06b6d4',
'#ef4444',
'#10b981',
'#6366f1',
'#f97316',
'#14b8a6',
'#ec4899'
],

borderRadius:10,
borderSkipped:false

}]

},

options:{

responsive:true,

plugins:{

legend:{
display:false
}

},

scales:{

y:{

beginAtZero:true,

max:100,

ticks:{
color:'#ffffff'
},

grid:{
color:'rgba(255,255,255,.08)'
}

},

x:{

ticks:{
color:'#ffffff'
},

grid:{
display:false
}

}

}

}

});

new Chart(document.getElementById('pieChart'),{

type:'doughnut',

data:{

labels:['Present','Absent'],

datasets:[{

data:[
<?php echo $present; ?>,
<?php echo $absent; ?>
],

backgroundColor:[
'#22c55e',
'#ef4444'
],

borderWidth:2

}]

},

options:{

responsive:true,

plugins:{

legend:{
position:'bottom',
labels:{
color:'#ffffff'
}
}

}

}

});

</script>

<?php include 'includes/footer.php'; ?>