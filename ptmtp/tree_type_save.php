<?php
session_start(); include 'conn.php'; include 'auth.php'; require_roles(['naeb_officer']);
if (!isset($_POST['save'])) redirect('tree_types.php');
$id=(int)($_POST['tree_type_id']??0); $name=db_escape($con,$_POST['type_name']??''); $description=db_escape($con,$_POST['description']??''); $price=(float)($_POST['unit_price']??0); $active=isset($_POST['is_active'])?1:0; $uid=(int)$_SESSION['user_id'];
if (!$name || $price<0) { flash('danger','Provide a valid tree type and price.'); redirect($id?"tree_type_form.php?id=$id":'tree_type_form.php'); }
if ($id) $ok=mysqli_query($con,"UPDATE tree_types SET type_name='$name',description='$description',unit_price=$price,is_active=$active WHERE tree_type_id=$id");
else {$ok=mysqli_query($con,"INSERT INTO tree_types(type_name,description,unit_price,is_active,created_by) VALUES('$name','$description',$price,$active,$uid)");if($ok)$id=(int)mysqli_insert_id($con);}
if ($ok && $id) {mysqli_query($con,"UPDATE trees SET species='$name',unit_price=$price,estimated_value=quantity*$price WHERE tree_type_id=$id");audit($con,$uid,'tree_type_saved','tree_type',$id,"$name at $price RWF");}
flash($ok?'success':'danger',$ok?'Tree type saved.':'Could not save tree type; the name may already exist.'); redirect('tree_types.php');
?>
