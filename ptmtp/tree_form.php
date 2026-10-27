<?php
session_start();
include 'conn.php';
include 'auth.php';
require_roles(['forestry_officer', 'naeb_officer']);
$t = ['tree_id' => '', 'plantation_id' => '', 'tree_type_id' => '', 'species' => '', 'quantity' => '', 'age_years' => '', 'growth_status' => 'seedling', 'unit_price' => 0];
if ($_SESSION['role'] === 'naeb_officer') redirect('tree_types.php');
if (!isset($_GET['id'])) redirect('trees.php');
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $r = mysqli_query($con, "SELECT * FROM trees WHERE tree_id=$id");
    if ($r && mysqli_num_rows($r))
        $t = mysqli_fetch_assoc($r);
    else
        redirect('trees.php');
}
$plantations = mysqli_query($con, "SELECT p.plantation_id,p.plantation_name,u.full_name FROM plantations p JOIN users u ON u.user_id=p.owner_id ORDER BY p.plantation_name");
$tree_types = mysqli_query($con, "SELECT tree_type_id,type_name,unit_price FROM tree_types WHERE is_active=1 OR tree_type_id=".(int)$t['tree_type_id']." ORDER BY type_name");
$page_title = $t['tree_id'] ? 'Edit tree record' : 'Add tree record';
include 'includes/header.php';
include 'includes/navbar.php';
?>
<h1 class="page-title h2 mb-3"><?= e($page_title) ?></h1>
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="post" action="tree_save.php"><input type="hidden" name="tree_id" value="<?= e($t['tree_id']) ?>">
            <div class="row g-3">
                <fieldset class="row g-3 m-0 p-0 border-0">
                <div class="col-md-6"><label class="form-label">Plantation</label><select class="form-select"
                        name="plantation_id" disabled>
                        <option value="">Select plantation</option>
                        <?php while ($p = mysqli_fetch_assoc($plantations)): ?>
                            <option value="<?= (int) $p['plantation_id'] ?>" <?= (string) $t['plantation_id'] === (string) $p['plantation_id'] ? 'selected' : '' ?>>
                            

                                                            <?= e($p['plantation_name'] . ' — ' . $p['full_name']) ?></option><?php endwhile; ?>
                    </select></div>
                <div class="col-md-6"><label class="form-label">Tree type registered by NAEB</label><select class="form-select" disabled><option><?= e($t['species']) ?></option></select></div>
                <div class="col-md-4"><label class="form-label">Quantity</label><input class="form-control"
                        type="number" value="<?= e($t['quantity']) ?>" readonly></div>
                <div class="col-md-4"><label class="form-label">Age (years)</label><input class="form-control"
                        type="number" min="0" name="age_years" value="<?= e($t['age_years']) ?>" required></div>
                <div class="col-md-4"><label class="form-label">Growth status</label><select class="form-select"
                    name="growth_status"><?php foreach (['seedling', 'growing', 'mature', 'ready_for_harvest'] as $s): ?>

                                                        <option value="<?= $s ?>" <?= $t['growth_status'] === $s ? 'selected' : '' ?>>
                                    <?= e(ucwords(str_replace('_', ' ', $s))) ?></option><?php endforeach; ?>
                    </select></div>
                </fieldset>
            </div>
            <div class="alert alert-light border mt-3 mb-0">The tree name and selling price come from the NAEB tree-type catalogue.</div>
            <div class="mt-4"><button class="btn btn-success" name="save">Save tree record</button> <a
                    class="btn btn-light" href="trees.php">Cancel</a></div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
