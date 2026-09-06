<?php
session_start();
include 'includes/db.php';

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($fullname === "" || $username === "" || $password === "" || $confirm_password === "") {
        $error = "Please fill in all fields.";
    } elseif (strlen($username) < 3) {
        $error = "Username must contain at least 3 characters.";
    } elseif (strlen($password) < 6) {
        $error = "Password must contain at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");

        if (!$check) {
            $error = "Database query failed.";
        } else {
            mysqli_stmt_bind_param($check, "s", $username);
            mysqli_stmt_execute($check);
            mysqli_stmt_store_result($check);

            if (mysqli_stmt_num_rows($check) > 0) {
                $error = "Username already exists.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO users (fullname, username, password, role)
                     VALUES (?, ?, ?, 'Teacher')"
                );

                if ($stmt) {
                    mysqli_stmt_bind_param(
                        $stmt,
                        "sss",
                        $fullname,
                        $username,
                        $hashed_password
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        $success = "Account created successfully. You can now log in.";
                    } else {
                        $error = "Registration failed. Please try again.";
                    }

                    mysqli_stmt_close($stmt);
                } else {
                    $error = "Database query failed.";
                }
            }

            mysqli_stmt_close($check);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Student Analytics</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #101827, #1e293b);
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .register-card {
            width: 100%;
            max-width: 450px;
            background: #ffffff;
            border-radius: 18px;
            padding: 35px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        .register-card h2 {
            font-weight: 700;
            color: #172033;
        }

        .form-control {
            height: 48px;
            border-radius: 10px;
        }

        .btn-register {
            height: 48px;
            border: none;
            border-radius: 10px;
            background: #2563eb;
            color: white;
            font-weight: 600;
        }

        .btn-register:hover {
            background: #1d4ed8;
        }

        a {
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="register-card">
    <div class="text-center mb-4">
        <h2>Create Account</h2>
        <p class="text-muted mb-0">Student Analytics System</p>
    </div>

    <?php if ($error !== ""): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php">

        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input
                type="text"
                name="fullname"
                class="form-control"
                placeholder="Enter your full name"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Username</label>
            <input
                type="text"
                name="username"
                class="form-control"
                placeholder="Create a username"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input
                type="password"
                name="password"
                class="form-control"
                placeholder="Create a password"
                required
            >
        </div>

        <div class="mb-4">
            <label class="form-label">Confirm Password</label>
            <input
                type="password"
                name="confirm_password"
                class="form-control"
                placeholder="Confirm your password"
                required
            >
        </div>

        <button type="submit" name="register" class="btn btn-register w-100">
            Create Account
        </button>
    </form>

    <div class="text-center mt-4">
        <span class="text-muted">Already have an account?</span>
        <a href="login.php">Login here</a>
    </div>
</div>

</body>
</html>