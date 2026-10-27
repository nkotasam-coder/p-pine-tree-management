<?php
session_start();
include 'conn.php';
include 'auth.php';
require_roles(['admin', 'naeb_officer']);
$id = (int) ($_GET['id'] ?? 0);
if (!$id || $id === (int) $_SESSION['user_id'])
    redirect('users.php');
if ($_SESSION['role'] === 'naeb_officer' && !(int) scalar($con, "SELECT COUNT(*) FROM users WHERE user_id=$id AND role IN ('forestry_officer','naeb_officer')"))
    redirect('users.php');
$ok = mysqli_query($con, "DELETE FROM users WHERE user_id=$id");
flash($ok ? 'success' : 'danger', $ok ? 'User deleted.' : 'User cannot be deleted while linked records exist.');
redirect('users.php');
?>
