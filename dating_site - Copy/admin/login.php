<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        try {
            // Fetch admin user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_admin = 1 LIMIT 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['password'])) {
                // ✅ Login successful
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];

                header('Location: index.php'); // Redirect to dashboard
                exit;
            } else {
                $error = '❌ Invalid email or password';
            }
        } catch (PDOException $e) {
            $error = '❌ Database error: ' . $e->getMessage();
        }
    } else {
        $error = '⚠️ Please enter both email and password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | LoveConnect</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #ff758c, #ff7eb3);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: #fff;
            padding: 35px 30px;
            border-radius: 12px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.2);
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 25px;
            color: #ff4d79;
            font-size: 26px;
        }
        .login-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        .login-container button {
            width: 100%;
            background: #ff4d79;
            color: #fff;
            border: none;
            padding: 12px;
            margin-top: 15px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .login-container button:hover {
            background: #e63e68;
        }
        .error {
            color: red;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .toggle-pass {
            cursor: pointer;
            font-size: 14px;
            color: #555;
            text-align: left;
            margin-top: -8px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Admin Login</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="email" name="email" placeholder="Admin Email" required>
        <input type="password" id="password" name="password" placeholder="Password" required>
        <div class="toggle-pass" onclick="togglePassword()">👁 Show/Hide Password</div>
        <button type="submit">Login</button>
    </form>
</div>

<script>
function togglePassword() {
    const pass = document.getElementById('password');
    if (pass.type === 'password') {
        pass.type = 'text';
    } else {
        pass.type = 'password';
    }
}
</script>

</body>
</html>
