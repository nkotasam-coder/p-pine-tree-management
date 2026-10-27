<?php
session_start();
include 'conn.php';
include 'auth.php';
require_roles(['forestry_officer', 'owner']);
$where = $_SESSION['role'] === 'owner' ? 'WHERE p.owner_id=' . (int) $_SESSION['user_id'] : '';
$rows = mysqli_query($con, "SELECT p.plantation_name,u.full_name owner_name,COALESCE(SUM(t.quantity),0) total_trees,COALESCE(SUM(t.estimated_value),0) total_value FROM plantations p JOIN users u ON u.user_id=p.owner_id LEFT JOIN trees t ON t.plantation_id=p.plantation_id $where GROUP BY p.plantation_id ORDER BY total_value DESC");
$grand = 0;
$data = [];
while ($r = mysqli_fetch_assoc($rows)) {
    $data[] = $r;
    $grand += (float) $r['total_value'];
}
$page_title = 'Timber value';
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title h2">Timber value</h1>
        <p class="text-muted mb-0">Current estimates based on recorded quantity and age.</p>
    </div>
    <div class="text-end"><small class="text-muted">Total estimated value</small>
        <div class="h4 text-success"><?= e(money($grand)) ?></div>
    </div>
</div>
<div class="table-responsive shadow-sm">
    <table class="table table-striped table-hover table-bordered mb-0">
        <thead>
            <tr>
                <th>Plantation</th>
                <th>Owner</th>
                <th>Total trees</th>
                <th>Estimated value</th>
            </tr>
        </thead>
        <tbody><?php foreach ($data as $r): ?>
                <tr>
                    <td><?= e($r['plantation_name']) ?></td>
                    <td><?= e($r['owner_name']) ?></td>
                    <td><?= number_format($r['total_trees']) ?></td>
                    <td><?= e(money($r['total_value'])) ?></td>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include 'includes/footer.php'; ?>
