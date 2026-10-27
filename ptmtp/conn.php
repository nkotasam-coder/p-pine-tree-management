<?php
$con = mysqli_connect('localhost', 'root', '', 'ptmpt');
if (!$con) { die("Connection failed: " . mysqli_connect_error()); }
mysqli_set_charset($con, 'utf8mb4');
?>
