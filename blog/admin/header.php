<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
if(!isset($_SESSION['admin_id'])) redirect('login.php');
$settings = $pdo->query("SELECT * FROM settings WHERE id=1")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= sanitize($settings['site_name']) ?> - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.sidebar { min-height: 100vh; background:#343a40; color:#fff; }
.sidebar a { color:#fff; display:block; padding:10px; text-decoration:none; }
.sidebar a:hover { background:#495057; }
.navbar-brand img{max-height:40px;}
</style>
</head>
<body>
<div class="d-flex">
<div class="sidebar p-3">
<div class="text-center mb-4">
<?php if($settings['logo']): ?><img src="../assets/images/<?= $settings['logo'] ?>" class="img-fluid"><br><?php endif; ?>
<strong><?= sanitize($settings['site_name']) ?></strong>
</div>
<a href="index.php">Dashboard</a>
<a href="posts.php">Posts</a>
<a href="categories.php">Categories</a>
<a href="users.php">Users</a>
<a href="comments.php">Comments</a>
<a href="settings.php">Settings</a>
<a href="analytics.php">analytics</a>
<a href="user_settings.php">user details</a>
<a href="logout.php">Logout</a>

</div>
<div class="flex-grow-1 p-4">
