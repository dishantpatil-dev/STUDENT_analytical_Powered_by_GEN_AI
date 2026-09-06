<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'includes/session.php';
include 'includes/db.php';

// Handle AJAX Request directly in this file
if (isset($_POST['user_message'])) {
    header('Content-Type: application/json');
    
    // ------------------------------------------------------------------------
    // 1. REPLACE THIS WITH YOUR ACTUAL GOOGLE AI STUDIO API KEY
    // ------------------------------------------------------------------------
    $apiKey = "AQ.Ab8RN6I6sNKbtz8DDmaoqhajiCdNcGrZZU1EYlucerpUzWk8eg"; 
    
    $userMsg = trim($_POST['user_message']);

    if (empty($userMsg)) {
        echo json_encode(['reply' => 'Please type a question.']);
        exit();
    }

    if ($apiKey === "YOUR_ACTUAL_GEMINI_API_KEY_HERE" || empty($apiKey)) {
        echo json_encode(['reply' => '<strong>API Key Missing!</strong> Please edit <code>ai_assistant.php</code> and insert your Gemini API Key at line 13.']);
        exit();
    }

    // 2. Fetch System Context from Database
    $studentsData = [];
    $s_query = mysqli_query($conn, "SELECT id, roll_no, name, class, gender, phone, email FROM students");
    while ($s = mysqli_fetch_assoc($s_query)) {
        $sid = $s['id'];
        
        // Fetch Attendance
        $att_q = mysqli_query($conn, "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present
            FROM attendance WHERE student_id = $sid
        ");
        $att_data = mysqli_fetch_assoc($att_q);
        $total_days = $att_data['total'] ?? 0;
        $present_days = $att_data['present'] ?? 0;
        $att_pct = $total_days > 0 ? round(($present_days / $total_days) * 100, 1) : 0;

        // Fetch Marks
        $marks_q = mysqli_query($conn, "
            SELECT s.subject_name, m.marks 
            FROM marks m 
            JOIN subjects s ON m.subject_id = s.id 
            WHERE m.student_id = $sid
        ");
        $marks = [];
        while ($m = mysqli_fetch_assoc($marks_q)) {
            $marks[$m['subject_name']] = $m['marks'];
        }

        $studentsData[] = [
            'roll_no' => $s['roll_no'],
            'name' => $s['name'],
            'class' => $s['class'],
            'attendance_percentage' => $att_pct . '%',
            'days_present' => "$present_days/$total_days",
            'subject_marks' => $marks
        ];
    }

    $dbContextJSON = json_encode($studentsData, JSON_PRETTY_PRINT);

    // 3. System Prompt Construction
    $systemPrompt = "You are a helpful AI Assistant for a Student Analytics System. "
                  . "Answer user queries based on the following student database context:\n\n"
                  . "DATABASE CONTEXT:\n" . $dbContextJSON . "\n\n"
                  . "INSTRUCTIONS:\n"
                  . "1. Provide accurate and concise answers.\n"
                  . "2. Format your response cleanly using HTML tags like <strong>, <br>, <ul>, <li>.\n"
                  . "3. User Question: " . $userMsg;

    // 4. API Request Endpoint
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=" . trim($apiKey);

    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $systemPrompt]
                ]
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr) {
        echo json_encode(['reply' => '<strong>Server Connection Error:</strong> ' . $curlErr]);
        exit();
    }

    $jsonResponse = json_decode($response, true);

    if ($httpCode !== 200) {
        $apiErrorMsg = $jsonResponse['error']['message'] ?? 'HTTP Code ' . $httpCode;
        echo json_encode(['reply' => '<strong>Google API Error (' . $httpCode . '):</strong> ' . htmlspecialchars($apiErrorMsg)]);
        exit();
    }

    $aiReply = $jsonResponse['candidates'][0]['content']['parts'][0]['text'] ?? null;

    if ($aiReply) {
        $formattedReply = nl2br(preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $aiReply));
        echo json_encode(['reply' => $formattedReply]);
    } else {
        echo json_encode(['reply' => 'No response content returned from Gemini API.']);
    }
    exit();
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<style>
    .page-container { padding-top: 1.5rem; }
    .glass-card {
        background: #111a2e;
        border: 1px solid rgba(0, 198, 255, 0.2);
        border-radius: 14px;
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
    }
    .chat-box {
        height: 420px;
        overflow-y: auto;
        padding: 15px;
        background: rgba(10, 17, 32, 0.6);
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .chat-msg {
        margin-bottom: 15px;
        display: flex;
        align-items: flex-start;
    }
    .msg-bot .msg-bubble {
        background: #172642;
        color: #ffffff;
        border: 1px solid rgba(0, 198, 255, 0.2);
        border-radius: 12px 12px 12px 0px;
        padding: 12px 16px;
        max-width: 85%;
        line-height: 1.5;
    }
    .msg-user {
        justify-content: flex-end;
    }
    .msg-user .msg-bubble {
        background: #00c6ff;
        color: #0b1329;
        font-weight: 500;
        border-radius: 12px 12px 0px 12px;
        padding: 12px 16px;
        max-width: 85%;
    }
    .quick-btn {
        background: rgba(17, 26, 46, 0.9);
        color: #00c6ff;
        border: 1px solid rgba(0, 198, 255, 0.3);
        border-radius: 8px;
        padding: 10px 14px;
        text-align: left;
        width: 100%;
        transition: all 0.2s;
    }
    .quick-btn:hover {
        background: #00c6ff;
        color: #0b1329;
        font-weight: 600;
    }
</style>

<div class="main-content">
    <?php include 'includes/navbar.php'; ?>
    <div class="container-fluid page-container">
        <div class="row g-4">
            
            <!-- Chat Window -->
            <div class="col-lg-8">
                <div class="glass-card p-4">
                    <h3 class="text-white mb-3"><i class="bi bi-robot text-info me-2"></i>Gemini AI Student Assistant</h3>
                    
                    <div class="chat-box mb-3" id="chatBox">
                        <div class="chat-msg msg-bot">
                            <div class="msg-bubble">
                                Hello! I'm powered by Google Gemini AI. Ask me anything about your student analytics data!
                            </div>
                        </div>
                    </div>

                    <form id="chatForm" class="d-flex gap-2">
                        <input type="text" id="userInput" class="form-control bg-dark text-white border-secondary" placeholder="Ask Gemini AI about student records..." required autocomplete="off">
                        <button type="submit" class="btn btn-info text-dark fw-bold px-4">Send</button>
                    </form>
                </div>
            </div>

            <!-- Quick Prompts Panel -->
            <div class="col-lg-4">
                <div class="glass-card p-4">
                    <h5 class="text-white mb-3"><i class="bi bi-lightning-charge text-warning me-2"></i>Quick Questions</h5>
                    <div class="d-flex flex-column gap-2">
                        <button class="quick-btn" onclick="sendQuickPrompt('What is the attendance and marks of student dishant?')">Attendance & Marks of Dishant?</button>
                        <button class="quick-btn" onclick="sendQuickPrompt('Which students have low attendance?')">Which students have low attendance?</button>
                        <button class="quick-btn" onclick="sendQuickPrompt('Who are the top performing students?')">Who are the top performing students?</button>
                        <button class="quick-btn" onclick="sendQuickPrompt('Provide a full class performance summary.')">Class Performance Summary</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    const chatForm = document.getElementById('chatForm');
    const userInput = document.getElementById('userInput');
    const chatBox = document.getElementById('chatBox');

    function appendMessage(text, isUser) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `chat-msg ${isUser ? 'msg-user' : 'msg-bot'}`;
        msgDiv.innerHTML = `<div class="msg-bubble">${text}</div>`;
        chatBox.appendChild(msgDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function sendPrompt(text) {
        appendMessage(text, true);
        
        const thinkingId = 'thinking-' + Date.now();
        const msgDiv = document.createElement('div');
        msgDiv.className = 'chat-msg msg-bot';
        msgDiv.id = thinkingId;
        msgDiv.innerHTML = `<div class="msg-bubble"><i class="bi bi-three-dots"></i> Gemini AI is thinking...</div>`;
        chatBox.appendChild(msgDiv);
        chatBox.scrollTop = chatBox.scrollHeight;

        const formData = new FormData();
        formData.append('user_message', text);

        fetch('ai_assistant.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            const tempEl = document.getElementById(thinkingId);
            if (tempEl) tempEl.remove();
            appendMessage(data.reply, false);
        })
        .catch(err => {
            const tempEl = document.getElementById(thinkingId);
            if (tempEl) tempEl.remove();
            appendMessage("Error communicating with server endpoint.", false);
        });
    }

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const text = userInput.value.trim();
        if(text !== "") {
            sendPrompt(text);
            userInput.value = '';
        }
    });

    function sendQuickPrompt(promptText) {
        sendPrompt(promptText);
    }
</script>

<?php include 'includes/footer.php'; ?>