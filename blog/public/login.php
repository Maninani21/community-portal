<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if(isset($_SESSION['user_id'])){
    header('Location: index.php');
    exit;
}

$err = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if($email && $password){
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND role='user'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if($user && password_verify($password, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: index.php');
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
<title>User Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width: 400px;">
<h2 class="mb-4 text-center">Login</h2>
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
<p class="mt-3 text-center">Not registered? <a href="register.php">Register here</a></p>
</form>
</div>
</body>
</html>
