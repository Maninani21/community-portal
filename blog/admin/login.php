<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if(isset($_SESSION['admin_id'])){
    header('Location: index.php');
    exit;
}

$err = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if($email && $password){
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND role='admin'");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if($admin && password_verify($password, $admin['password'])){
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            header('Location: index.php'); // Admin dashboard
            exit;
        } else {
            $err = "Invalid email or password.";
        }
    } else {
        $err = "Please enter email and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width: 400px;">
<h2 class="mb-4 text-center">Admin Login</h2>
<?php if($err): ?>
<div class="alert alert-danger"><?= $err ?></div>
<?php endif; ?>
<form method="POST">
<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" required>
</div>
<div class="mb-3">
<label>Password</label>
<input type="password" name="password" class="form-control" required>
</div>
<button type="submit" class="btn btn-primary w-100">Login</button>
</form>
</div>
</body>
</html>
