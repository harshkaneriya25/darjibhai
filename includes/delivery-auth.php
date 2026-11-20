<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Check if logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: /darjibhai/admin/login.php');
    exit;
}

$roleName = strtolower(trim($_SESSION['admin_role'] ?? ''));
$roleId   = (int) ($_SESSION['admin_role_id'] ?? 0);

// Resolve the role by name so the guard agrees with admin navigation regardless of role IDs.
$isDelivery = ($roleName === 'delivery partner' || $roleName === 'delivery_partner');

// 2. If not a Delivery Partner, redirect to their respective view
if (!$isDelivery) {
    if ($roleId === 2 || $roleName === 'master') {
        header('Location: /darjibhai/master/dashboard.php');
        exit;
    }
    header('Location: /darjibhai/admin/dashboard.php');
    exit;
}
