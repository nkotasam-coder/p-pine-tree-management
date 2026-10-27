<?php
session_start();
include 'conn.php';
include 'auth.php';
require_roles(['admin', 'naeb_officer']);
$role_filter = $_SESSION['role'] === 'naeb_officer' ? "WHERE role IN ('forestry_officer','naeb_officer')" : '';
$users = mysqli_query($con, "SELECT user_id,full_name,email,phone,role,created_at FROM users $role_filter ORDER BY created_at DESC");
$page_title = 'Users';
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title h2 mb-1"><?= $_SESSION['role'] === 'naeb_officer' ? 'Officer registration' : 'Users' ?></h1>
        <p class="text-muted mb-0"><?= $_SESSION['role'] === 'naeb_officer' ? 'Register NAEB and forestry officers.' : 'Manage all system accounts.' ?></p>
    </div><a class="btn btn-success" href="user_form.php"><i class="bi bi-plus-lg"></i>
        Add user</a>
</div>
<div class="table-responsive shadow-sm">
    <table class="table table-striped table-hover table-bordered mb-0">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($u = mysqli_fetch_assoc($users)): ?>
                <tr>
                    <td><?= e($u['full_name']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['phone']) ?></td>
                    <td><span class="badge text-bg-success"><?= e(str_replace('_', ' ', $u['role'])) ?></span></td>
                    <td><?= e(date('d M Y', strtotime($u['created_at']))) ?></td>
                    <td><a class="btn btn-sm btn-outline-success" href="user_form.php?id=<?= (int) $u['user_id'] ?>">Edit</a>
                        <?php if ((int) $u['user_id'] !== (int) $_SESSION['user_id']): ?><a
                                class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this user?')"
                                href="user_delete.php?id=<?= (int) $u['user_id'] ?>">Delete</a><?php endif; ?></td>
                </tr><?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include 'includes/footer.php'; ?>
