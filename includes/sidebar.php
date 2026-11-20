<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUri = $_SERVER['REQUEST_URI'];

$roleName   = strtolower(trim($_SESSION['admin_role'] ?? ''));
$roleId     = (int) ($_SESSION['admin_role_id'] ?? 0);

$isMaster   = ($roleId === 2 || $roleName === 'master');
$isDelivery = ($roleName === 'delivery partner' || $roleName === 'delivery_partner');
$isAdmin    = (!$isMaster && !$isDelivery);
?>

<aside class="admin-sidebar" id="adminSidebar">

    <!-- BRAND HEADER WITH SCISSORS BADGE -->
    <div class="sidebar-brand">
        <?php if ($isMaster): ?>
            <a href="/darjibhai/master/dashboard.php">
        <?php elseif ($isDelivery): ?>
            <a href="/darjibhai/delivery-partner/dashboard.php">
        <?php else: ?>
            <a href="/darjibhai/admin/dashboard.php">
        <?php endif; ?>
            <span class="brand-badge">
                <i class="fa-solid fa-scissors"></i>
            </span>
            <span class="darjibhai-logo">Darji<span>bhai</span></span>
        </a>
    </div>

    <div class="sidebar-menu">

        <!-- ================= MASTER ROLE ================= -->
        <?php if ($isMaster): ?>

            <span class="sidebar-label">Main</span>

            <a
                href="/darjibhai/master/dashboard.php"
                class="sidebar-link <?= (strpos($currentUri, '/master/dashboard.php') !== false || $currentUri === '/darjibhai/master/') ? 'active' : '' ?>"
            >
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>

            <span class="sidebar-label">Management</span>

            <a
                href="/darjibhai/master/orders/index.php"
                class="sidebar-link <?= (strpos($currentUri, '/master/orders') !== false) ? 'active' : '' ?>"
            >
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Orders</span>
            </a>

        <!-- ================= DELIVERY PARTNER ROLE ================= -->
        <?php elseif ($isDelivery): ?>

            <span class="sidebar-label">Main</span>

            <a
                href="/darjibhai/delivery-partner/dashboard.php"
                class="sidebar-link <?= (strpos($currentUri, '/delivery-partner/dashboard.php') !== false || $currentUri === '/darjibhai/delivery-partner/') ? 'active' : '' ?>"
            >
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>

            <span class="sidebar-label">Deliveries</span>

            <a
                href="/darjibhai/delivery-partner/delivery-orders/index.php"
                class="sidebar-link <?= (strpos($currentUri, '/delivery-partner/delivery-orders') !== false) ? 'active' : '' ?>"
            >
                <i class="fa-solid fa-truck"></i>
                <span>Delivery Order</span>
            </a>

        <!-- ================= ADMIN / SUPER ADMIN ROLE ================= -->
        <?php else: ?>

            <span class="sidebar-label">Main</span>

            <a
                href="/darjibhai/admin/dashboard.php"
                class="sidebar-link <?= (strpos($currentUri, '/admin/dashboard.php') !== false) ? 'active' : '' ?>"
            >
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>

            <span class="sidebar-label">Management</span>

            <a
                href="/darjibhai/admin/user-managment/index.php"
                class="sidebar-link <?= (strpos($currentUri, '/admin/user-managment') !== false || strpos($currentUri, '/admin/users') !== false) ? 'active' : '' ?>"
            >
                <i class="fa-solid fa-users"></i>
                <span>Users</span>
            </a>

            <a
                href="/darjibhai/admin/products/index.php"
                class="sidebar-link <?= (strpos($currentUri, '/admin/products') !== false) ? 'active' : '' ?>"
            >
                <i class="fa-solid fa-shirt"></i>
                <span>Products Categories</span>
            </a>

            <a
                href="/darjibhai/admin/job-description/index.php"
                class="sidebar-link <?= (strpos($currentUri, '/admin/job-description') !== false) ? 'active' : '' ?>"
            >
                <i class="fa-solid fa-list-check"></i>
                <span>Job Description</span>
            </a>

                <!-- <a
                    href="/darjibhai/admin/roles/index.php"
                    class="sidebar-link <?= (strpos($currentUri, '/admin/roles') !== false) ? 'active' : '' ?>"
                >
                    <i class="fa-solid fa-user-shield"></i>
                    <span>Roles</span>
                </a> -->

             <span class="sidebar-label">Orders</span>

            <a
                href="/darjibhai/admin/orders/index.php"
                class="sidebar-link <?= (strpos($currentUri, '/admin/orders') !== false) ? 'active' : '' ?>"
            >
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Orders</span>
            </a>

        <?php endif; ?>

        <!-- ================= LOGOUT ================= -->
        <span class="sidebar-label">Account</span>

        <a
            href="/darjibhai/admin/logout.php"
            class="sidebar-link text-danger"
        >
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Logout</span>
        </a>

    </div>

</aside>
