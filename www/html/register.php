<?php
session_start();
include 'database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($username === "" || $password === "" || $confirm === "") {
        $error = "Please fill in all fields.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters.";
    } elseif (strlen($password) < 4) {
        $error = "Password must be at least 4 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username already taken.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashed);
            if ($stmt->execute()) {
                $success = "Account created! <a href='index.php'>Sign in</a>";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Platform - Register</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: system-ui, -apple-system, sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: white; border-radius: 16px; padding: 40px; width: 100%; max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        h2 { text-align: center; color: #333; margin-bottom: 8px; }
        .subtitle { text-align: center; color: #888; margin-bottom: 24px; font-size: 14px; }
        .error { background: #fee; color: #c33; padding: 10px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; text-align: center; }
        .success { background: #efe; color: #393; padding: 10px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; text-align: center; }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; transition: border-color 0.2s; }
        input[type="text"]:focus, input[type="password"]:focus { outline: none; border-color: #667eea; }
        label { display: block; margin-bottom: 6px; color: #555; font-weight: 500; font-size: 14px; }
        .form-group { margin-bottom: 16px; }
        .btn { width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: opacity 0.2s; }
        .btn:hover { opacity: 0.9; }
        .footer { text-align: center; margin-top: 20px; font-size: 14px; color: #888; }
        .footer a { color: #667eea; text-decoration: none; font-weight: 500; }
        .footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Create Account</h2>
        <p class="subtitle">Join the quiz platform</p>
        <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required minlength="3">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="4">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" name="register" class="btn">Create Account</button>
        </form>
        <div class="footer">Already have an account? <a href="index.php">Sign in</a></div>
    </div>
</body>
</html>
