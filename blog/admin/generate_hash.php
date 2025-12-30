<?php
$password = 'admin123'; // The password you want for admin
$hash = password_hash($password, PASSWORD_BCRYPT);
echo $hash;
?>
