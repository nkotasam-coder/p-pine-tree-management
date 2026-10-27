<?php
session_start();
include 'conn.php';
include 'auth.php';
require_roles(['forestry_officer']);
$id = (int) ($_GET['id'] ?? 0);
$ok = $id && mysqli_query($con, "DELETE FROM plantations WHERE plantation_id=$id");
flash($ok ? 'success' : 'danger', $ok ? 'Plantation deleted.' : 'Plantation cannot be deleted while payments are linked to it.');
redirect('plantations.php');
?>
