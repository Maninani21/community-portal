<?php include 'header.php'; ?>
<!-- Theme Switcher Icon -->
<button id="theme-icon" 
        style="position:fixed; top:20px; right:20px; z-index:1000; background:#fff; border:none; padding:10px; border-radius:50%; box-shadow:0 2px 5px rgba(0,0,0,0.3); cursor:pointer;" 
        title="Change Background">
    🎨
</button>

<?php
// Approve / Spam / Delete
if(isset($_GET['action'],$_GET['id'])){
    $id = $_GET['id'];
    if($_GET['action']=='delete') $pdo->prepare("DELETE FROM comments WHERE id=?")->execute([$id]);
    else $pdo->prepare("UPDATE comments SET status=? WHERE id=?")->execute([$_GET['action'],$id]);
    redirect('comments.php');
}

// Fetch comments
$comments = $pdo->query("SELECT c.*, p.title AS post_title FROM comments c LEFT JOIN posts p ON c.post_id=p.id ORDER BY c.created_at DESC")->fetchAll();
?>

<div class="container-fluid">
<h1>Comments</h1>
<div class="table-responsive">
<table class="table table-striped table-bordered">
<tr><th>ID</th><th>Post</th><th>Name</th><th>Email</th><th>Content</th><th>Status</th><th>Actions</th></tr>
<?php foreach($comments as $c): ?>
<tr>
<td><?= $c['id'] ?></td>
<td><?= sanitize($c['post_title']) ?></td>
<td><?= sanitize($c['user_name']) ?></td>
<td><?= sanitize($c['user_email']) ?></td>
<td><?= sanitize($c['content']) ?></td>
<td><?= $c['status'] ?></td>
<td>
<a href="?action=approved&id=<?= $c['id'] ?>" class="btn btn-sm btn-success">Approve</a>
<a href="?action=spam&id=<?= $c['id'] ?>" class="btn btn-sm btn-warning">Spam</a>
<a href="?action=delete&id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
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
