<?php include 'header.php'; ?>
<!-- Theme Switcher Icon -->
<!-- Theme Switcher Button -->
<button id="theme-icon" 
    style="position:fixed; top:20px; right:20px; z-index:1000; 
           background:#fff; border:none; padding:10px; border-radius:50%; 
           box-shadow:0 2px 5px rgba(0,0,0,0.3); cursor:pointer;"
    title="Toggle Theme">🌙</button>
<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// ----------------- HANDLE AJAX LIKE REQUEST -----------------
if (isset($_POST['like_post_id']) && isset($_SESSION['user_id'])) {
    $postId = (int)$_POST['like_post_id'];
    $userId = (int)$_SESSION['user_id'];

    // Check if user already liked
    $check = $pdo->prepare("SELECT 1 FROM post_likes WHERE user_id=? AND post_id=?");
    $check->execute([$userId, $postId]);

    if (!$check->fetch()) {
        // Add like record
        $pdo->prepare("INSERT INTO post_likes (user_id, post_id) VALUES (?,?)")->execute([$userId, $postId]);
        // Increase like count in posts
        $pdo->prepare("UPDATE posts SET likes = COALESCE(likes,0)+1 WHERE id=?")->execute([$postId]);
    }

    // Return new like count
    $newLikes = $pdo->query("SELECT likes FROM posts WHERE id=$postId")->fetchColumn();
    echo $newLikes;
    exit;
}

