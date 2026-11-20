<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. If not logged in, send to login
if (!isset($_SESSION['admin_id'])) {
    header("Location: /darjibhai/admin/login.php");
    exit;
}

// 2. Fetch role details from session
$roleName = strtolower(trim($_SESSION['admin_role'] ?? ''));
$roleId   = (int) ($_SESSION['admin_role_id'] ?? 0);

// Check if user is Master
$isMaster = ($roleId === 2 || $roleName === 'master');

// Check if user is Delivery Partner
$isDelivery = ($roleName === 'delivery partner' || $roleName === 'delivery_partner');

// 3. Block Master from accessing admin dashboard
if ($isMaster) {
    header("Location: /darjibhai/master/dashboard.php");
    exit;
}

// 4. Block Delivery Partner from accessing admin dashboard
if ($isDelivery) {
    header("Location: /darjibhai/delivery-partner/dashboard.php");
    exit;
}
