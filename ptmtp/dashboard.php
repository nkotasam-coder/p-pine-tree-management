<?php
session_start();
include 'conn.php';
include 'auth.php';
require_login();

$role = $_SESSION['role'];
$uid = (int) $_SESSION['user_id'];
$cards = [];
$chart_one = ['title' => '', 'subtitle' => '', 'type' => 'doughnut', 'labels' => [], 'data' => []];
$chart_two = ['title' => '', 'subtitle' => '', 'type' => 'bar', 'labels' => [], 'data' => []];
$recent = [];
$hero_text = '';

function chart_rows(mysqli $con, string $sql, string $label_key, string $value_key): array {
    $result = mysqli_query($con, $sql);
    $labels = [];
    $values = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = ucwords(str_replace('_', ' ', $row[$label_key]));
            $values[] = (float) $row[$value_key];
        }
    }
    return [$labels, $values];
}

if ($role === 'admin') {
    $hero_text = 'Manage system users and review official reports without changing field or payment records.';
    $cards = [
        ['Total users', scalar($con, "SELECT COUNT(*) FROM users"), 'people-fill', 'green', 'All registered system accounts'],
        ['Officers', scalar($con, "SELECT COUNT(*) FROM users WHERE role IN ('forestry_officer','naeb_officer')"), 'person-badge-fill', 'blue', 'NAEB and forestry officers'],
        ['Owners and buyers', scalar($con, "SELECT COUNT(*) FROM users WHERE role IN ('owner','buyer')"), 'people', 'orange', 'Public service accounts'],
        ['Reports generated', scalar($con, "SELECT COUNT(*) FROM reports"), 'file-earmark-bar-graph-fill', 'purple', 'Printed or viewed official reports']
    ];
    [$chart_one['labels'], $chart_one['data']] = chart_rows($con, "SELECT role label,COUNT(*) total FROM users GROUP BY role", 'label', 'total');
    $chart_one['title'] = 'Users by role'; $chart_one['subtitle'] = 'Platform access distribution';
    [$chart_two['labels'], $chart_two['data']] = chart_rows($con, "SELECT report_type label,COUNT(*) total FROM reports GROUP BY report_type ORDER BY total DESC LIMIT 8", 'label', 'total');
    $chart_two['title'] = 'Reports by type'; $chart_two['subtitle'] = 'Administrative report activity';
    $q = mysqli_query($con, "SELECT full_name title,CONCAT(email,' · ',REPLACE(role,'_',' ')) detail,created_at activity_date FROM users ORDER BY created_at DESC LIMIT 5");
    while ($q && $row = mysqli_fetch_assoc($q)) $recent[] = [$row['title'], $row['detail'], date('d M', strtotime($row['activity_date'])), 'person'];
} elseif ($role === 'forestry_officer') {
    $hero_text = 'Approve planting, cutting, and buying requests before forestry work is allowed.';
    $cards = [
        ['Registered plantations', scalar($con, "SELECT COUNT(*) FROM plantations"), 'tree-fill', 'green', number_format((float) scalar($con, "SELECT COALESCE(SUM(area_size),0) FROM plantations"), 2) . ' total hectares'],
        ['Pending requests', scalar($con, "SELECT COUNT(*) FROM forest_requests WHERE status='pending'"), 'clipboard-check', 'blue', 'Need forestry approval'],
        ['Approved requests', scalar($con, "SELECT COUNT(*) FROM forest_requests WHERE status='approved'"), 'check-circle-fill', 'orange', 'Licenses or purchase approvals granted'],
        ['Ready for harvest', scalar($con, "SELECT COUNT(DISTINCT plantation_id) FROM trees WHERE growth_status='ready_for_harvest'"), 'tree', 'purple', 'Requires field coordination']
    ];
    [$chart_one['labels'], $chart_one['data']] = chart_rows($con, "SELECT growth_status label,COALESCE(SUM(quantity),0) total FROM trees GROUP BY growth_status", 'label', 'total');
    $chart_one['title'] = 'Trees by growth stage'; $chart_one['subtitle'] = 'Quantity across the full lifecycle';
    [$chart_two['labels'], $chart_two['data']] = chart_rows($con, "SELECT district label,COALESCE(SUM(area_size),0) total FROM plantations GROUP BY district ORDER BY total DESC LIMIT 8", 'label', 'total');
    $chart_two['title'] = 'Area coverage by district'; $chart_two['subtitle'] = 'Registered hectares';
    $q = mysqli_query($con, "SELECT p.plantation_name title,CONCAT(t.species,' · ',t.quantity,' trees') detail,REPLACE(t.growth_status,'_',' ') value FROM trees t JOIN plantations p ON p.plantation_id=t.plantation_id ORDER BY t.tree_id DESC LIMIT 5");
    while ($q && $row = mysqli_fetch_assoc($q)) $recent[] = [$row['title'], $row['detail'], ucwords($row['value']), 'flower1'];
} elseif ($role === 'naeb_officer') {
    $hero_text = 'Set tree prices by type and monitor buyer payments.';
    $cards = [
        ['Approved', scalar($con, "SELECT COUNT(*) FROM payments WHERE status='approved'"), 'check-circle-fill', 'green', 'Successfully processed payments'],
        ['Pending review', scalar($con, "SELECT COUNT(*) FROM payments WHERE status='pending'"), 'hourglass-split', 'orange', money(scalar($con, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='pending'")) . ' awaiting decision'],
        ['Officers', scalar($con, "SELECT COUNT(*) FROM users WHERE role IN ('forestry_officer','naeb_officer')"), 'person-badge-fill', 'purple', 'Registered NAEB and forestry officers'],
        ['Total paid out', money(scalar($con, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='approved'")), 'cash-stack', 'blue', 'Approved owner disbursements']
    ];
    [$chart_one['labels'], $chart_one['data']] = chart_rows($con, "SELECT status label,COUNT(*) total FROM payments GROUP BY status", 'label', 'total');
    $chart_one['title'] = 'Payment status'; $chart_one['subtitle'] = 'Current transaction distribution';
    [$chart_two['labels'], $chart_two['data']] = chart_rows($con, "SELECT DATE_FORMAT(payment_date,'%b %Y') label,SUM(amount) total FROM payments WHERE status='approved' GROUP BY YEAR(payment_date),MONTH(payment_date),label ORDER BY YEAR(payment_date),MONTH(payment_date) LIMIT 12", 'label', 'total');
    $chart_two['title'] = 'Approved payment trend'; $chart_two['subtitle'] = 'Monthly disbursements in RWF'; $chart_two['type'] = 'line';
    $q = mysqli_query($con, "SELECT u.full_name title,CONCAT(p.plantation_name,' · ',pay.status) detail,pay.amount value,pay.payment_date activity_date FROM payments pay JOIN users u ON u.user_id=pay.owner_id JOIN plantations p ON p.plantation_id=pay.plantation_id ORDER BY pay.payment_id DESC LIMIT 5");
    while ($q && $row = mysqli_fetch_assoc($q)) $recent[] = [$row['title'], $row['detail'], money($row['value']), 'wallet2'];
} elseif ($role === 'buyer') {
    $hero_text = 'Request approval to buy trees and track the forestry officer decision.';
    $cards = [
        ['My requests', scalar($con, "SELECT COUNT(*) FROM forest_requests WHERE requester_id=$uid"), 'clipboard-check', 'green', 'All buying requests'],
        ['Pending approval', scalar($con, "SELECT COUNT(*) FROM forest_requests WHERE requester_id=$uid AND status='pending'"), 'hourglass-split', 'blue', 'Awaiting forestry officer review'],
        ['Approved', scalar($con, "SELECT COUNT(*) FROM forest_requests WHERE requester_id=$uid AND status='approved'"), 'check-circle-fill', 'orange', 'Ready to continue purchase process'],
        ['Rejected', scalar($con, "SELECT COUNT(*) FROM forest_requests WHERE requester_id=$uid AND status='rejected'"), 'x-circle-fill', 'purple', 'Requests needing correction']
    ];
    [$chart_one['labels'], $chart_one['data']] = chart_rows($con, "SELECT status label,COUNT(*) total FROM forest_requests WHERE requester_id=$uid GROUP BY status", 'label', 'total');
    $chart_one['title'] = 'My request status'; $chart_one['subtitle'] = 'Forestry decisions on purchase requests';
    [$chart_two['labels'], $chart_two['data']] = chart_rows($con, "SELECT request_type label,COUNT(*) total FROM forest_requests WHERE requester_id=$uid GROUP BY request_type", 'label', 'total');
    $chart_two['title'] = 'Request types'; $chart_two['subtitle'] = 'Tree buying approvals requested';
    $q = mysqli_query($con, "SELECT request_type title,CONCAT(COALESCE(species,'Trees'),' · ',status) detail,created_at FROM forest_requests WHERE requester_id=$uid ORDER BY request_id DESC LIMIT 5");
    while ($q && $row = mysqli_fetch_assoc($q)) $recent[] = [ucwords(str_replace('_', ' ', $row['title'])), $row['detail'], date('d M', strtotime($row['created_at'])), 'clipboard-check'];
} else {
    $hero_text = 'Follow your plantations and request approval before planting or cutting trees.';
    $cards = [
        ['My plantations', scalar($con, "SELECT COUNT(*) FROM plantations WHERE owner_id=$uid"), 'tree-fill', 'green', number_format((float) scalar($con, "SELECT COALESCE(SUM(area_size),0) FROM plantations WHERE owner_id=$uid"), 2) . ' total hectares'],
        ['Payments received', money(scalar($con, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE owner_id=$uid AND status='approved'")), 'cash-stack', 'blue', 'Approved NAEB payments'],
        ['Forest requests', scalar($con, "SELECT COUNT(*) FROM forest_requests WHERE requester_id=$uid"), 'clipboard-check', 'orange', scalar($con, "SELECT COUNT(*) FROM forest_requests WHERE requester_id=$uid AND status='pending'") . ' awaiting forestry approval'],
        ['Timber value', money(scalar($con, "SELECT COALESCE(SUM(t.estimated_value),0) FROM trees t JOIN plantations p ON p.plantation_id=t.plantation_id WHERE p.owner_id=$uid")), 'graph-up-arrow', 'purple', 'Current estimated portfolio value']
    ];
    [$chart_one['labels'], $chart_one['data']] = chart_rows($con, "SELECT t.growth_status label,COALESCE(SUM(t.quantity),0) total FROM trees t JOIN plantations p ON p.plantation_id=t.plantation_id WHERE p.owner_id=$uid GROUP BY t.growth_status", 'label', 'total');
    $chart_one['title'] = 'My tree growth stages'; $chart_one['subtitle'] = 'Tree quantity by current status';
    [$chart_two['labels'], $chart_two['data']] = chart_rows($con, "SELECT status label,COALESCE(SUM(amount),0) total FROM payments WHERE owner_id=$uid GROUP BY status", 'label', 'total');
    $chart_two['title'] = 'My payment overview'; $chart_two['subtitle'] = 'Payment value by status';
    $q = mysqli_query($con, "SELECT p.plantation_name title,CONCAT(DATE_FORMAT(pay.payment_date,'%d %b %Y'),' · ',pay.status) detail,pay.amount value FROM payments pay JOIN plantations p ON p.plantation_id=pay.plantation_id WHERE pay.owner_id=$uid ORDER BY pay.payment_id DESC LIMIT 5");
    while ($q && $row = mysqli_fetch_assoc($q)) $recent[] = [$row['title'], $row['detail'], money($row['value']), 'wallet2'];
}

$page_title = 'Dashboard';
include 'includes/header.php';
include 'includes/navbar.php';
?>
<section class="dashboard-hero mb-4">
  <span class="eyebrow text-white-50"><?= e(ucwords(str_replace('_', ' ', $role))) ?> workspace</span>
  <h1>Welcome back, <?= e(explode(' ', trim($_SESSION['full_name']))[0]) ?>.</h1>
  <p><?= e($hero_text) ?></p>
  <div class="hero-actions">
    <?php if ($role === 'forestry_officer'): ?>
      <a class="btn btn-hero" href="requests.php"><i class="bi bi-clipboard-check me-1"></i> Review requests</a>
      <a class="btn btn-hero-outline" href="plantation_form.php"><i class="bi bi-plus-lg me-1"></i> New plantation</a>
      <a class="btn btn-hero-outline" href="tree_form.php"><i class="bi bi-flower1 me-1"></i> Record trees</a>
    <?php endif; ?>
    <?php if ($role === 'naeb_officer'): ?>
      <a class="btn btn-hero" href="tree_types.php"><i class="bi bi-tags me-1"></i> Manage tree types</a>
      <a class="btn btn-hero-outline" href="user_form.php"><i class="bi bi-person-plus me-1"></i> Register officer</a>
    <?php endif; ?>
    <?php if ($role === 'owner'): ?>
      <a class="btn btn-hero" href="request_form.php"><i class="bi bi-clipboard-plus me-1"></i> Request license</a>
      <a class="btn btn-hero-outline" href="plantations.php"><i class="bi bi-tree me-1"></i> View my assets</a>
      <a class="btn btn-hero-outline" href="payments.php"><i class="bi bi-wallet2 me-1"></i> Payment history</a>
    <?php endif; ?>
    <?php if ($role === 'buyer'): ?>
      <a class="btn btn-hero" href="trees.php"><i class="bi bi-cart-plus me-1"></i> View available trees</a>
      <a class="btn btn-hero-outline" href="requests.php"><i class="bi bi-clock-history me-1"></i> Track requests</a>
    <?php endif; ?>
    <?php if ($role === 'admin'): ?>
      <a class="btn btn-hero" href="users.php"><i class="bi bi-people me-1"></i> User management</a>
      <a class="btn btn-hero-outline" href="reports.php"><i class="bi bi-file-earmark-bar-graph me-1"></i> Reports</a>
    <?php endif; ?>
  </div>
</section>

<div class="row g-3 mb-4">
  <?php foreach ($cards as [$label, $value, $icon, $color, $note]): ?>
    <div class="col-sm-6 col-xxl-3">
      <div class="stat-card"><div class="card-body">
        <div class="stat-icon <?= e($color) ?>"><i class="bi bi-<?= e($icon) ?>"></i></div>
        <div class="stat-label"><?= e($label) ?></div>
        <div class="stat-value"><?= e($value) ?></div>
        <div class="stat-note"><?= e($note) ?></div>
      </div></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
  <div class="col-xl-5">
    <div class="panel-card">
      <div class="card-header"><div><h2><?= e($chart_one['title']) ?></h2><small><?= e($chart_one['subtitle']) ?></small></div><i class="bi bi-pie-chart text-success"></i></div>
      <div class="card-body"><div class="chart-wrap"><canvas id="chartOne"></canvas></div></div>
    </div>
  </div>
  <div class="col-xl-7">
    <div class="panel-card">
      <div class="card-header"><div><h2><?= e($chart_two['title']) ?></h2><small><?= e($chart_two['subtitle']) ?></small></div><i class="bi bi-bar-chart text-success"></i></div>
      <div class="card-body"><div class="chart-wrap"><canvas id="chartTwo"></canvas></div></div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-xl-8">
    <div class="panel-card">
      <div class="card-header"><div><h2>Recent activity</h2><small>Latest records relevant to your workspace</small></div></div>
      <div class="card-body py-2">
        <?php if (!$recent): ?><div class="empty-state"><i class="bi bi-inbox fs-2 d-block mb-2"></i>No activity has been recorded yet.</div><?php endif; ?>
        <?php foreach ($recent as [$title, $detail, $value, $icon]): ?>
          <div class="activity-item">
            <div class="activity-icon"><i class="bi bi-<?= e($icon) ?>"></i></div>
            <div class="activity-copy"><strong><?= e($title) ?></strong><small><?= e($detail) ?></small></div>
            <div class="activity-value"><?= e($value) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="panel-card">
      <div class="card-header"><div><h2>Workspace shortcuts</h2><small>Frequently used pages</small></div></div>
      <div class="card-body d-grid gap-2">
        <?php if (in_array($role, ['forestry_officer','owner'], true)): ?><a class="btn btn-light text-start p-3" href="plantations.php"><i class="bi bi-tree text-success me-2"></i> Plantation records</a><?php endif; ?>
        <?php if (in_array($role, ['forestry_officer','owner','buyer'], true)): ?><a class="btn btn-light text-start p-3" href="requests.php"><i class="bi bi-clipboard-check text-success me-2"></i> Forest requests</a><?php endif; ?>
        <?php if (in_array($role, ['naeb_officer','owner'], true)): ?><a class="btn btn-light text-start p-3" href="payments.php"><i class="bi bi-wallet2 text-success me-2"></i> Payment records</a><?php endif; ?>
        <?php if ($role === 'forestry_officer'): ?><a class="btn btn-light text-start p-3" href="map.php"><i class="bi bi-geo-alt text-success me-2"></i> Open GIS map</a><?php endif; ?>
        <?php if (in_array($role, ['admin','naeb_officer'], true)): ?><a class="btn btn-light text-start p-3" href="users.php"><i class="bi bi-people text-success me-2"></i> <?= $role === 'naeb_officer' ? 'Officer registration' : 'User management' ?></a><?php endif; ?>
        <?php if ($role === 'admin'): ?><a class="btn btn-light text-start p-3" href="reports.php"><i class="bi bi-file-earmark-bar-graph text-success me-2"></i> Generate reports</a><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const palette = ['#0a3d24','#177245','#1c8a53','#54a979','#8bc9a5','#111111','#68726c','#dff3e8'];
const chartDefaults = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 18, font: { family: 'DM Sans', size: 11 } } },
    tooltip: { padding: 11, cornerRadius: 8 }
  }
};
const oneLabels = <?= json_encode($chart_one['labels']) ?>;
const oneData = <?= json_encode($chart_one['data']) ?>;
new Chart(document.getElementById('chartOne'), {
  type: '<?= e($chart_one['type']) ?>',
  data: { labels: oneLabels.length ? oneLabels : ['No data'], datasets: [{ data: oneData.length ? oneData : [1], backgroundColor: oneLabels.length ? palette : ['#e5ebe7'], borderWidth: 0, hoverOffset: 5 }] },
  options: { ...chartDefaults, cutout: '65%' }
});
const twoType = '<?= e($chart_two['type']) ?>';
new Chart(document.getElementById('chartTwo'), {
  type: twoType,
  data: {
    labels: <?= json_encode($chart_two['labels']) ?>,
    datasets: [{
      label: 'Total',
      data: <?= json_encode($chart_two['data']) ?>,
      backgroundColor: twoType === 'line' ? 'rgba(25,135,84,.12)' : '#278f5a',
      borderColor: '#177245',
      borderWidth: twoType === 'line' ? 3 : 0,
      borderRadius: 7,
      fill: true,
      tension: .35,
      pointBackgroundColor: '#177245'
    }]
  },
  options: {
    ...chartDefaults,
    plugins: { ...chartDefaults.plugins, legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { font: { family: 'DM Sans', size: 10 } } },
      y: { beginAtZero: true, grid: { color: '#edf1ee' }, ticks: { font: { family: 'DM Sans', size: 10 } } }
    }
  }
});
</script>
<?php include 'includes/footer.php'; ?>
