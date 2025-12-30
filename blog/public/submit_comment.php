<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $post_id = $_POST['post_id'] ?? null;
    $content = trim($_POST['content'] ?? '');

    if(!$post_id || !$content){
        $_SESSION['message'] = "Post ID or comment cannot be empty.";
        header("Location: post.php?id=" . ($post_id ?? ''));
        exit;
    }

    if(isset($_SESSION['user_id'])){
        $user_id = $_SESSION['user_id'];

        // Fetch user info
        $stmt = $pdo->prepare("SELECT name,email FROM users WHERE id=?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        $user_name = $user['name'];
        $user_email = $user['email'];
    } else {
        // Optional: allow guests (replace these lines with guest input fields)
        $_SESSION['message'] = "Please login to comment.";
        header("Location: login.php");
        exit;
    }

    // Insert comment
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, user_name, user_email, content, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$post_id, $user_id, $user_name, $user_email, $content, 'pending']);

    $_SESSION['message'] = "Comment submitted for approval.";
    header("Location: post.php?id=$post_id");
    exit;
}
?>
