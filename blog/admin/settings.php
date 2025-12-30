<?php include 'header.php'; ?>
<!-- Theme Switcher Icon -->
<button id="theme-icon" 
        style="position:fixed; top:20px; right:20px; z-index:1000; background:#fff; border:none; padding:10px; border-radius:50%; box-shadow:0 2px 5px rgba(0,0,0,0.3); cursor:pointer;" 
        title="Change Background">
    🎨
</button>

<?php
$settings = $pdo->query("SELECT * FROM settings WHERE id=1")->fetch();
if($_SERVER['REQUEST_METHOD']==='POST'){
    $site_name=$_POST['site_name'];
    $site_description=$_POST['site_description'];
    $logo = $settings['logo'];

    if(isset($_FILES['logo']) && $_FILES['logo']['name']!=''){
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $logo = uniqid().'.'.$ext;
        move_uploaded_file($_FILES['logo']['tmp_name'], "../assets/images/".$logo);
    }

    $pdo->prepare("UPDATE settings SET site_name=?, site_description=?, logo=? WHERE id=1")
        ->execute([$site_name,$site_description,$logo]);
    redirect('settings.php');
}
?>

<div class="container-fluid">
<h1>Site Settings</h1>
<form method="POST" enctype="multipart/form-data" class="row g-3">
<div class="col-md-6"><label class="form-label">Site Name</label><input type="text" name="site_name" class="form-control" value="<?= sanitize($settings['site_name']) ?>" required></div>
<div class="col-md-6"><label class="form-label">Logo</label><input type="file" name="logo" class="form-control" accept="image/*">
<?php if($settings['logo']): ?><img src="../assets/images/<?= $settings['logo'] ?>" width="100" class="mt-2"><?php endif; ?></div>
<div class="col-12"><label class="form-label">Site Description</label><textarea name="site_description" class="form-control" rows="4"><?= sanitize($settings['site_description']) ?></textarea></div>
<div class="col-12"><button type="submit" class="btn btn-success">Save Settings</button></div>
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
