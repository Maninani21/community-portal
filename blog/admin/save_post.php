<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title']);
    $content = $_POST['content'];
    $video_link = !empty($_POST['video_link']) ? trim($_POST['video_link']) : null;

    $image_file = null;
    $video_file = null;

    // ---------- Handle Image ----------
    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . "_" . basename($_FILES['image']['name']);
        $target = "../uploads/" . $image_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_file = $image_name;
        }
    }

    // ---------- Handle Video ----------
    if (!empty($_FILES['video_file']['name'])) {
        $video_name = time() . "_" . basename($_FILES['video_file']['name']);
        $target = "../uploads/" . $video_name;
        if (move_uploaded_file($_FILES['video_file']['tmp_name'], $target)) {
            $video_file = $video_name;
        }
    }

    // ---------- Insert Post ----------
    $stmt = $pdo->prepare("INSERT INTO posts 
        (title, content, image, video_file, video_link, status, created_at) 
        VALUES (?, ?, ?, ?, ?, 'published', NOW())");

    $stmt->execute([$title, $content, $image_file, $video_file, $video_link]);

    header("Location: index.php?msg=Post+created");
    exit;
}
