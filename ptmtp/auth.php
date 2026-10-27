<?php
function require_login(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function require_roles(array $roles): void {
    require_login();
    if (!in_array($_SESSION['role'], $roles, true)) {
        $_SESSION['flash'] = ['danger', 'Access denied.'];
        header('Location: dashboard.php');
        exit;
    }
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function db_escape(mysqli $con, $value): string {
    return mysqli_real_escape_string($con, trim((string)$value));
}

function flash(string $type, string $message): void {
    $_SESSION['flash'] = [$type, $message];
}

function redirect(string $page): void {
    header("Location: $page");
    exit;
}

function scalar(mysqli $con, string $sql) {
    $result = mysqli_query($con, $sql);
    if (!$result) return 0;
    $row = mysqli_fetch_row($result);
    return $row[0] ?? 0;
}

function money($amount): string {
    return number_format((float)$amount, 0) . ' RWF';
}

function audit(mysqli $con, int $user_id, string $action, string $entity_type, int $entity_id, string $details = ''): void {
    $action = db_escape($con, $action); $entity_type = db_escape($con, $entity_type); $details = db_escape($con, $details);
    mysqli_query($con, "INSERT INTO audit_log(user_id,action,entity_type,entity_id,details) VALUES($user_id,'$action','$entity_type',$entity_id,'$details')");
}
?>
