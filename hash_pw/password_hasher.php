<?php
$password = '12345678'; // Replace with the desired password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo $hashed_password;
?>

<!-- hash_pw/password_hasher -->