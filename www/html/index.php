<?php
session_start();
include 'database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === "" || $password === "") {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $db_user, $db_pass);
            $stmt->fetch();
            if (password_verify($password, $db_pass)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $db_user;
                header('Location: dashboard.php');
                exit;
            }
        }
        $error = "Invalid username or password.";
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Platform - Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: system-ui, -apple-system, sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: white; border-radius: 16px; padding: 40px; width: 100%; max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        h2 { text-align: center; color: #333; margin-bottom: 8px; }
        .subtitle { text-align: center; color: #888; margin-bottom: 24px; font-size: 14px; }
        .error { background: #fee; color: #c33; padding: 10px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; text-align: center; }
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
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to take quizzes</p>
        <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn">Sign In</button>
        </form>
        <div class="footer">Don't have an account? <a href="register.php">Register</a></div>
    </div>
</body>
</html>
