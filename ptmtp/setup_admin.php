<?php
session_start();
include 'conn.php';
include 'auth.php';

if ((int) scalar($con, "SELECT COUNT(*) FROM users WHERE role='admin'") > 0) {
    flash('danger', 'Administrator setup has already been completed.');
    redirect('login.php');
}

$error = '';
if (isset($_POST['create_admin'])) {
    $name = db_escape($con, $_POST['full_name'] ?? '');
    $email = db_escape($con, $_POST['email'] ?? '');
    $phone = db_escape($con, $_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $hash = db_escape($con, password_hash($password, PASSWORD_DEFAULT));
        $ok = mysqli_query($con, "INSERT INTO users(full_name,email,phone,password,role) VALUES('$name','$email','$phone','$hash','admin')");
        if ($ok) {
            flash('success', 'Administrator created. You can now sign in.');
            redirect('login.php');
        }
        $error = 'Could not create the administrator.';
    }
}

$page_title = 'Administrator setup';
include 'includes/header.php';
?>
<div
    class="auth-shell d-flex align-items-center justify-content-center position-fixed top-0 start-0 end-0 bottom-0 p-3 overflow-auto">
    <div class="card auth-card w-100 p-3 my-4">
        <div class="card-body">
            <h1 class="h3 text-center mb-1">First administrator</h1>
            <p class="text-muted text-center mb-4">This setup closes permanently after the first admin is created.</p>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <form method="post">
                <div class="mb-3"><label class="form-label">Full name</label><input class="form-control"
                        name="full_name" required></div>
                <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email"
                        name="email" required></div>
                <div class="mb-3"><label class="form-label">Phone</label><input class="form-control" name="phone"
                        required></div>
                <div class="mb-3"><label class="form-label">Password</label><input class="form-control" type="password"
                        name="password" minlength="8" required></div>
                <button class="btn btn-success w-100" name="create_admin">Create administrator</button>
            </form>
            <p class="text-center mt-3 mb-0"><a class="text-success" href="login.php">Back to sign in</a></p>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>