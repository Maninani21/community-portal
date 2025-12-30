<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

$errors = [];
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if(!$name || !$email || !$password || !$confirm){
        $errors[] = "All fields are required.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = "Invalid email address.";
    } elseif($password !== $confirm){
        $errors[] = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
        $stmt->execute([$email]);
        if($stmt->rowCount()){
            $errors[] = "Email already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
            $stmt->execute([$name,$email,$hash,'user']);
            $success = "Registration successful! <a href='login.php'>Login here</a>.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Registration</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width: 400px;">
<h2 class="mb-4 text-center">Register</h2>

<?php if($errors): ?>
<div class="alert alert-danger"><?php foreach($errors as $e) echo $e.'<br>'; ?></div>
<?php endif; ?>

<?php if($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="POST">
<div class="mb-3">
<label>Name</label>
<input type="text" name="name" class="form-control" required>
</div>
<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" required>
</div>
<div class="mb-3">
<label>Password</label>
<input type="password" name="password" class="form-control" required>
</div>
<div class="mb-3">
<label>Confirm Password</label>
<input type="password" name="confirm" class="form-control" required>
</div>
<button type="submit" class="btn btn-primary w-100">Register</button>
<p class="mt-3 text-center">Already registered? <a href="login.php">Login here</a></p>
</form>
</div>
</body>
</html>
