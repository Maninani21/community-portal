<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if(!isset($_SESSION['admin_id'])){
    header('Location: login.php');
    exit;
}

$err = '';
$success = '';

// Handle admin update
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $user_id = $_POST['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);

    $update_fields = [];
    $update_params = [];

    if($name) { $update_fields[] = "name=?"; $update_params[] = $name; }
    if($email) { $update_fields[] = "email=?"; $update_params[] = $email; }
    if($phone) { $update_fields[] = "phone=?"; $update_params[] = $phone; }
    if($password) { 
        $update_fields[] = "password=?"; 
        $update_params[] = password_hash($password, PASSWORD_DEFAULT); 
    }

    if($update_fields){
        $update_params[] = $user_id;
        $sql = "UPDATE users SET ".implode(", ", $update_fields)." WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($update_params);
        $success = "User updated successfully.";
    } else {
        $err = "Nothing to update.";
    }
}

// Fetch all users
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Management - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>User Management</h2>
    <a href="index.php" class="btn btn-secondary mb-3"><i class="bi bi-house-door"></i> Back to Dashboard</a>

    <?php if($err): ?><div class="alert alert-danger"><?= sanitize($err) ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?= sanitize($success) ?></div><?php endif; ?>

    <div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Password</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($users as $u): ?>
            <tr>
                <form method="POST">
                    <td><?= $u['id'] ?><input type="hidden" name="user_id" value="<?= $u['id'] ?>"></td>
                    <td><input type="text" name="name" class="form-control" value="<?= sanitize($u['name']) ?>" required></td>
                    <td><input type="email" name="email" class="form-control" value="<?= sanitize($u['email'] ?? '') ?>"></td>
                    <td><input type="text" name="phone" class="form-control" value="<?= sanitize($u['phone'] ?? '') ?>"></td>
                    <td><input type="password" name="password" class="form-control" placeholder="New password"></td>
                    <td><button type="submit" class="btn btn-sm btn-success mt-1">Update</button></td>
                </form>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
</body>
</html>
