<?php include 'header.php'; ?>

<!-- Theme Switcher Icon -->
<button id="theme-icon" 
        style="position:fixed; top:20px; right:20px; z-index:1000; background:#fff; border:none; padding:10px; border-radius:50%; box-shadow:0 2px 5px rgba(0,0,0,0.3); cursor:pointer;" 
        title="Change Background">
    🎨
</button>

<?php
// Fetch dashboard stats
$totalPosts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$totalComments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalViews = $pdo->query("SELECT COUNT(*) FROM analytics")->fetchColumn();

// Fetch all posts for table preview
$posts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC")->fetchAll();
?>

<div class="container-fluid mt-4">
    <h1 class="mb-4">Admin Dashboard</h1>

    <!-- Quick Access Links -->
    <div class="mb-4 d-flex flex-wrap gap-3">
        <a href="posts.php" class="btn btn-primary"><i class="bi bi-file-post"></i> Posts</a>
        <a href="comments.php" class="btn btn-success"><i class="bi bi-chat-dots"></i> Comments</a>
        <a href="users.php" class="btn btn-info"><i class="bi bi-people"></i> Users</a>
        <a href="categories.php" class="btn btn-warning"><i class="bi bi-tags"></i> Categories</a>
        <a href="analytics.php" class="btn btn-secondary"><i class="bi bi-bar-chart"></i> Analytics</a>
        <a href="user_settings.php" class="btn btn-light btn-sm mt-2"><i class="bi bi-people"></i> Manage</a>
    </div>

    <!-- Dashboard Cards -->
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <h5 class="card-title">Posts</h5>
                    <p class="fs-3"><?= $totalPosts ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <h5 class="card-title">Comments</h5>
                    <p class="fs-3"><?= $totalComments ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info h-100">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <p class="fs-3"><?= $totalUsers ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning h-100">
                <div class="card-body">
                    <h5 class="card-title">Categories</h5>
                    <p class="fs-3"><?= $totalCategories ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics & Top Posts -->
    <div class="row g-4 mt-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">Total Page Views</div>
                <div class="card-body fs-4"><?= $totalViews ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">Top 5 Posts</div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php 
                        $popularPosts = $pdo->query("SELECT p.title, COUNT(a.id) AS views FROM posts p LEFT JOIN analytics a ON p.id=a.post_id GROUP BY p.id ORDER BY views DESC LIMIT 5")->fetchAll();
                        foreach($popularPosts as $p): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= sanitize($p['title']) ?>
                                <span class="badge bg-primary rounded-pill"><?= $p['views'] ?> views</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Posts Table Preview -->
    <div class="card mt-5">
        <div class="card-header bg-dark text-white">Posts Preview (with Video)</div>
        <div class="card-body">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Image</th>
                        <th>Video</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($posts as $p): ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td><?= sanitize($p['title']) ?></td>
                            <td>
                                <?php if(!empty($p['image'])): ?>
                                    <img src="/assets/images/<?= sanitize($p['image']) ?>" width="80" class="img-thumbnail">
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!empty($p['video_file']) && file_exists(__DIR__ . '/../assets/videos/' . $p['video_file'])): ?>
                                    <video width="120" controls>
                                        <source src="/assets/videos/<?= sanitize($p['video_file']) ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php elseif(!empty($p['video_link'])): ?>
                                    <a href="<?= sanitize($p['video_link']) ?>" target="_blank">View Link</a>
                                <?php endif; ?>
                            </td>
                            <td><?= ucfirst($p['status']) ?></td>
                            <td><?= $p['created_at'] ?></td>
                            <td>
                                <a href="edit_post.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete_post.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include 'footer.php'; ?>

<script>
// Theme switcher
const themeIcon = document.getElementById('theme-icon');
const body = document.body;

const colors = ['#FFFFFF','#000000'];
let currentColorIndex = 0;

// Load saved color
const savedColor = localStorage.getItem('bgColor');
if(savedColor){
    body.style.backgroundColor = savedColor;
}

// Change color on click
themeIcon.addEventListener('click', () => {
    currentColorIndex = (currentColorIndex + 1) % colors.length;
    body.style.backgroundColor = colors[currentColorIndex];
    localStorage.setItem('bgColor', colors[currentColorIndex]);
});
</script>
