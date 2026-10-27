<?php
session_start();
include 'conn.php';
include 'auth.php';

if (isset($_SESSION['user_id']))
  redirect('dashboard.php');
$error = '';
$needs_admin = (int) scalar($con, "SELECT COUNT(*) FROM users WHERE role='admin'") === 0;
if (isset($_POST['login'])) {
  $email = db_escape($con, $_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $result = mysqli_query($con, "SELECT * FROM users WHERE email='$email' LIMIT 1");
  $user = $result ? mysqli_fetch_assoc($result) : null;
  if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['user_id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];
    redirect('dashboard.php');
  }
  $error = 'Invalid email or password.';
}
$page_title = 'Login';
include 'includes/header.php';
?>
<div
  class="auth-shell d-flex align-items-center justify-content-center position-fixed top-0 start-0 end-0 bottom-0 p-3">
  <div  class="card auth-card w-100 p-3 p-md-4">
    <div class="card-body">
      <div class="text-center mb-4"><i class="bi bi-tree-fill text-success display-3"></i>
        <h1 class="h3 mt-2">Welcome to PTMPT</h1>
        <p class="text-muted">Manage plantations and payments securely.</p>
      </div>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <form method="post">
        <div class="mb-3"><label class="form-label">Email address</label><input class="form-control" type="email"
            name="email" required autofocus></div>
        <div class="mb-3"><label class="form-label">Password</label><input class="form-control" type="password"
            name="password" required></div>
        <button class="btn btn-success w-100" name="login">Sign in</button>
      </form>
      <p class="text-center mt-3 mb-0">Plantation owner? <a class="text-success" href="register.php">Create an
          account</a></p>
      <?php if ($needs_admin): ?>
        <p class="text-center mt-2 mb-0"><a class="text-danger" href="setup_admin.php">Set up the first administrator</a>
        </p><?php endif; ?>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>