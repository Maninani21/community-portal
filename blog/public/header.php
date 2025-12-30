<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
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
  body { transition: background-color 0.3s ease; }
  .color-icon { 
    font-size: 1.5rem; 
    cursor: pointer; 
    position: fixed; 
    top: 10px; 
    right: 10px; 
    z-index: 1000;
  }
</style>
</head>
<body>

<!-- Color toggle icon -->
<i class="bi bi-circle-half color-icon" id="colorToggle"></i>

<script>
  const toggle = document.getElementById('colorToggle');
  toggle.addEventListener('click', () => {
    if(document.body.style.backgroundColor === 'black'){
      document.body.style.backgroundColor = 'white';
      document.body.style.color = 'black';
    } else {
      document.body.style.backgroundColor = 'black';
      document.body.style.color = 'white';
    }
  });
</script>

</body>
</html>
