<?php
session_start();
include 'conn.php';
include 'auth.php';
require_roles(['forestry_officer', 'owner', 'buyer']);

$role = $_SESSION['role'];
$uid = (int) $_SESSION['user_id'];
$where = $role === 'forestry_officer' ? '' : "WHERE fr.requester_id=$uid";
if (isset($_GET['status']) && in_array($_GET['status'], ['pending', 'approved', 'rejected'], true)) {
    $status = db_escape($con, $_GET['status']);
    $where = $where ? "$where AND fr.status='$status'" : "WHERE fr.status='$status'";
}
$rows = mysqli_query($con, "SELECT fr.*,u.full_name requester,u.role requester_role,p.plantation_name,rv.full_name reviewer
    FROM forest_requests fr
    JOIN users u ON u.user_id=fr.requester_id
    LEFT JOIN plantations p ON p.plantation_id=fr.plantation_id
    LEFT JOIN users rv ON rv.user_id=fr.reviewed_by
    $where
    ORDER BY fr.created_at DESC");
$page_title = $role === 'forestry_officer' ? 'Forest approvals' : 'My forest requests';
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title h2 mb-1"><?= e($page_title) ?></h1>
        <p class="text-muted mb-0"><?= $role === 'forestry_officer' ? 'Approve planting, cutting, and tree buying requests.' : 'Request a planting license, cutting license, or tree purchase approval.' ?></p>
    </div>
    <?php if ($role !== 'forestry_officer'): ?><a class="btn btn-success" href="request_form.php"><i class="bi bi-plus-lg"></i> New request</a><?php endif; ?>
</div>
<div class="mb-3 d-flex gap-2 flex-wrap">
    <a class="btn btn-sm btn-outline-success" href="requests.php">All</a>
    <?php foreach (['pending', 'approved', 'rejected'] as $s): ?><a class="btn btn-sm btn-outline-success" href="requests.php?status=<?= $s ?>"><?= ucfirst($s) ?></a><?php endforeach; ?>
</div>
<div class="table-responsive shadow-sm">
    <table class="table table-striped table-hover table-bordered mb-0">
        <thead><tr>
            <th>Requester</th>
            <th>Type</th>
            <th>NAEB tree type</th>
            <th>Qty/Area</th>
            <th>Status</th>
            <th>Next stage</th>
            <th>Reason</th>
            <th>Reviewed by</th>
            <?php if ($role === 'forestry_officer'): ?><th>Actions</th><?php endif; ?>
        </tr></thead>
        <tbody>
        <?php while ($r = mysqli_fetch_assoc($rows)): ?>
            <tr>
                <td><?= e($r['requester']) ?><br><small class="text-muted"><?= e(str_replace('_', ' ', $r['requester_role'])) ?></small></td>
                <td><?= e(ucwords(str_replace('_', ' ', $r['request_type']))) ?></td>
                <td><?= e($r['species']) ?></td>
                <td><?= e($r['quantity'] ? number_format((int) $r['quantity']) . ' trees' : number_format((float) $r['area_size'], 2) . ' ha') ?></td>
                <td><span class="badge text-bg-success"><?= e(ucfirst($r['status'])) ?></span></td>
                <td><?= e(ucwords(str_replace('_',' ',$r['fulfillment_status']))) ?><?php if($role==='owner'&&$r['fulfillment_status']==='awaiting_registration'): ?><br><a class="btn btn-sm btn-success mt-1" href="plantation_form.php?request_id=<?= (int)$r['request_id'] ?>">Register plantation</a><?php endif; ?><?php if($role==='buyer'&&$r['fulfillment_status']==='awaiting_payment'): ?><br><a class="btn btn-sm btn-success mt-1" href="payment_form.php?request_id=<?= (int)$r['request_id'] ?>">Pay now</a><?php endif; ?></td>
                <td><?= e($r['reason']) ?></td>
                <td><?= e($r['reviewer'] ?: '-') ?></td>
                <?php if ($role === 'forestry_officer'): ?><td>
                    <?php if ($r['status'] === 'pending'): ?>
                        <form class="d-inline" method="post" action="request_action.php"><input type="hidden" name="request_id" value="<?= (int) $r['request_id'] ?>"><button class="btn btn-sm btn-success" name="action" value="approved">Approve</button></form>
                        <form class="d-inline" method="post" action="request_action.php"><input type="hidden" name="request_id" value="<?= (int) $r['request_id'] ?>"><button class="btn btn-sm btn-outline-danger" name="action" value="rejected">Reject</button></form>
                    <?php else: ?>Reviewed<?php endif; ?>
                </td><?php endif; ?>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include 'includes/footer.php'; ?>
