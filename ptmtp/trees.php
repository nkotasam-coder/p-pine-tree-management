<?php
session_start();
include 'conn.php';
include 'auth.php';
require_roles(['forestry_officer', 'naeb_officer', 'owner', 'buyer']);
$where = $_SESSION['role'] === 'owner' ? 'WHERE p.owner_id=' . (int) $_SESSION['user_id'] : '';
if ($_SESSION['role'] === 'buyer') $where = "WHERE t.growth_status IN ('mature','ready_for_harvest') AND t.sale_quantity > t.reserved_quantity AND t.unit_price > 0";
$rows = mysqli_query($con, "SELECT t.*,p.plantation_name,u.full_name owner_name FROM trees t JOIN plantations p ON p.plantation_id=t.plantation_id JOIN users u ON u.user_id=p.owner_id $where ORDER BY p.plantation_name,t.tree_id DESC");
$page_title = 'Trees';
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title h2"><?= $_SESSION['role'] === 'buyer' ? 'Available trees' : 'Tree records' ?></h1>
</div>
<div class="table-responsive shadow-sm">
    <table class="table table-striped table-hover table-bordered mb-0">
        <thead>
            <tr>
                <th>Plantation</th>
                <th>Owner</th>
                <th>Species</th>
                <th>Quantity</th>
                <th>Age</th>
                <th>Status</th>
                <th>Price / tree</th><th>Total value</th><?php if (in_array($_SESSION['role'], ['forestry_officer','buyer'], true)): ?>
                    <th>Action</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while ($t = mysqli_fetch_assoc($rows)): ?>
                <tr>
                    <td><?= e($t['plantation_name']) ?></td>
                    <td><?= e($t['owner_name']) ?></td>
                    <td><?= e($t['species']) ?></td>
                    <td><?= number_format($_SESSION['role']==='buyer' ? $t['sale_quantity']-$t['reserved_quantity'] : $t['quantity']) ?><?= $_SESSION['role']==='owner' ? '<br><small class="text-muted">'.number_format($t['sale_quantity']).' approved for sale, '.number_format($t['reserved_quantity']).' reserved</small>' : '' ?></td>
                    <td><?= (int) $t['age_years'] ?> years</td>
                    <td><span
                            class="badge text-bg-<?= in_array($t['growth_status'], ['mature', 'ready_for_harvest']) ? 'success' : 'secondary' ?>"><?= e(str_replace('_', ' ', $t['growth_status'])) ?></span>
                    </td>
                    <td><?= $t['unit_price'] > 0 ? e(money($t['unit_price'])) : '<span class="text-muted">Not priced</span>' ?></td>
                    <td><?= e(money($t['estimated_value'])) ?></td><?php if (in_array($_SESSION['role'], ['forestry_officer','buyer'], true)): ?>
                        <td><?php if ($_SESSION['role'] === 'buyer'): ?><a class="btn btn-sm btn-success" href="request_form.php?tree_id=<?= (int) $t['tree_id'] ?>">Request purchase</a><?php else: ?><a class="btn btn-sm btn-outline-success" href="tree_form.php?id=<?= (int) $t['tree_id'] ?>">Edit</a><?php endif; ?>
                        </td><?php endif; ?>
                </tr><?php endwhile; ?>
        </tbody>
    </table>
</div><?php include 'includes/footer.php'; ?>
