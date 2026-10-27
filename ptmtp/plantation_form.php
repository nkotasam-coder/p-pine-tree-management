<?php
session_start();
include 'conn.php';
include 'auth.php';
require_roles(['forestry_officer','owner']);
$p = ['plantation_id' => '', 'owner_id' => '', 'plantation_name' => '', 'district' => '', 'sector' => '', 'latitude' => '', 'longitude' => '', 'area_size' => '', 'plant_request_id'=>''];
$approved=null;
if($_SESSION['role']==='owner'){$rid=(int)($_GET['request_id']??0);$uid=(int)$_SESSION['user_id'];$r=mysqli_query($con,"SELECT * FROM forest_requests WHERE request_id=$rid AND requester_id=$uid AND request_type='plant_license' AND status='approved' AND fulfillment_status='awaiting_registration'");$approved=$r?mysqli_fetch_assoc($r):null;if(!$approved){flash('danger','Choose an approved planting request first.');redirect('requests.php');}$p['owner_id']=$uid;$p['area_size']=$approved['area_size'];$p['plant_request_id']=$rid;}
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $r = mysqli_query($con, "SELECT * FROM plantations WHERE plantation_id=$id");
    if ($r && mysqli_num_rows($r))
        $p = mysqli_fetch_assoc($r);
    else
        redirect('plantations.php');
}
$owners = mysqli_query($con, "SELECT user_id,full_name,email FROM users WHERE role='owner' ORDER BY full_name");
$page_title = $p['plantation_id'] ? 'Edit plantation' : 'Add plantation';
include 'includes/header.php';
include 'includes/navbar.php';
?>
<h1 class="page-title h2 mb-3"><?= e($page_title) ?></h1>
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="post" action="plantation_save.php"><input type="hidden" name="plantation_id"
                value="<?= e($p['plantation_id']) ?>">
            <input type="hidden" name="plant_request_id" value="<?= e($p['plant_request_id']) ?>">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Plantation name</label><input class="form-control"
                        name="plantation_name" value="<?= e($p['plantation_name']) ?>" required></div>
                <div class="col-md-6"><label class="form-label">Owner</label><select class="form-select" name="owner_id" <?= $_SESSION['role']==='owner'?'disabled':'' ?>
                        required>
                        <option value="">Select owner</option><?php while ($o = mysqli_fetch_assoc($owners)): ?>
                            <option value="<?= (int) $o['user_id'] ?>"
                                <?= (string) $p['owner_id'] === (string) $o['user_id'] ? 'selected' : '' ?>>
                                <?= e($o['full_name'] . ' — ' . $o['email']) ?></option><?php endwhile; ?>
                    </select><?php if($_SESSION['role']==='owner'): ?><input type="hidden" name="owner_id" value="<?= (int)$p['owner_id'] ?>"><?php endif; ?></div>
                <div class="col-md-6"><label class="form-label">District</label><input class="form-control"
                        name="district" value="<?= e($p['district']) ?>" required></div>
                <div class="col-md-6"><label class="form-label">Sector</label><input class="form-control" name="sector"
                        value="<?= e($p['sector']) ?>" required></div>
                <div class="col-md-4"><label class="form-label">Latitude</label><input class="form-control"
                        type="number" step="0.0000001" min="-90" max="90" name="latitude"
                        value="<?= e($p['latitude']) ?>" required></div>
                <div class="col-md-4"><label class="form-label">Longitude</label><input class="form-control"
                        type="number" step="0.0000001" min="-180" max="180" name="longitude"
                        value="<?= e($p['longitude']) ?>" required></div>
                <div class="col-md-4"><label class="form-label">Area size (ha)</label><input class="form-control"
                        type="number" step="0.01" min="0.01" name="area_size" value="<?= e($p['area_size']) ?>" <?= $_SESSION['role']==='owner'?'readonly':'' ?>
                        required></div>
            </div>
            <?php if($approved): ?><div class="alert alert-success mt-3 mb-0">Approved to plant <?= number_format((int)$approved['quantity']) ?> <?= e($approved['species']) ?> trees. This stock will be created automatically.</div><?php endif; ?>
            <div class="mt-4"><button class="btn btn-success" name="save">Save plantation</button> <a
                    class="btn btn-light" href="plantations.php">Cancel</a></div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
