<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id'])) {
    header('Location: index.php');
    exit;
}

$postId = (int)$_POST['post_id'];
$userId = $_SESSION['user_id'];

// Prevent duplicate likes
$check = $pdo->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id=? AND user_id=?");
$check->execute([$postId, $userId]);

if ($check->fetchColumn() == 0) {
    $pdo->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)")->execute([$postId, $userId]);
    $pdo->prepare("UPDATE posts SET likes = likes + 1 WHERE id=?")->execute([$postId]);
}

header("Location: post.php?id=$postId");
exit;
