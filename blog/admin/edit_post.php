<?php include 'header.php'; ?>

<!-- Theme Switcher Icon -->
<button id="theme-icon" 
        style="position:fixed; top:20px; right:20px; z-index:1000; background:#fff; border:none; padding:10px; border-radius:50%; box-shadow:0 2px 5px rgba(0,0,0,0.3); cursor:pointer;" 
        title="Change Background">
    🎨
</button>

<?php
$id = $_GET['id'] ?? null;
$title = $content = $category_id = $status = $image = $video_link = $video_file = $tags = $meta_title = $meta_description = $post_type = '';
$featured = 0;

// Fetch post if editing
if($id){
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id=?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if($post){
        $title = $post['title'];
        $content = $post['content'];
        $category_id = $post['category_id'];
        $status = $post['status'];
        $image = $post['image'];
        $video_link = $post['video_link'] ?? '';
        $video_file = $post['video_file'] ?? '';
        $tags = $post['tags'] ?? '';
        $meta_title = $post['meta_title'] ?? '';
        $meta_description = $post['meta_description'] ?? '';
        $featured = $post['featured'] ?? 0;
        $post_type = $post['post_type'] ?? 'general';
    }
}

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

// Post types array
$post_types = ['general', 'news', 'announcement', 'tutorial', 'event'];

// Handle full admin form submission
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['full_admin_form'])){
    $title = trim($_POST['title']);
    $content = $_POST['content'];
    $category_id = $_POST['category_id'];
    $status = $_POST['status'];
    $slug = slugify($title);
    $tags = $_POST['tags'];
    $meta_title = $_POST['meta_title'];
    $meta_description = $_POST['meta_description'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $video_link = $_POST['video_link'];
    $post_type = $_POST['post_type'] ?? 'general';

    // Image upload
    $img_name = $image;
    if(isset($_FILES['image']) && $_FILES['image']['name'] != ''){
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $img_name = uniqid().'.'.$ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "../assets/images/".$img_name);
    }

    // Video upload
    $video_name = $video_file;
    if(isset($_FILES['video_file']) && $_FILES['video_file']['name'] != ''){
        $ext = pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION);
        $video_name = uniqid().'.'.$ext;
        move_uploaded_file($_FILES['video_file']['tmp_name'], "../assets/videos/".$video_name);
    }

    if($id){
        // Update post
        $stmt = $pdo->prepare("UPDATE posts SET title=?, slug=?, content=?, category_id=?, status=?, image=?, video_link=?, video_file=?, tags=?, meta_title=?, meta_description=?, featured=?, post_type=? WHERE id=?");
        $stmt->execute([$title, $slug, $content, $category_id, $status, $img_name, $video_link, $video_name, $tags, $meta_title, $meta_description, $featured, $post_type, $id]);
    } else {
        // Insert new post
        $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, category_id, author_id, status, image, video_link, video_file, tags, meta_title, meta_description, featured, post_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $content, $category_id, $_SESSION['admin_id'], $status, $img_name, $video_link, $video_name, $tags, $meta_title, $meta_description, $featured, $post_type]);
    }

    redirect('posts.php');
}
?>

<div class="container-fluid mt-4">
    <!-- FULL ADMIN FORM -->
    <h2><?= $id ? 'Edit' : 'Add' ?> Post</h2>
    <form method="POST" enctype="multipart/form-data" class="row g-3">
        <input type="hidden" name="full_admin_form" value="1">

        <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?= sanitize($title) ?>" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">Select Category</option>
                <?php foreach($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $category_id==$c['id']?'selected':'' ?>><?= sanitize($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Post Type</label>
            <select name="post_type" class="form-select" required>
                <?php foreach($post_types as $type): ?>
                <option value="<?= $type ?>" <?= $post_type==$type?'selected':'' ?>><?= ucfirst($type) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-control" rows="6"><?= sanitize($content) ?></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label">Upload Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            <?php if($image): ?><img src="../assets/images/<?= $image ?>" width="120" class="mt-2"><?php endif; ?>
        </div>

        <div class="col-md-6">
            <label class="form-label">Upload Video (MP4/WebM)</label>
            <input type="file" name="video_file" class="form-control" accept="video/*">
            <?php if($video_file): ?>
                <video width="200" controls class="mt-2">
                    <source src="../assets/videos/<?= $video_file ?>" type="video/mp4">
                </video>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label class="form-label">Video Link (YouTube/Vimeo)</label>
            <input type="text" name="video_link" class="form-control" value="<?= sanitize($video_link) ?>" placeholder="https://youtube.com/...">
            <?php if($video_link): ?>
                <iframe width="200" height="120" src="<?= $video_link ?>" frameborder="0" allowfullscreen class="mt-2"></iframe>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label class="form-label">Tags (comma separated)</label>
            <input type="text" name="tags" class="form-control" value="<?= sanitize($tags) ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">SEO Title</label>
            <input type="text" name="meta_title" class="form-control" value="<?= sanitize($meta_title) ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">SEO Description</label>
            <textarea name="meta_description" class="form-control" rows="2"><?= sanitize($meta_description) ?></textarea>
        </div>

        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="draft" <?= $status=='draft'?'selected':'' ?>>Draft</option>
                <option value="published" <?= $status=='published'?'selected':'' ?>>Published</option>
            </select>
        </div>

        <div class="col-md-3">
            <div class="form-check mt-4">
                <input type="checkbox" name="featured" class="form-check-input" <?= $featured?'checked':'' ?>>
                <label class="form-check-label">Featured Post</label>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-success"><?= $id ? 'Update' : 'Add' ?> Post</button>
        </div>
    </form>

    <hr class="my-5">

    <!-- QUICK ADD FORM -->
    <h2>Quick Add Post</h2>
    <form action="save_post.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Title" required class="form-control mb-2">
        <textarea name="content" placeholder="Content" required class="form-control mb-2"></textarea>
        <input type="file" name="image" class="form-control mb-2">
        <input type="file" name="video_file" accept="video/mp4,video/webm,video/ogg" class="form-control mb-2">
        <input type="text" name="video_link" placeholder="Or YouTube Link" class="form-control mb-2">
        <button type="submit" class="btn btn-primary">Publish</button>
    </form>
</div>

<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script>
CKEDITOR.replace('content');

// Theme Switcher Script
const themeIcon = document.getElementById('theme-icon');
const body = document.body;
const colors = ['#FFFFFF','#000000'];
let currentColorIndex = 0;

const savedColor = localStorage.getItem('bgColor');
if(savedColor){
    body.style.backgroundColor = savedColor;
}

themeIcon.addEventListener('click', () => {
    currentColorIndex = (currentColorIndex + 1) % colors.length;
    body.style.backgroundColor = colors[currentColorIndex];
    localStorage.setItem('bgColor', colors[currentColorIndex]);
});
</script>

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
