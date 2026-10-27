<?php
session_start();
include 'conn.php'; include 'auth.php'; require_roles(['admin', 'naeb_officer']);
if (!isset($_POST['save'])) redirect('users.php');
$id = (int)($_POST['user_id'] ?? 0);
$name = db_escape($con, $_POST['full_name'] ?? '');
$email = db_escape($con, $_POST['email'] ?? '');
$phone = db_escape($con, $_POST['phone'] ?? '');
$role = db_escape($con, $_POST['role'] ?? '');
$allowed = $_SESSION['role'] === 'naeb_officer'
    ? ['forestry_officer', 'naeb_officer']
    : ['admin','forestry_officer','naeb_officer','owner','buyer'];
if (!in_array($role, $allowed, true)) redirect('users.php');
if ($id && $_SESSION['role'] === 'naeb_officer' && !(int) scalar($con, "SELECT COUNT(*) FROM users WHERE user_id=$id AND role IN ('forestry_officer','naeb_officer')"))
    redirect('users.php');
$password = $_POST['password'] ?? '';
if ($id) {
    $password_sql = '';
    if ($password !== '') {
        if (strlen($password) < 8) { flash('danger','Password must be at least 8 characters.'); redirect("user_form.php?id=$id"); }
        $hash = db_escape($con, password_hash($password, PASSWORD_DEFAULT));
        $password_sql = ", password='$hash'";
    }
    $ok = mysqli_query($con, "UPDATE users SET full_name='$name',email='$email',phone='$phone',role='$role'$password_sql WHERE user_id=$id");
} else {
    if (strlen($password) < 8) { flash('danger','Password must be at least 8 characters.'); redirect('user_form.php'); }
    $hash = db_escape($con, password_hash($password, PASSWORD_DEFAULT));
    $ok = mysqli_query($con, "INSERT INTO users(full_name,email,phone,password,role) VALUES('$name','$email','$phone','$hash','$role')");
}
flash($ok?'success':'danger', $ok?'User saved successfully.':'Could not save user. The email may already exist.');
redirect('users.php');
?>
