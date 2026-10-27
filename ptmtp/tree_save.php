<?php
session_start();include 'conn.php';include 'auth.php';require_roles(['forestry_officer']);if(!isset($_POST['save']))redirect('trees.php');
$id=(int)($_POST['tree_id']??0);$age=(int)($_POST['age_years']??0);$status=db_escape($con,$_POST['growth_status']??'');$statuses=['seedling','growing','mature','ready_for_harvest'];$uid=(int)$_SESSION['user_id'];
if(!$id||$age<0||!in_array($status,$statuses,true)){flash('danger','Provide a valid age and growth status.');redirect("tree_form.php?id=$id");}
$ok=mysqli_query($con,"UPDATE trees t JOIN tree_types tt ON tt.tree_type_id=t.tree_type_id SET t.age_years=$age,t.growth_status='$status',t.species=tt.type_name,t.unit_price=tt.unit_price,t.estimated_value=t.quantity*tt.unit_price WHERE t.tree_id=$id");
if($ok)audit($con,$uid,'tree_growth_updated','tree',$id,"Age $age; status $status");flash($ok?'success':'danger',$ok?'Tree growth record updated.':'Could not update tree record.');redirect('trees.php');?>
