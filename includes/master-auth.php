<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Check if admin session exists
if (!isset($_SESSION['admin_id'])) {
    header('Location: /darjibhai/admin/login.php');
    exit;
}

// 2. Fetch role identifiers
$adminRole   = strtolower(trim($_SESSION['admin_role'] ?? ''));
$adminRoleId = (int) ($_SESSION['admin_role_id'] ?? 0);

// Role ID 2 corresponds to 'Master' in the roles table
$isMaster = ($adminRoleId === 2 || $adminRole === 'master');

// 3. Block access if the role is not Master
if (!$isMaster) {
    // If Super Admin tries to access, redirect to main admin dashboard
    if ($adminRoleId === 1 || $adminRole === 'super admin' || $adminRole === 'administrator') {
        header('Location: /darjibhai/admin/dashboard.php');
        exit;
    }

    // Default redirect to login for any other unauthorized user
    header('Location: /darjibhai/admin/login.php');
    exit;
}