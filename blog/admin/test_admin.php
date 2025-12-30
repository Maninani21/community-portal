<?php
require_once '../includes/db.php'; // make sure path is correct

$email = 'admin@example.com';
$password = 'admin123'; // the password you want to test

$stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND role='admin'");
$stmt->execute([$email]);
$admin = $stmt->fetch();

if(!$admin){
    echo "Admin not found in DB or role is not 'admin'.";
    exit;
}

// Check password
if(password_verify($password, $admin['password'])){
    echo "Password is correct! Login will work.";
} else {
    echo "Password is incorrect!";
}
?>
