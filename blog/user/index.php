<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if(!isset($_SESSION['user_id'])){
    header('Location: ../public/login.php');
    exit;
}

// Fetch user's posts
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name 
                       FROM posts p 
                       LEFT JOIN categories c ON p.category_id = c.id
                       WHERE p.author_id=? 
                       ORDER BY p.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between mb-4 align-items-center">
        <h1>Welcome, <?= sanitize($_SESSION['user_name']) ?></h1>
        <div>
            <a href="../public/index.php" class="btn btn-secondary me-2">
                <i class="bi bi-house-door"></i> Back to Blog
            </a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <a href="edit_post.php" class="btn btn-primary mb-3">Create New Post</a>

    <div class="table-responsive">
    <table class="table table-bordered table-striped">
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Category</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach($posts as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= sanitize($p['title']) ?></td>
            <td><?= sanitize($p['category_name']) ?></td>
            <td><?= $p['status'] ?></td>
            <td>
                <a href="edit_post.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>
</div>
</body>
</html>
