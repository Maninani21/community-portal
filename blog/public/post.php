<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$post_id = (int)$_GET['id'];

// ---------- HANDLE VIEWS ----------
if (!isset($_SESSION['viewed_posts'])) {
    $_SESSION['viewed_posts'] = [];
}

if (!in_array($post_id, $_SESSION['viewed_posts'])) {
    $pdo->prepare("UPDATE posts SET views = COALESCE(views,0) + 1 WHERE id=?")->execute([$post_id]);
    $_SESSION['viewed_posts'][] = $post_id;
}

// ---------- FETCH POST ----------
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, u.name as author_name
    FROM posts p 
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.author_id = u.id
    WHERE p.id=? AND p.status='published'
");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    echo "<div style='padding:20px;text-align:center;color:red;'>❌ Post not found.</div>";
    exit;
}

// ---------- FETCH APPROVED COMMENTS ----------
$stmt2 = $pdo->prepare("
    SELECT * FROM comments 
    WHERE post_id=? AND status='approved' 
    ORDER BY created_at DESC
");
$stmt2->execute([$post_id]);
$comments = $stmt2->fetchAll();

// ---------- FETCH RELATED POSTS ----------
$stmt3 = $pdo->prepare("
    SELECT id, title FROM posts 
    WHERE category_id=? AND id!=? AND status='published' 
    ORDER BY created_at DESC LIMIT 3
");
$stmt3->execute([$post['category_id'], $post_id]);
$related = $stmt3->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= sanitize($post['meta_title'] ?: $post['title']) ?></title>
<meta name="description" content="<?= sanitize($post['meta_description'] ?: substr(strip_tags($post['content']),0,150)) ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
img, video, iframe { max-width: 100%; border-radius: 8px; margin-bottom: 1rem; }
.card-related { margin-bottom: 1rem; }
body { background: #f8f9fa; }
</style>
</head>
<body>
<div class="container mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between mb-4 align-items-center">
        <h1>
            <?= sanitize($post['title']) ?>
            <?php if ($post['featured']): ?>
                <span class="badge bg-warning text-dark">Featured</span>
            <?php endif; ?>
        </h1>
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                Welcome, <?= sanitize($_SESSION['user_name']) ?> |
                <a href="index.php" class="btn btn-outline-primary btn-sm">Dashboard</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-primary btn-sm">Login</a>
                <a href="register.php" class="btn btn-outline-success btn-sm">Register</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Back Button -->
    <a href="index.php" class="btn btn-secondary btn-sm mb-3">← Back to Dashboard</a>

    <!-- Post Meta -->
    <p>
        <small class="text-muted">
            Category: <a href="index.php?category=<?= $post['category_id'] ?>"><?= sanitize($post['category_name']) ?></a> | 
            Posted on <?= $post['created_at'] ?> by <?= sanitize($post['author_name'] ?? 'Admin') ?> | 
            👁 <?= (int)$post['views'] ?> Views
        </small>
    </p>

    <!-- Featured Image -->
    <?php if (!empty($post['image'])): ?>
        <img src="/assets/images/<?= sanitize($post['image']) ?>" 
             class="img-fluid mb-4" 
             alt="<?= sanitize($post['title']) ?>">
    <?php endif; ?>

    <!-- Video / YouTube Embed -->
    <?php if (!empty($post['video_file']) && file_exists(__DIR__ . '/../assets/videos/' . $post['video_file'])): ?>
        <div class="mb-4">
            <video width="100%" controls>
                <source src="/assets/videos/<?= sanitize($post['video_file']) ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
    <?php elseif (!empty($post['video_link'])): ?>
        <?php 
            // Convert standard YouTube links to embed format
            $embed_link = $post['video_link'];
            if (strpos($embed_link, 'youtube.com/watch?v=') !== false) {
                parse_str(parse_url($embed_link, PHP_URL_QUERY), $youtube_params);
                $embed_link = 'https://www.youtube.com/embed/' . $youtube_params['v'];
            } elseif (strpos($embed_link, 'youtu.be/') !== false) {
                $embed_link = str_replace('youtu.be/', 'www.youtube.com/embed/', $embed_link);
            }
        ?>
        <div class="mb-4 ratio ratio-16x9">
            <iframe src="<?= sanitize($embed_link) ?>" frameborder="0" allowfullscreen></iframe>
        </div>
    <?php endif; ?>

    <!-- Content -->
    <div><?= $post['content'] ?></div>

    <!-- Tags -->
    <?php if (!empty($post['tags'])): ?>
        <p class="mt-3">
            Tags: 
            <?php foreach (explode(',', $post['tags']) as $tag): ?>
                <a href="index.php?search=<?= urlencode(trim($tag)) ?>" 
                   class="badge bg-secondary"><?= sanitize(trim($tag)) ?></a>
            <?php endforeach; ?>
        </p>
    <?php endif; ?>

    <!-- Comments -->
    <hr>
    <h4>Comments</h4>
    <?php if ($comments): ?>
        <?php foreach ($comments as $c): ?>
            <div class="mb-3 border p-2 rounded bg-white">
                <strong><?= sanitize($c['user_name']) ?></strong> 
                <small class="text-muted"><?= $c['created_at'] ?></small>
                <p><?= nl2br(sanitize($c['content'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No comments yet.</p>
    <?php endif; ?>

    <!-- Comment Form -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <h4>Leave a Comment</h4>
        <form method="POST" action="submit_comment.php">
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
            <textarea name="content" class="form-control mb-2" rows="4" required></textarea>
            <button type="submit" class="btn btn-primary">Submit Comment</button>
            <small class="text-muted">Your comment will appear after approval.</small>
        </form>
    <?php else: ?>
        <p>Please <a href="login.php">login</a> to comment.</p>
    <?php endif; ?>

    <!-- Related Posts -->
    <?php if ($related): ?>
        <hr>
        <h4>Related Posts</h4>
        <div class="row">
            <?php foreach ($related as $r): ?>
                <div class="col-md-4 card-related">
                    <div class="card p-2">
                        <a href="post.php?id=<?= $r['id'] ?>"><?= sanitize($r['title']) ?></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Social Share -->
    <hr>
    <h5>Share this post:</h5>
    <?php $share_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>
    <a href="https://wa.me/?text=<?= urlencode($share_url) ?>" target="_blank" class="btn btn-success btn-sm">WhatsApp</a>
    <a href="https://t.me/share/url?url=<?= urlencode($share_url) ?>&text=<?= urlencode($post['title']) ?>" target="_blank" class="btn btn-info btn-sm">Telegram</a>
    <a href="mailto:?subject=Check this post&body=<?= urlencode($share_url) ?>" class="btn btn-secondary btn-sm">Email</a>

</div>
</body>
</html>
