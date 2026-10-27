<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'];
$role_label = ucwords(str_replace('_', ' ', $role));
function nav_active(array $pages): string {
    global $current_page;
    return in_array($current_page, $pages, true) ? ' active' : '';
}
?>
<div class="app-shell">
  <aside class="app-sidebar no-print" id="appSidebar">
    <div class="sidebar-brand">
      <span class="brand-mark"><i class="bi bi-tree-fill"></i></span>
      <div><strong>PTMPT</strong><small>Forestry Management</small></div>
      <button class="sidebar-close d-lg-none" id="sidebarClose" type="button"><i class="bi bi-x-lg"></i></button>
    </div>

    <div class="sidebar-profile">
      <div class="profile-avatar"><?= e(strtoupper(substr($_SESSION['full_name'], 0, 1))) ?></div>
      <div class="overflow-hidden"><strong><?= e($_SESSION['full_name']) ?></strong><small><?= e($role_label) ?></small></div>
    </div>

    <nav class="sidebar-nav">
      <span class="nav-caption">Overview</span>
      <a class="sidebar-link<?= nav_active(['dashboard.php']) ?>" href="dashboard.php"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>

      <?php if (in_array($role, ['forestry_officer','naeb_officer','owner','buyer'], true)): ?>
        <span class="nav-caption">Forestry</span>
        <?php if (in_array($role, ['forestry_officer','owner'], true)): ?>
        <a class="sidebar-link<?= nav_active(['plantations.php','plantation_form.php']) ?>" href="plantations.php"><i class="bi bi-tree"></i><span><?= $role === 'owner' ? 'My Plantations' : 'Plantations' ?></span></a>
        <?php endif; ?>
        <a class="sidebar-link<?= nav_active(['trees.php','tree_form.php']) ?>" href="trees.php"><i class="bi bi-flower1"></i><span>Tree Records</span></a>
        <?php if ($role === 'naeb_officer'): ?><a class="sidebar-link<?= nav_active(['tree_types.php','tree_type_form.php']) ?>" href="tree_types.php"><i class="bi bi-tags-fill"></i><span>Tree Types</span></a><?php endif; ?>
        <?php if (in_array($role, ['forestry_officer','owner'], true)): ?>
        <a class="sidebar-link<?= nav_active(['timber_value.php']) ?>" href="timber_value.php"><i class="bi bi-graph-up-arrow"></i><span>Timber Value</span></a>
        <?php endif; ?>
      <?php endif; ?>

      <?php if (in_array($role, ['forestry_officer','owner','buyer'], true)): ?>
        <a class="sidebar-link<?= nav_active(['requests.php','request_form.php']) ?>" href="requests.php"><i class="bi bi-clipboard-check"></i><span><?= $role === 'forestry_officer' ? 'Forest Approvals' : 'My Requests' ?></span></a>
      <?php endif; ?>

      <?php if ($role === 'forestry_officer'): ?>
        <a class="sidebar-link<?= nav_active(['map.php']) ?>" href="map.php"><i class="bi bi-geo-alt-fill"></i><span>GIS Map</span></a>
      <?php endif; ?>

      <?php if (in_array($role, ['naeb_officer','owner','buyer'], true)): ?>
        <span class="nav-caption">Finance</span>
        <a class="sidebar-link<?= nav_active(['payments.php','payment_form.php']) ?>" href="payments.php"><i class="bi bi-wallet2"></i><span><?= $role === 'owner' ? 'My Payments' : 'Payments' ?></span></a>
      <?php endif; ?>

      <?php if (in_array($role, ['admin','naeb_officer'], true)): ?>
        <span class="nav-caption">Administration</span>
        <a class="sidebar-link<?= nav_active(['users.php','user_form.php']) ?>" href="users.php"><i class="bi bi-people-fill"></i><span><?= $role === 'naeb_officer' ? 'Officer Registration' : 'User Management' ?></span></a>
      <?php endif; ?>

      <?php if ($role === 'admin'): ?>
        <a class="sidebar-link<?= nav_active(['reports.php']) ?>" href="reports.php"><i class="bi bi-file-earmark-bar-graph-fill"></i><span>Reports</span></a>
      <?php endif; ?>
      <?php if (in_array($role, ['admin','forestry_officer','naeb_officer'], true)): ?>
        <a class="sidebar-link<?= nav_active(['audit_log.php']) ?>" href="audit_log.php"><i class="bi bi-clock-history"></i><span>Audit Trail</span></a>
      <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
      <a class="sidebar-link logout-link" href="logout.php"><i class="bi bi-box-arrow-left"></i><span>Sign out</span></a>
    </div>
  </aside>

  <div class="app-main">
    <header class="app-topbar no-print">
      <button class="menu-toggle" id="sidebarOpen" type="button"><i class="bi bi-list"></i></button>
      <div class="topbar-title"><strong><?= isset($page_title) ? e($page_title) : 'PTMPT' ?></strong><small><?= e(date('l, d F Y')) ?></small></div>
      <div class="topbar-actions">
        <span class="status-pill"><span></span> System online</span>
        <div class="topbar-avatar"><?= e(strtoupper(substr($_SESSION['full_name'], 0, 1))) ?></div>
      </div>
    </header>
    <main class="app-content">
      <?php if (!empty($_SESSION['flash'])): [$flash_type, $flash_message] = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <div class="alert alert-<?= e($flash_type) ?> alert-dismissible fade show shadow-sm" role="alert">
          <?= e($flash_message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
