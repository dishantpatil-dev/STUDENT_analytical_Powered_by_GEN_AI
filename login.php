<?php
session_start();
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please fill in all fields.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, username, password FROM users WHERE username = ?"
        );

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password'])) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = (int)$row['id'];
                $_SESSION['username'] = $row['username'];

                header("Location: dashboard.php");
                exit();
            }
        }

        $error = "Invalid username or password.";
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Analytics System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #0b1329;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-card {
            background: #111a2e;
            border: 1px solid rgba(0, 198, 255, 0.2);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
        }

        .form-control {
            background-color: #17233d;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .form-control:focus {
            background-color: #1a2846;
            color: #ffffff;
            border-color: #00c6ff;
            box-shadow: 0 0 10px rgba(0, 198, 255, 0.3);
        }
    </style>
</head>
<body>

<div class="login-card text-center">
    <div class="mb-3">
        <i class="bi bi-mortarboard-fill text-info display-4"></i>
    </div>

    <h3 class="fw-bold text-white mb-1">Student Analytics System</h3>
    <p class="text-white-50 mb-4">Enter credentials to login</p>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 border-0 bg-danger text-white">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3 text-start">
            <label class="form-label text-white-50 fw-bold">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-info">
                    <i class="bi bi-person-fill"></i>
                </span>
                <input type="text" name="username" class="form-control" required>
            </div>
        </div>

        <div class="mb-4 text-start">
            <label class="form-label text-white-50 fw-bold">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-info">
                    <i class="bi bi-lock-fill"></i>
                </span>
                <input type="password" name="password" id="passwordInput"
                       class="form-control" required>

                <button class="btn btn-outline-secondary" type="button"
                        onclick="togglePassword()">
                    <i class="bi bi-eye-fill text-info" id="eyeIcon"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-info text-dark fw-bold w-100 py-2">
            <i class="bi bi-box-arrow-in-right me-1"></i> Login
        </button>
    </form>

    <div class="text-center mt-4">
        <span class="text-white-50">Don't have an account?</span>
        <a href="register.php" class="text-info text-decoration-none fw-bold">
            Create Account
        </a>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const icon = document.getElementById('eyeIcon');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
    }
}
</script>

</body>
</html>