<?php
session_start();
include 'conn.php';
include 'auth.php';
require_roles(['admin', 'naeb_officer']);
$allowed_roles = $_SESSION['role'] === 'naeb_officer'
    ? ['forestry_officer', 'naeb_officer']
    : ['admin', 'forestry_officer', 'naeb_officer', 'owner', 'buyer'];
$user = ['user_id' => '', 'full_name' => '', 'email' => '', 'phone' => '', 'role' => $allowed_roles[0]];
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $result = mysqli_query($con, "SELECT * FROM users WHERE user_id=$id");
    if ($result && mysqli_num_rows($result))
        $user = mysqli_fetch_assoc($result);
    else
        redirect('users.php');
    if (!in_array($user['role'], $allowed_roles, true))
        redirect('users.php');
}
$page_title = $user['user_id'] ? 'Edit user' : 'Add user';
include 'includes/header.php';
include 'includes/navbar.php';
?>
<h1 class="page-title h2 mb-3"><?= e($page_title) ?></h1>
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="post" action="user_save.php">
            <input type="hidden" name="user_id" value="<?= e($user['user_id']) ?>">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Full name</label><input class="form-control"
                        name="full_name" value="<?= e($user['full_name']) ?>" required></div>
                <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email"
                        name="email" value="<?= e($user['email']) ?>" required></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone"
                        value="<?= e($user['phone']) ?>" required></div>
                <div class="col-md-6"><label class="form-label">Role</label><select class="form-select" name="role"
                        required><?php foreach ($allowed_roles as $r): ?>
                            <option value="<?= $r ?>" <?= $user['role'] === $r ? 'selected' : '' ?>>
                                <?= e(ucwords(str_replace('_', ' ', $r))) ?></option><?php endforeach; ?>
                    </select></div>
                <div class="col-12"><label class="form-label">Password
                        <?= $user['user_id'] ? '(leave blank to keep current password)' : '' ?></label><input
                        class="form-control" type="password" name="password" minlength="8"
                        <?= $user['user_id'] ? '' : 'required' ?>></div>
            </div>
            <div class="mt-4"><button class="btn btn-success" name="save">Save user</button> <a class="btn btn-light"
                    href="users.php">Cancel</a></div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
