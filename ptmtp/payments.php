<?php
session_start();
include 'conn.php';
include 'auth.php';
require_roles(['naeb_officer', 'owner', 'buyer']);
$conditions = [];
if ($_SESSION['role'] === 'owner')
    $conditions[] = 'pay.owner_id=' . (int) $_SESSION['user_id'];
if ($_SESSION['role'] === 'buyer')
    $conditions[] = 'pay.buyer_id=' . (int) $_SESSION['user_id'];
$status = db_escape($con, $_GET['status'] ?? '');
if (in_array($status, ['pending', 'approved', 'rejected'], true))
    $conditions[] = "pay.status='$status'";
$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
$rows = mysqli_query($con, "SELECT pay.*,u.full_name owner_name,b.full_name buyer_name,p.plantation_name,t.species FROM payments pay JOIN users u ON u.user_id=pay.owner_id LEFT JOIN users b ON b.user_id=pay.buyer_id JOIN plantations p ON p.plantation_id=pay.plantation_id LEFT JOIN trees t ON t.tree_id=pay.tree_id $where ORDER BY pay.payment_date DESC,pay.payment_id DESC");
$page_title = 'Payments';
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="page-title h2 mb-1"><?= $_SESSION['role'] === 'owner' ? 'Buyer payments' : ($_SESSION['role'] === 'buyer' ? 'My payments' : 'Payments') ?></h1>
        <p class="text-muted mb-0"><?= $_SESSION['role'] === 'owner' ? 'Approve or reject payments sent by buyers.' : 'Tree purchase payment history and owner approval status.' ?></p>
    </div><?php if ($_SESSION['role'] === 'buyer'): ?><a class="btn btn-success" href="trees.php"><i class="bi bi-cart-plus"></i> Buy trees</a><?php endif; ?>
</div>
<div class="btn-group mb-3 no-print"><a class="btn btn-outline-success <?= !$status ? 'active' : '' ?>"
        href="payments.php">All</a><?php foreach (['pending', 'approved', 'rejected'] as $s): ?><a
            class="btn btn-outline-success <?= $status === $s ? 'active' : '' ?>"
            href="payments.php?status=<?= $s ?>"><?= ucfirst($s) ?></a><?php endforeach; ?></div>
<div class="table-responsive shadow-sm">
    <table class="table table-striped table-hover table-bordered mb-0">
        <thead>
            <tr>
                <th>Date</th>
                <th>Owner</th>
                <th>Buyer</th>
                <th>Plantation</th>
                <th>Trees</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Status</th><?php if ($_SESSION['role'] === 'owner'): ?>
                    <th>Actions</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while ($p = mysqli_fetch_assoc($rows)): ?>
                <tr>
                    <td><?= e(date('d M Y', strtotime($p['payment_date']))) ?></td>
                    <td><?= e($p['owner_name']) ?></td>
                    <td><?= e($p['buyer_name'] ?: 'Legacy payment') ?></td>
                    <td><?= e($p['plantation_name']) ?></td>
                    <td><?= $p['species'] ? e(number_format($p['quantity']) . ' × ' . $p['species']) : '—' ?></td>
                    <td><?= e(money($p['amount'])) ?></td>
                    <td><?= e($p['payment_method']) ?></td>
                    <td><span
                            class="badge text-bg-<?= $p['status'] === 'approved' ? 'success' : ($p['status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= e($p['status']) ?></span>
                    </td>
                    <?php if ($_SESSION['role'] === 'owner'): ?>
                        <td><?php if ($p['status'] === 'pending' && $p['buyer_id']): ?>
                                <form class="d-inline" method="post" action="payment_save.php"><input type="hidden"
                                        name="payment_id" value="<?= (int) $p['payment_id'] ?>"><button
                                        class="btn btn-sm btn-success" name="approve">Approve</button> <button
                                        class="btn btn-sm btn-outline-danger" name="reject">Reject</button></form><?php endif; ?>
                        </td><?php endif; ?>
                </tr><?php endwhile; ?>
        </tbody>
    </table>
</div><?php include 'includes/footer.php'; ?>
