<?php include 'header.php'; ?>
<!-- Theme Switcher Icon -->
<button id="theme-icon" 
        style="position:fixed; top:20px; right:20px; z-index:1000; background:#fff; border:none; padding:10px; border-radius:50%; box-shadow:0 2px 5px rgba(0,0,0,0.3); cursor:pointer;" 
        title="Change Background">
    🎨
</button>
<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Only admin access
if(!isset($_SESSION['admin_id'])){
    header('Location: login.php');
    exit;
}

// Get user ID
$id = $_GET['id'] ?? null;
if(!$id){
    header('Location: users.php');
    exit;
}

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if(!$user){
    header('Location: users.php');
    exit;
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, phone=?, address=? WHERE id=?");
    $stmt->execute([$name, $email, $phone, $address, $id]);

    header('Location: users.php');
    exit;
}
?>

<?php include 'header.php'; ?>

<div class="container mt-4">
<h1>Edit User</h1>
<form method="POST" class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="<?= sanitize($user['name']) ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= sanitize($user['email']) ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="<?= sanitize($user['phone'] ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Address</label>
        <input type="text" name="address" class="form-control" value="<?= sanitize($user['address'] ?? '') ?>">
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-success">Update User</button>
        <a href="users.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>
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
