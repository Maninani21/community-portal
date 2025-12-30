<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if(!isset($_SESSION['user_id'])){
    header('Location: ../public/login.php');
    exit;
}

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$err = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name']);
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify old password
    if(!password_verify($old_password, $user['password'])){
        $err = "Old password is incorrect.";
    } elseif($new_password && $new_password !== $confirm_password){
        $err = "New password and confirm password do not match.";
    } else {
        // Update query
        $update_fields = [];
        $update_params = [];

        if($name && $name !== $user['name']){
            $update_fields[] = "name=?";
            $update_params[] = $name;
            $_SESSION['user_name'] = $name; // update session
        }

        if($new_password){
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update_fields[] = "password=?";
            $update_params[] = $hashed;
        }

        // Only allow adding email/phone if empty
        if(empty($user['email']) && !empty($_POST['email'])){
            $update_fields[] = "email=?";
            $update_params[] = $_POST['email'];
        }

        if(empty($user['phone']) && !empty($_POST['phone'])){
            $update_fields[] = "phone=?";
            $update_params[] = $_POST['phone'];
        }

        if($update_fields){
            $update_params[] = $user['id'];
            $sql = "UPDATE users SET ".implode(", ", $update_fields)." WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($update_params);
            $success = "Profile updated successfully.";
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        } else {
            $err = "Nothing to update.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Settings</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>User Settings</h2>
    <?php if($err): ?><div class="alert alert-danger"><?= sanitize($err) ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?= sanitize($success) ?></div><?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?= sanitize($user['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= sanitize($user['email'] ?? '') ?>" <?= !empty($user['email']) ? 'readonly' : '' ?> placeholder="Add email if empty">
        </div>

        <div class="mb-3">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control" value="<?= sanitize($user['phone'] ?? '') ?>" <?= !empty($user['phone']) ? 'readonly' : '' ?> placeholder="Add phone if empty">
        </div>

        <hr>
        <h5>Change Password</h5>
        <div class="mb-3">
            <label class="form-label">Old Password</label>
            <input type="password" name="old_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>
        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
    </form>
</div>
</body>
</html>
