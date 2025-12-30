<?php include 'header.php'; ?>
<!-- Theme Switcher Icon -->
<button id="theme-icon" 
        style="position:fixed; top:20px; right:20px; z-index:1000; background:#fff; border:none; padding:10px; border-radius:50%; box-shadow:0 2px 5px rgba(0,0,0,0.3); cursor:pointer;" 
        title="Change Background">
    🎨
</button>

<?php
// Fetch posts with author & category
$posts = $pdo->query("
    SELECT p.*, u.name AS author, c.name AS category 
    FROM posts p
    LEFT JOIN users u ON p.author_id = u.id
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
")->fetchAll();

// Handle Delete (keep this above output)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Delete old image if exists
    $stmt = $pdo->prepare("SELECT image FROM posts WHERE id=?");
    $stmt->execute([$id]);
    $old = $stmt->fetch();
    if ($old && !empty($old['image']) && file_exists("../assets/images/".$old['image'])) {
        @unlink("../assets/images/".$old['image']);
    }

    $pdo->prepare("DELETE FROM posts WHERE id=?")->execute([$id]);
    redirect('posts.php');
}
?>

<div class="container-fluid">
    <h1 class="mb-3">
        Posts 
        <a href="edit_post.php" class="btn btn-primary float-end">➕ Add New Post</a>
    </h1>
    
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Image</th>
                    <th>Video</th>
                    <th>Tags</th>
                    <th>SEO</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($posts as $p): 
                // Defensive values so missing keys won't throw warnings
                $featured = isset($p['featured']) ? (int)$p['featured'] : 0;
                $image    = isset($p['image']) ? $p['image'] : '';
                $video    = isset($p['video']) ? $p['video'] : '';
                $tags     = isset($p['tags']) ? $p['tags'] : '';
                $meta_title = isset($p['meta_title']) ? $p['meta_title'] : '';
                $meta_description = isset($p['meta_description']) ? $p['meta_description'] : '';
            ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= sanitize($p['title']) ?></td>
                    <td><?= sanitize($p['category'] ?? '') ?></td>
                    <td><?= sanitize($p['author'] ?? '') ?></td>
                    <td>
                        <span class="badge bg-<?= ($p['status'] ?? '')=='published' ? 'success' : 'secondary' ?>">
                            <?= ucfirst($p['status'] ?? 'draft') ?>
                        </span>
                    </td>
                    <td>
                        <?= $featured ? '<span class="badge bg-warning text-dark">⭐ Yes</span>' : '—' ?>
                    </td>
                    <td>
                        <?php if($image): ?>
                            <img src="../assets/images/<?= sanitize($image) ?>" width="80" class="rounded">
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(!empty($video)): ?>
                            <a href="<?= sanitize($video) ?>" target="_blank" class="btn btn-sm btn-outline-info">View</a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(!empty($tags)): 
                            $tagsArr = explode(',', $tags);
                            foreach($tagsArr as $tag){
                                $tag = trim($tag);
                                if($tag !== ''): ?>
                                    <span class="badge bg-primary">#<?= sanitize(ltrim($tag, '#')) ?></span>
                                <?php endif;
                            }
                        else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($meta_title || $meta_description): ?>
                            <small><strong>T:</strong> <?= sanitize($meta_title) ?><br>
                            <strong>D:</strong> <?= sanitize(mb_strimwidth($meta_description,0,60,'...')) ?></small>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_post.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">✏ Edit</a>
                        <a href="?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this post?')">🗑 Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
<script>
// Select elements
const themeIcon = document.getElementById('theme-icon');
const body = document.body; // target entire page body

// Define colors
const colors = ['#FFFFFF','#000000'];
let currentColorIndex = 0;

// Load saved color
const savedColor = localStorage.getItem('bgColor');
if(savedColor){
    body.style.backgroundColor = savedColor;
}

// On click, change background color
themeIcon.addEventListener('click', () => {
    currentColorIndex = (currentColorIndex + 1) % colors.length;
    body.style.backgroundColor = colors[currentColorIndex];
    localStorage.setItem('bgColor', colors[currentColorIndex]);
});
</script>