// ----------------- FETCH POSTS -----------------
$stmt = $pdo->query("
    SELECT p.*, c.name AS category_name, u.name AS author_name 
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.author_id = u.id
    WHERE p.status='published'
    ORDER BY p.featured DESC, p.created_at DESC
");
$allPosts = $stmt->fetchAll();

// ----------------- POST TYPES -----------------
$post_types = ['general', 'news', 'announcement', 'tutorial', 'event'];
$postTypeIcons = [
    'general'      => 'bi-journal-text',
    'news'         => 'bi-newspaper',
    'announcement' => 'bi-megaphone',
    'tutorial'     => 'bi-lightbulb',
    'event'        => 'bi-calendar-event'
];
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
.card-img-top { max-height: 250px; object-fit: cover; transition: transform 0.3s ease; }
.card-img-top:hover { transform: scale(1.05); }
.card { transition: box-shadow 0.3s ease; }
.card:hover { box-shadow: 0px 8px 20px rgba(0,0,0,0.2); }
.badge-featured { background-color: #ffc107; color: #000; }
.post-type-icon { font-size: 1.2rem; margin-right: 5px; color: #0d6efd; }
.btn-filter { margin-right:5px; }
</style>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-4">

<!-- Header -->
<div class="d-flex justify-content-between mb-4 align-items-center">
    <h1>My Blog</h1>
    <div>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="../user/index.php" class="btn btn-outline-primary" title="Dashboard"><i class="bi bi-speedometer2"></i></a>
            <a href="settings.php" class="btn btn-outline-secondary" title="Settings"><i class="bi bi-gear"></i></a>
            <span>Welcome, <?= sanitize($_SESSION['user_name']) ?></span>
            <a href='logout.php' class="btn btn-outline-danger">Logout</a>
        <?php else: ?>
            <a href='login.php' class="btn btn-outline-primary">Login</a>
            <a href='register.php' class="btn btn-outline-success">Register</a>
        <?php endif; ?>
    </div>
</div>

<!-- Post Filters -->
<div class="mb-3">
    <button class="btn btn-outline-primary btn-filter" data-type="all"><i class="bi bi-grid-1x2"></i> All</button>
    <?php foreach($post_types as $type): ?>
        <button class="btn btn-outline-secondary btn-filter" data-type="<?= $type ?>">
            <i class="bi <?= $postTypeIcons[$type] ?>"></i> <?= ucfirst($type) ?>
        </button>
    <?php endforeach; ?>
</div>

<!-- Posts Grid -->
<div id="posts-container" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
<?php foreach($allPosts as $p): 
    $postLink = "http://localhost/blog/public/post.php?id=".$p['id']; ?>
    <div class="col post-card" data-post-type="<?= $p['post_type'] ?? 'general' ?>">
        <div class="card h-100">
            <?php if($p['image']): ?>
                <img src="assets/images/<?= $p['image'] ?>" class="card-img-top" alt="<?= sanitize($p['title']) ?>">
            <?php endif; ?>

            <div class="card-body">
                <h5 class="card-title">
                    <?= sanitize($p['title']) ?>
                    <?php if($p['featured']): ?><span class="badge badge-featured">Featured</span><?php endif; ?>
                </h5>
                <p><i class="bi <?= $postTypeIcons[$p['post_type'] ?? 'general'] ?> post-type-icon"></i> <?= ucfirst($p['post_type'] ?? 'General') ?></p>
                <p class="card-text"><?= substr(strip_tags($p['content']),0,120) ?>...</p>
                <p><small class="text-muted">Category: <?= sanitize($p['category_name']) ?> | By: <?= sanitize($p['author_name'] ?? 'Admin') ?> | <?= $p['created_at'] ?></small></p>

                <div class="d-flex justify-content-between mt-2">
                    <!-- Like Button -->
                    <button class="btn btn-outline-primary btn-like" data-post-id="<?= $p['id'] ?>">
                        <i class="bi bi-hand-thumbs-up"></i> Like <span class="like-count"><?= $p['likes'] ?? 0 ?></span>
                    </button>

                    <!-- Views Counter -->
                    <span class="badge bg-light text-dark"><i class="bi bi-eye"></i> <?= $p['views'] ?? 0 ?> Views</span>

                    <!-- Share Dropdown -->
                    <div class="btn-group">
                        <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Share</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" target="_blank" href="https://wa.me/?text=<?= urlencode($postLink) ?>">WhatsApp</a></li>
                            <li><a class="dropdown-item" target="_blank" href="https://t.me/share/url?url=<?= urlencode($postLink) ?>&text=Check+this+post">Telegram</a></li>
                            <li><a class="dropdown-item" href="mailto:?subject=Check this post&body=<?= urlencode($postLink) ?>">Email</a></li>
                            <li><button class="dropdown-item copy-link-btn" data-link="<?= $postLink ?>">Copy Link</button></li>
                        </ul>
                    </div>

                    <!-- Comment -->
                    <a href="post.php?id=<?= $p['id'] ?>" class="btn btn-outline-success"><i class="bi bi-chat"></i> Comment</a>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
</div>

<script>
$(function(){
    // Like button
    $('.btn-like').click(function(){
        var btn = $(this);
        var postId = btn.data('post-id');
        $.post('', {like_post_id: postId}, function(newLikes){
            btn.find('.like-count').text(newLikes);
        });
    });

    // Copy link
    $('.copy-link-btn').click(function(){
        navigator.clipboard.writeText($(this).data('link'));
        alert('Link copied to clipboard!');
    });

    // Filter
    $('.btn-filter').click(function(){
        var type = $(this).data('type');
        if(type === 'all'){ $('.post-card').show(); }
        else {
            $('.post-card').hide();
            $('.post-card[data-post-type="'+type+'"]').show();
        }
    });
});


</script>
</body>
</html>
<script>
<?php include 'footer.php'; ?>
/<script>
// Select elements
const themeIcon = document.getElementById('theme-icon');
const body = document.body;

// Define colors (light / dark)
const themes = [
    { bg: '#FFFFFF', color: '#000000', icon: '🌙' }, // Light mode
    { bg: '#000000', color: '#FFFFFF', icon: '☀️' }  // Dark mode
];

let currentTheme = 0;

// Load saved theme from localStorage
const savedThemeIndex = localStorage.getItem('themeIndex');
if (savedThemeIndex !== null) {
    currentTheme = parseInt(savedThemeIndex);
    applyTheme(currentTheme);
}

// Apply theme to body and icon
function applyTheme(index) {
    body.style.backgroundColor = themes[index].bg;
    body.style.color = themes[index].color;
    themeIcon.textContent = themes[index].icon;
}

// Toggle theme on click
themeIcon.addEventListener('click', () => {
    currentTheme = (currentTheme + 1) % themes.length;
    applyTheme(currentTheme);
    localStorage.setItem('themeIndex', currentTheme);
});
</script>