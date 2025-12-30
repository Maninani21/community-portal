<?php

require_once 'db.php';
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Blog</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
.theme-switcher {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
    cursor: pointer;
    font-size: 1.5rem;
}
</style>
</head>
<body>
<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">My Blog</a>
        <div class="d-flex align-items-center">
            <?php if(isset($_SESSION['user_id'])): ?>
                <span class="me-2">Welcome, <?= sanitize($_SESSION['user_name']) ?></span>
                <a href="dashboard.php" class="btn btn-outline-primary me-2"><i class="bi bi-speedometer2"></i></a>
                <a href="logout.php" class="btn btn-outline-danger me-2">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-primary me-2">Login</a>
                <a href="register.php" class="btn btn-outline-success me-2">Register</a>
            <?php endif; ?>
            <i class="bi bi-palette-fill theme-switcher" id="theme-icon" title="Change Background"></i>
        </div>
    </div>
</nav>
