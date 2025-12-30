<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if(!isset($_SESSION['user_id'])){
    header('Location: ../public/login.php');
    exit;
}

$id = $_GET['id'] ?? null;
$title = $content = $category_id = $status = $image = $video_file = $tags = '';
$status = 'pending';
$post_type = 'general';

// Fetch post if editing
if($id){
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id=? AND author_id=?");
    $stmt->execute([$id,$_SESSION['user_id']]);
    $post = $stmt->fetch();
    if($post){
        $title = $post['title'];
        $content = $post['content'];
        $category_id = $post['category_id'];
        $status = $post['status'];
        $image = $post['image'];
        $video_file = $post['video_file'];
        $tags = $post['tags'];
        $post_type = $post['post_type'] ?? 'general';
    }
}

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

// Post types array
$post_types = ['general', 'news', 'announcement', 'tutorial', 'event'];

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title = trim($_POST['title']);
    $content = $_POST['content'];
    $category_id = $_POST['category_id'];
    $slug = slugify($title);
    $tags = $_POST['tags'];
    $post_type = $_POST['post_type'] ?? 'general';

    // Image upload
    $img_name = $image;
    if(isset($_FILES['image']) && $_FILES['image']['name'] != ''){
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $img_name = uniqid().'.'.$ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/".$img_name);
    }

    // Video upload
    $vid_name = $video_file;
    if(isset($_FILES['video_file']) && $_FILES['video_file']['name'] != ''){
        $ext = pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION);
        $vid_name = uniqid().'.'.$ext;
        move_uploaded_file($_FILES['video_file']['tmp_name'], "../uploads/".$vid_name);
    }

    if($id){
        // Update post
        $stmt = $pdo->prepare("UPDATE posts SET title=?, slug=?, content=?, category_id=?, status=?, image=?, video_file=?, tags=?, post_type=? WHERE id=? AND author_id=?");
        $stmt->execute([$title,$slug,$content,$category_id,$status,$img_name,$vid_name,$tags,$post_type,$id,$_SESSION['user_id']]);
    } else {
        // Insert new post
        $stmt = $pdo->prepare("INSERT INTO posts (title,slug,content,category_id,author_id,status,image,video_file,tags,post_type) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$title,$slug,$content,$category_id,$_SESSION['user_id'],$status,$img_name,$vid_name,$tags,$post_type]);
    }

    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $id?'Edit':'Add' ?> Post</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
<h1><?= $id?'Edit':'Add' ?> Post</h1>

<!-- Back to Dashboard Button -->
<div class="mb-3">
    <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
</div>

<form method="POST" enctype="multipart/form-data">

    <div class="mb-3">
        <label>Title</label>
        <input type="text" name="title" class="form-control" value="<?= sanitize($title) ?>" required>
    </div>

    <div class="mb-3">
        <label>Category</label>
        <select name="category_id" class="form-select" required>
            <option value="">Select Category</option>
            <?php foreach($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $category_id==$c['id']?'selected':'' ?>><?= sanitize($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label>Post Type</label>
        <select name="post_type" class="form-select" required>
            <?php foreach($post_types as $type): ?>
                <option value="<?= $type ?>" <?= $post_type==$type?'selected':'' ?>><?= ucfirst($type) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label>Content</label>
        <textarea name="content" class="form-control" rows="6"><?= sanitize($content) ?></textarea>
    </div>

    <div class="mb-3">
        <label>Tags (comma separated)</label>
        <input type="text" name="tags" class="form-control" value="<?= sanitize($tags) ?>">
    </div>

    <div class="mb-3">
        <label>Featured Image</label>
        <input type="file" name="image" class="form-control" accept="image/*">
        <?php if($image): ?><img src="../uploads/<?= $image ?>" width="120" class="mt-2"><?php endif; ?>
    </div>

    <div class="mb-3">
        <label>Upload Video</label>
        <input type="file" name="video_file" class="form-control" accept="video/*">
        <?php if($video_file): ?><video width="200" controls class="mt-2"><source src="../uploads/<?= $video_file ?>" type="video/mp4"></video><?php endif; ?>
    </div>

    <!-- Buttons -->
    <div class="mb-3">
        <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
        <button type="submit" class="btn btn-success"><?= $id?'Update':'Add' ?> Post</button>
    </div>

</form>
</div>
</body>
</html>
