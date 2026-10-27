<?php
session_start();
include 'conn.php';
include 'auth.php';
require_roles(['admin']);
$role = $_SESSION['role'];
$allowed = ['plantation', 'owner', 'payment', 'timber_value', 'requests'];
$type = $_GET['type'] ?? $allowed[0];
if ($type === 'timber')
    $type = 'timber_value';
if (!in_array($type, $allowed, true)) {
    $type = $allowed[0];
    flash('danger', 'That report is not available for your role.');
}
$headers = [];
$data = [];
$title = '';
if ($type === 'plantation') {
    $title = 'Plantation report';
    $headers = ['Plantation', 'Owner', 'District', 'Sector', 'Area (ha)', 'Tree quantity'];
    $q = mysqli_query($con, "SELECT p.plantation_name,u.full_name,p.district,p.sector,p.area_size,COALESCE(SUM(t.quantity),0) trees FROM plantations p JOIN users u ON u.user_id=p.owner_id LEFT JOIN trees t ON t.plantation_id=p.plantation_id GROUP BY p.plantation_id ORDER BY p.district,p.plantation_name");
    while ($r = mysqli_fetch_assoc($q))
        $data[] = array_values($r);
} elseif ($type === 'payment') {
    $title = 'Payment report';
    $headers = ['Date', 'Owner', 'Plantation', 'Amount', 'Method', 'Status'];
    $q = mysqli_query($con, "SELECT pay.payment_date,u.full_name,p.plantation_name,pay.amount,pay.payment_method,pay.status FROM payments pay JOIN users u ON u.user_id=pay.owner_id JOIN plantations p ON p.plantation_id=pay.plantation_id ORDER BY pay.payment_date DESC");
    while ($r = mysqli_fetch_assoc($q)) {
        $r['amount'] = money($r['amount']);
        $data[] = array_values($r);
    }
} elseif ($type === 'timber_value') {
    $title = 'Timber value report';
    $headers = ['Plantation', 'Owner', 'Species', 'Quantity', 'Age', 'Status', 'Estimated value'];
    $q = mysqli_query($con, "SELECT p.plantation_name,u.full_name,t.species,t.quantity,t.age_years,t.growth_status,t.estimated_value FROM trees t JOIN plantations p ON p.plantation_id=t.plantation_id JOIN users u ON u.user_id=p.owner_id ORDER BY t.estimated_value DESC");
    while ($r = mysqli_fetch_assoc($q)) {
        $r['estimated_value'] = money($r['estimated_value']);
        $data[] = array_values($r);
    }
} elseif ($type === 'requests') {
    $title = 'Forest requests report';
    $headers = ['Requester', 'Role', 'Type', 'Plantation', 'Quantity', 'Status', 'Created'];
    $q = mysqli_query($con, "SELECT u.full_name,u.role,fr.request_type,p.plantation_name,fr.quantity,fr.status,fr.created_at FROM forest_requests fr JOIN users u ON u.user_id=fr.requester_id LEFT JOIN plantations p ON p.plantation_id=fr.plantation_id ORDER BY fr.created_at DESC");
    while ($r = mysqli_fetch_assoc($q)) {
        $data[] = [
            $r['full_name'],
            str_replace('_', ' ', $r['role']),
            str_replace('_', ' ', $r['request_type']),
            $r['plantation_name'] ?: 'New planting',
            number_format((int) $r['quantity']),
            ucfirst($r['status']),
            date('d M Y', strtotime($r['created_at']))
        ];
    }
} else {
    $title = 'Owner report';
    $headers = ['Owner', 'Email', 'Phone', 'Plantations', 'Total area (ha)', 'Approved payments'];
    $q = mysqli_query($con, "SELECT u.full_name,u.email,u.phone,(SELECT COUNT(*) FROM plantations p WHERE p.owner_id=u.user_id) plantations,COALESCE((SELECT SUM(p.area_size) FROM plantations p WHERE p.owner_id=u.user_id),0) area,COALESCE((SELECT SUM(pay.amount) FROM payments pay WHERE pay.owner_id=u.user_id AND pay.status='approved'),0) paid FROM users u WHERE u.role='owner' ORDER BY u.full_name");
    while ($r = mysqli_fetch_assoc($q)) {
        $r['paid'] = money($r['paid']);
        $data[] = array_values($r);
    }
}
$report_type = db_escape($con, $type);
$uid = (int) $_SESSION['user_id'];
mysqli_query($con, "INSERT INTO reports(report_type,generated_by) VALUES('$report_type',$uid)");
$page_title = 'Reports';
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title h2"><?= e($title) ?></h1>
        <p class="text-muted mb-0">Generated <?= e(date('d M Y, H:i')) ?></p>
    </div><button onclick="window.print()" class="btn btn-success no-print"><i class="bi bi-printer"></i> Print
        report</button>
</div>
<div class="btn-group mb-3 no-print"><?php foreach ($allowed as $r): ?><a
            class="btn btn-outline-success <?= $type === $r ? 'active' : '' ?>"
            href="reports.php?type=<?= $r ?>"><?= e(ucwords(str_replace('_', ' ', $r))) ?></a><?php endforeach; ?></div>
<div class="table-responsive shadow-sm">
    <table class="table table-striped table-hover table-bordered mb-0">
        <thead>
            <tr><?php foreach ($headers as $h): ?>
                    <th><?= e($h) ?></th><?php endforeach; ?>
            </tr>
        </thead>
        <tbody><?php foreach ($data as $row): ?>
                <tr><?php foreach ($row as $cell): ?>
                        <td><?= e($cell) ?></td><?php endforeach; ?>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include 'includes/footer.php'; ?>
