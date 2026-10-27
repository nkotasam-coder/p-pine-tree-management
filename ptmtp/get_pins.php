<?php
session_start();
include 'conn.php';
include 'auth.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'forestry_officer') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Access denied']);
    exit;
}
$result = mysqli_query($con, "SELECT p.latitude lat,p.longitude lng,p.plantation_name name,p.area_size size,u.full_name owner FROM plantations p JOIN users u ON p.owner_id=u.user_id WHERE p.latitude IS NOT NULL AND p.longitude IS NOT NULL");
$pins = [];
while ($row = mysqli_fetch_assoc($result))
    $pins[] = $row;
header('Content-Type: application/json');
echo json_encode($pins);
?>