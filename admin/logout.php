<?php

session_start();

// Remove admin session data
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_phone']);
unset($_SESSION['admin_role_id']);
unset($_SESSION['admin_role']);

// Destroy session
session_destroy();

// Redirect to admin login
header("Location: /darjibhai/admin/login.php");
exit;