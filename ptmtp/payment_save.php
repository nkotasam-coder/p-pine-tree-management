<?php
session_start();
include 'conn.php';
include 'auth.php';
require_roles(['buyer', 'owner']);
$uid = (int) $_SESSION['user_id'];

if ($_SESSION['role'] === 'owner' && (isset($_POST['approve']) || isset($_POST['reject']))) {
    $id = (int) ($_POST['payment_id'] ?? 0);
    $approved = isset($_POST['approve']);
    $status = $approved ? 'approved' : 'rejected';
    mysqli_begin_transaction($con);
    $r = mysqli_query($con, "SELECT * FROM payments WHERE payment_id=$id AND owner_id=$uid AND status='pending' FOR UPDATE");
    $pay = $r ? mysqli_fetch_assoc($r) : null;
    $ok = (bool) $pay;
    if ($ok) {
        $tree = (int) $pay['tree_id']; $qty = (int) $pay['quantity']; $request = (int) $pay['request_id'];
        if ($approved) {
            $ok = mysqli_query($con, "UPDATE trees SET quantity=quantity-$qty,sale_quantity=sale_quantity-$qty,reserved_quantity=reserved_quantity-$qty,estimated_value=(quantity-$qty)*unit_price WHERE tree_id=$tree AND quantity>=$qty AND sale_quantity>=$qty AND reserved_quantity>=$qty") && mysqli_affected_rows($con) === 1;
            $fulfillment = 'completed';
        } else {
            $ok = mysqli_query($con, "UPDATE trees SET reserved_quantity=reserved_quantity-$qty WHERE tree_id=$tree AND reserved_quantity>=$qty") && mysqli_affected_rows($con) === 1;
            $fulfillment = 'payment_rejected';
        }
        if ($ok) $ok = mysqli_query($con, "UPDATE payments SET status='$status' WHERE payment_id=$id AND status='pending'") && mysqli_affected_rows($con) === 1;
        if ($ok) $ok = mysqli_query($con, "UPDATE forest_requests SET fulfillment_status='$fulfillment',completed_at=" . ($approved ? 'NOW()' : 'NULL') . " WHERE request_id=$request") && mysqli_affected_rows($con) === 1;
    }
    if ($ok) { mysqli_commit($con); audit($con, $uid, 'payment_' . $status, 'payment', $id, 'Owner decision; stock and reservation updated'); }
    else mysqli_rollback($con);
    flash($ok ? 'success' : 'danger', $ok ? "Payment $status." : 'Could not update payment or reserved stock.');
    redirect('payments.php');
}

if ($_SESSION['role'] !== 'buyer' || !isset($_POST['save'])) redirect('payments.php');
$request = (int) ($_POST['request_id'] ?? 0);
$method = db_escape($con, $_POST['payment_method'] ?? '');
mysqli_begin_transaction($con);
$r = mysqli_query($con, "SELECT fr.quantity,fr.tree_id,t.unit_price,t.plantation_id,p.owner_id FROM forest_requests fr JOIN trees t ON t.tree_id=fr.tree_id JOIN plantations p ON p.plantation_id=t.plantation_id LEFT JOIN payments pay ON pay.request_id=fr.request_id WHERE fr.request_id=$request AND fr.requester_id=$uid AND fr.status='approved' AND fr.fulfillment_status='awaiting_payment' AND pay.payment_id IS NULL FOR UPDATE");
$x = $r ? mysqli_fetch_assoc($r) : null;
$ok = $x && $method;
if ($ok) {
    $qty = (int) $x['quantity']; $amount = $qty * (float) $x['unit_price'];
    $ok = mysqli_query($con, "INSERT INTO payments(owner_id,plantation_id,buyer_id,tree_id,quantity,request_id,amount,payment_date,payment_method,status) VALUES(" . (int)$x['owner_id'] . "," . (int)$x['plantation_id'] . ",$uid," . (int)$x['tree_id'] . ",$qty,$request,$amount,CURDATE(),'$method','pending')");
    $id = (int) mysqli_insert_id($con);
    if ($ok) $ok = mysqli_query($con, "UPDATE forest_requests SET fulfillment_status='payment_pending' WHERE request_id=$request AND fulfillment_status='awaiting_payment'") && mysqli_affected_rows($con) === 1;
}
if ($ok) { mysqli_commit($con); audit($con, $uid, 'payment_submitted', 'payment', $id, 'Submitted from approved buying request'); }
else mysqli_rollback($con);
flash($ok ? 'success' : 'danger', $ok ? 'Payment submitted to the owner for verification.' : 'Payment is unavailable or already submitted.');
redirect('payments.php');
?>
