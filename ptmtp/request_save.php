<?php
session_start(); include 'conn.php'; include 'auth.php'; require_roles(['owner','buyer']);
if(!isset($_POST['save'])) redirect('requests.php');
$uid=(int)$_SESSION['user_id']; $role=$_SESSION['role']; $type=$_POST['request_type']??''; $allowed=$role==='buyer'?['buy_trees']:['plant_license','cut_license']; $tree_type_id=(int)($_POST['tree_type_id']??0); $tree_id=(int)($_POST['tree_id']??0); $quantity=(int)($_POST['quantity']??0); $area=(float)($_POST['area_size']??0); $reason=db_escape($con,$_POST['reason']??''); $species=''; $plantation_sql='NULL';
if(!in_array($type,$allowed,true)||$quantity<1||!$reason){flash('danger','Provide a valid request and quantity.');redirect('request_form.php');}
if($type==='plant_license') { $r=mysqli_query($con,"SELECT type_name FROM tree_types WHERE tree_type_id=$tree_type_id AND is_active=1"); $x=$r?mysqli_fetch_assoc($r):null; $species=db_escape($con,$x['type_name']??''); if(!$species||$area<=0){flash('danger','Select a registered tree type and enter the planting area.');redirect('request_form.php');} }
else { $owner_condition=$role==='owner'?"p.owner_id=$uid AND t.quantity>=$quantity":"t.sale_quantity-t.reserved_quantity>=$quantity"; $r=mysqli_query($con,"SELECT t.tree_type_id,t.species,t.plantation_id FROM trees t JOIN plantations p ON p.plantation_id=t.plantation_id WHERE t.tree_id=$tree_id AND $owner_condition AND t.growth_status IN ('mature','ready_for_harvest')"); $x=$r?mysqli_fetch_assoc($r):null; if(!$x){flash('danger','Selected stock is unavailable or the quantity is too high.');redirect('request_form.php');} $tree_type_id=(int)$x['tree_type_id'];$species=db_escape($con,$x['species']);$plantation_sql=(int)$x['plantation_id']; }
$type=db_escape($con,$type);$area_sql=$area>0?(string)$area:'NULL';
$ok=mysqli_query($con,"INSERT INTO forest_requests(requester_id,plantation_id,tree_type_id,tree_id,request_type,species,quantity,area_size,reason) VALUES($uid,$plantation_sql,$tree_type_id,".($tree_id?:'NULL').",'$type','$species',$quantity,$area_sql,'$reason')");
if($ok) audit($con,$uid,'request_created','forest_request',(int)mysqli_insert_id($con),"$type: $quantity $species");
flash($ok?'success':'danger',$ok?'Request submitted for forestry approval.':'Could not submit request.');redirect('requests.php');
?>
