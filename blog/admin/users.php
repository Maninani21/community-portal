<?php include 'header.php'; ?>
<!-- Theme Switcher Icon -->
<button id="theme-icon" 
        style="position:fixed; top:20px; right:20px; z-index:1000; background:#fff; border:none; padding:10px; border-radius:50%; box-shadow:0 2px 5px rgba(0,0,0,0.3); cursor:pointer;" 
        title="Change Background">
    🎨
</button>

<?php
// Handle Add/Edit
$editUser = null; // For pre-filling form

if(isset($_GET['edit'])){
    $editId = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$editId]);
    $editUser = $stmt->fetch();
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'] ?? '';
    $id = $_POST['id'] ?? null;

    if($password) $passHash = password_hash($password,PASSWORD_BCRYPT);

    if($id){
        if($password){
            $stmt=$pdo->prepare("UPDATE users SET name=?, email=?, role=?, password=? WHERE id=?");
            $stmt->execute([$name,$email,$role,$passHash,$id]);
        } else {
            $stmt=$pdo->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
            $stmt->execute([$name,$email,$role,$id]);
        }
    } else {
        $stmt=$pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
        $stmt->execute([$name,$email,password_hash($password,PASSWORD_BCRYPT),$role]);
    }
    redirect('users.php');
}

// Fetch users
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

// Delete
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
    redirect('users.php');
}
?>

<div class="container-fluid">
<h1>Users</h1>

<!-- Add/Edit Form -->
<form method="POST" class="row g-3 mb-4">
<div class="col-md-4"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required value="<?= $editUser ? sanitize($editUser['name']) : '' ?>"></div>
<div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required value="<?= $editUser ? sanitize($editUser['email']) : '' ?>"></div>
<div class="col-md-2"><label class="form-label">Role</label>
<select name="role" class="form-select">
<option value="admin" <?= $editUser && $editUser['role']=='admin'?'selected':'' ?>>Admin</option>
<option value="editor" <?= $editUser && $editUser['role']=='editor'?'selected':'' ?>>Editor</option>
<option value="user" <?= !$editUser || $editUser['role']=='user'?'selected':'' ?>>User</option>
</select></div>
<div class="col-md-2"><label class="form-label">Password</label><input type="password" name="password" class="form-control"></div>
<input type="hidden" name="id" value="<?= $editUser ? $editUser['id'] : '' ?>">
<div class="col-12"><button type="submit" class="btn btn-success"><?= $editUser ? 'Update User' : 'Save User' ?></button>
<?php if($editUser): ?><a href="users.php" class="btn btn-secondary">Cancel</a><?php endif; ?></div>
</form>

<!-- Table -->
<div class="table-responsive">
<table class="table table-striped table-bordered">
<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
<?php foreach($users as $u): ?>
<tr>
<td><?= $u['id'] ?></td>
<td><?= sanitize($u['name']) ?></td>
<td><?= sanitize($u['email']) ?></td>
<td><?= $u['role'] ?></td>
<td>
<a href="?edit=<?= $u['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
<a href="?delete=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>
<?php include 'footer.php'; ?>
<script>
// Theme switcher
const themeIcon = document.getElementById('theme-icon');
const body = document.body;
const colors = ['#FFFFFF','#000000'];
let currentColorIndex = 0;
const savedColor = localStorage.getItem('bgColor');
if(savedColor) body.style.backgroundColor = savedColor;
themeIcon.addEventListener('click', () => {
    currentColorIndex = (currentColorIndex + 1) % colors.length;
    body.style.backgroundColor = colors[currentColorIndex];
    localStorage.setItem('bgColor', colors[currentColorIndex]);
});
</script>

