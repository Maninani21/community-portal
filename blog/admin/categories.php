<?php include 'header.php'; ?>
<!-- Theme Switcher Icon -->
<button id="theme-icon" 
        style="position:fixed; top:20px; right:20px; z-index:1000; background:#fff; border:none; padding:10px; border-radius:50%; box-shadow:0 2px 5px rgba(0,0,0,0.3); cursor:pointer;" 
        title="Change Background">
    🎨
</button>

<?php
// Handle Add/Edit
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name = $_POST['name'];
    $slug = slugify($name);
    $id = $_POST['id'] ?? null;

    if($id){
        $stmt=$pdo->prepare("UPDATE categories SET name=?, slug=? WHERE id=?");
        $stmt->execute([$name,$slug,$id]);
    } else {
        $stmt=$pdo->prepare("INSERT INTO categories (name,slug) VALUES (?,?)");
        $stmt->execute([$name,$slug]);
    }
    redirect('categories.php');
}

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY created_at DESC")->fetchAll();

// Delete
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
    redirect('categories.php');
}
?>

<div class="container-fluid">
<h1>Categories</h1>

<!-- Add/Edit Form -->
<form method="POST" class="row g-3 mb-4">
<div class="col-md-6">
<label class="form-label">Category Name</label>
<input type="text" name="name" class="form-control" required>
<input type="hidden" name="id">
</div>
<div class="col-md-6 align-self-end">
<button type="submit" class="btn btn-success">Save Category</button>
</div>
</form>

<!-- Table -->
<div class="table-responsive">
<table class="table table-striped table-bordered">
<tr><th>ID</th><th>Name</th><th>Slug</th><th>Actions</th></tr>
<?php foreach($categories as $c): ?>
<tr>
<td><?= $c['id'] ?></td>
<td><?= sanitize($c['name']) ?></td>
<td><?= sanitize($c['slug']) ?></td>
<td>
<a href="?edit=<?= $c['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
<a href="?delete=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
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
