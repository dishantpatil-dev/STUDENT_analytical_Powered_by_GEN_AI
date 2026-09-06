<?php
header('Content-Type: application/json; charset=utf-8');
include 'includes/session.php';
include 'includes/db.php';
include 'includes/ai_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['reply' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$prompt = trim($input['prompt'] ?? '');
if ($prompt === '') {
    echo json_encode(['reply' => 'Please enter a question.']);
    exit;
}
if (strlen($prompt) > 1000) {
    echo json_encode(['reply' => 'Please keep your question under 1000 characters.']);
    exit;
}

function column_exists($conn, $table, $column) {
    $table = mysqli_real_escape_string($conn, $table);
    $column = mysqli_real_escape_string($conn, $column);
    $r = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $r && mysqli_num_rows($r) > 0;
}

function scalar_query($conn, $sql, $key) {
    $r = mysqli_query($conn, $sql);
    if (!$r) return 0;
    $row = mysqli_fetch_assoc($r);
    return $row[$key] ?? 0;
}

$nameCol = column_exists($conn, 'students', 'student_name') ? 'student_name' : 'name';
$marksHasPercentage = column_exists($conn, 'marks', 'percentage');
$marksHasGrade = column_exists($conn, 'marks', 'grade');

$totalStudents = scalar_query($conn, "SELECT COUNT(*) AS n FROM students", 'n');
$totalTeachers = scalar_query($conn, "SELECT COUNT(*) AS n FROM teachers", 'n');
$totalSubjects = scalar_query($conn, "SELECT COUNT(*) AS n FROM subjects", 'n');
$totalAttendance = scalar_query($conn, "SELECT COUNT(*) AS n FROM attendance", 'n');
$present = scalar_query($conn, "SELECT COUNT(*) AS n FROM attendance WHERE status='Present'", 'n');
$absent = scalar_query($conn, "SELECT COUNT(*) AS n FROM attendance WHERE status='Absent'", 'n');
$avgMarks = scalar_query($conn, "SELECT COALESCE(AVG(marks),0) AS n FROM marks", 'n');

$students = [];
$sql = "SELECT s.id, s.`$nameCol` AS student_name, s.roll_no, s.class,
        COALESCE(m.avg_marks,0) AS avg_marks,
        COALESCE(a.total_attendance,0) AS total_attendance,
        COALESCE(a.present_count,0) AS present_count
        FROM students s
        LEFT JOIN (SELECT student_id, AVG(marks) avg_marks FROM marks GROUP BY student_id) m ON m.student_id=s.id
        LEFT JOIN (SELECT student_id, COUNT(*) total_attendance, SUM(status='Present') present_count FROM attendance GROUP BY student_id) a ON a.student_id=s.id
        ORDER BY avg_marks DESC";
$r = mysqli_query($conn, $sql);
if ($r) {
    while ($row = mysqli_fetch_assoc($r)) {
        $totalAtt = (int)$row['total_attendance'];
        $row['attendance_pct'] = $totalAtt > 0 ? round(((int)$row['present_count'] / $totalAtt) * 100, 1) : null;
        $students[] = $row;
    }
}

// Keep the prompt context compact enough for the model while still providing useful analytics.
$contextStudents = array_slice($students, 0, 50);
$context = [
    'system_summary' => [
        'total_students' => (int)$totalStudents,
        'teachers' => (int)$totalTeachers,
        'subjects' => (int)$totalSubjects,
        'attendance_records' => (int)$totalAttendance,
        'present_records' => (int)$present,
        'absent_records' => (int)$absent,
        'average_marks' => round((float)$avgMarks, 2)
    ],
    'students' => $contextStudents
];

if (GEMINI_API_KEY === 'PASTE_YOUR_GEMINI_API_KEY_HERE') {
    echo json_encode(['reply' => 'AI is not configured yet. Add your Google AI Studio Gemini API key in includes/ai_config.php, then reload this page.']);
    exit;
}

$systemInstruction = "You are Student Analytics AI, a helpful assistant inside a teacher-facing student analytics web application. Use only the supplied database context. Answer questions about attendance, marks, rankings, trends, academic risk, and improvement suggestions. Do not invent student facts. If data is missing, clearly say so. Keep answers concise, practical and easy to scan. Protect student privacy and do not expose unnecessary personal contact details.\n\nDATABASE CONTEXT:\n" . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$payload = [
    'system_instruction' => ['parts' => [['text' => $systemInstruction]]],
    'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
    'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => 700]
];

$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode(GEMINI_MODEL) . ':generateContent?key=' . rawurlencode(GEMINI_API_KEY);
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30
]);
$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $curlError) {
    http_response_code(502);
    echo json_encode(['reply' => 'Could not reach the Gemini AI service. Please check your server connection.']);
    exit;
}

$data = json_decode($response, true);
if ($httpCode >= 400) {
    $message = $data['error']['message'] ?? 'Gemini API request failed.';
    http_response_code(502);
    echo json_encode(['reply' => 'AI service error: ' . $message]);
    exit;
}

$reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
if ($reply === '') $reply = 'I could not generate an answer from the available student data.';
echo json_encode(['reply' => $reply], JSON_UNESCAPED_UNICODE);
