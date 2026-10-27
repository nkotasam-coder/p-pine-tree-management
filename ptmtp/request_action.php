<?php
session_start(); include 'conn.php'; include 'auth.php'; require_roles(['forestry_officer']);
$id=(int)($_POST['request_id']??0);$action=$_POST['action']??'';$uid=(int)$_SESSION['user_id'];if(!$id||!in_array($action,['approved','rejected'],true))redirect('requests.php');
mysqli_begin_transaction($con);$r=mysqli_query($con,"SELECT * FROM forest_requests WHERE request_id=$id AND status='pending' FOR UPDATE");$req=$r?mysqli_fetch_assoc($r):null;$ok=(bool)$req;$fulfillment='none';
if($ok&&$action==='approved'){
 if($req['request_type']==='plant_license')$fulfillment='awaiting_registration';
 elseif($req['request_type']==='cut_license'){$tree=(int)$req['tree_id'];$qty=(int)$req['quantity'];$ok=mysqli_query($con,"UPDATE trees SET sale_quantity=sale_quantity+$qty WHERE tree_id=$tree AND quantity-sale_quantity>=$qty AND growth_status IN ('mature','ready_for_harvest')")&&mysqli_affected_rows($con)===1;}
 elseif($req['request_type']==='buy_trees'){$tree=(int)$req['tree_id'];$qty=(int)$req['quantity'];$ok=mysqli_query($con,"UPDATE trees SET reserved_quantity=reserved_quantity+$qty WHERE tree_id=$tree AND sale_quantity-reserved_quantity>=$qty AND growth_status IN ('mature','ready_for_harvest')")&&mysqli_affected_rows($con)===1;$fulfillment='awaiting_payment';}
}
if($ok)$ok=mysqli_query($con,"UPDATE forest_requests SET status='".db_escape($con,$action)."',reviewed_by=$uid,reviewed_at=NOW(),fulfillment_status='$fulfillment' WHERE request_id=$id AND status='pending'")&&mysqli_affected_rows($con)===1;
if($ok){mysqli_commit($con);audit($con,$uid,'request_'.$action,'forest_request',$id,$req['request_type'].' request');}else mysqli_rollback($con);
flash($ok?'success':'danger',$ok?"Request $action.":'Approval failed because the requested stock is no longer available.');redirect('requests.php');
?>
