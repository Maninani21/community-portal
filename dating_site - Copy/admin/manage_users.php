<?php
// admin/manage_users.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
if (!is_logged_in()) header('Location:/public/login.php');
$user = current_user($pdo);
if (!$user || !$user['is_admin']) { echo "Access denied"; exit; }

$stmt = $pdo->query("SELECT id,name,email,is_verified,created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Manage Users</title><link rel="stylesheet" href="/public/css/style.css"></head><body>
<div class="container">
<h1>Users</h1>
<?php foreach($users as $u): ?>
  <div class="card">
    <?php echo e($u['name']); ?> — <?php echo e($u['email']); ?> — <?php echo e($u['created_at']); ?>
    <a href="/admin/toggle_verify.php?id=<?php echo $u['id']; ?>"><?php echo $u['is_verified'] ? 'Unverify' : 'Verify'; ?></a>
  </div>
<?php endforeach; ?>
</div>
</body></html>
