<?php
session_start();
include 'conn.php';
include 'auth.php';
require_roles(['naeb_officer']);
$rows = mysqli_query($con, "SELECT tt.*,u.full_name creator FROM tree_types tt JOIN users u ON u.user_id=tt.created_by ORDER BY tt.type_name");
$page_title = 'Tree types';
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3"><div><h1 class="page-title h2 mb-1">Tree type catalogue</h1><p class="text-muted mb-0">Tree types owners and buyers are allowed to select.</p></div><a class="btn btn-success" href="tree_type_form.php"><i class="bi bi-plus-lg"></i> Add tree type</a></div>
<div class="table-responsive shadow-sm"><table class="table table-striped table-bordered mb-0"><thead><tr><th>Tree type</th><th>Description</th><th>Price / tree</th><th>Status</th><th>Created by</th><th>Action</th></tr></thead><tbody>
<?php while ($t=mysqli_fetch_assoc($rows)): ?><tr><td><?= e($t['type_name']) ?></td><td><?= e($t['description']) ?></td><td><?= e(money($t['unit_price'])) ?></td><td><span class="badge text-bg-<?= $t['is_active'] ? 'success' : 'secondary' ?>"><?= $t['is_active'] ? 'Active' : 'Inactive' ?></span></td><td><?= e($t['creator']) ?></td><td><a class="btn btn-sm btn-outline-success" href="tree_type_form.php?id=<?= (int)$t['tree_type_id'] ?>">Edit</a></td></tr><?php endwhile; ?>
</tbody></table></div><?php include 'includes/footer.php'; ?>
