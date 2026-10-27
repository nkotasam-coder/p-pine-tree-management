<?php
session_start();
include 'conn.php';
include 'auth.php';
require_roles(['forestry_officer', 'owner']);
$where = $_SESSION['role'] === 'owner' ? 'WHERE p.owner_id=' . (int) $_SESSION['user_id'] : '';
$rows = mysqli_query($con, "SELECT p.*,u.full_name owner_name,COALESCE(SUM(t.quantity),0) tree_count FROM plantations p JOIN users u ON u.user_id=p.owner_id LEFT JOIN trees t ON t.plantation_id=p.plantation_id $where GROUP BY p.plantation_id ORDER BY p.created_at DESC");
$page_title = 'Plantations';
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title h2 mb-1"><?= $_SESSION['role'] === 'owner' ? 'My plantations' : 'Plantations' ?></h1>
        <p class="text-muted mb-0">Registered land and ownership records.</p>
    </div><?php if ($_SESSION['role'] === 'owner'): ?><a class="btn btn-success" href="requests.php"><i class="bi bi-plus-lg"></i> Use approved planting request</a><?php elseif ($_SESSION['role'] === 'forestry_officer'): ?><span class="text-muted">Owners register plantations after approval</span><?php endif; ?>
</div>
<div class="table-responsive shadow-sm">
    <table class="table table-striped table-hover table-bordered mb-0">
        <thead>
            <tr>
                <th>Name</th>
                <th>Owner</th>
                <th>Location</th>
                <th>Area</th>
                <th>Trees</th>
                <th>Coordinates</th><?php if ($_SESSION['role'] !== 'owner'): ?>
                    <th>Actions</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while ($p = mysqli_fetch_assoc($rows)): ?>
                <tr>
                    <td><?= e($p['plantation_name']) ?></td>
                    <td><?= e($p['owner_name']) ?></td>
                    <td><?= e($p['sector'] . ', ' . $p['district']) ?></td>
                    <td><?= e($p['area_size']) ?> ha</td>
                    <td><?= number_format($p['tree_count']) ?></td>
                    <td><?= e($p['latitude'] . ', ' . $p['longitude']) ?></td>
                    <?php if ($_SESSION['role'] !== 'owner'): ?>
                        <td><a class="btn btn-sm btn-outline-success"
                                href="plantation_form.php?id=<?= (int) $p['plantation_id'] ?>">Edit</a> <a
                                class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Delete this plantation and its tree records?')"
                                href="plantation_delete.php?id=<?= (int) $p['plantation_id'] ?>">Delete</a></td><?php endif; ?>
                </tr><?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include 'includes/footer.php'; ?>
