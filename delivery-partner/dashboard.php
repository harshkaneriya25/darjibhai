<?php

require_once __DIR__ . '/../includes/delivery-auth.php';
require_once __DIR__ . '/../config/database.php';

$page_title = 'Delivery Dashboard';
$admin_page = true;
$extra_css = '<style>
    .dashboard-statistics > [class*="col-"] { display: flex; }
    .dashboard-statistics .dashboard-stat { width: 100%; min-height: 120px; }
</style>';

$delivery_id = (int) ($_SESSION['admin_id'] ?? 0);

/* 1. DELIVERIES COMPLETED TODAY */
$todaysDeliveries = 0;
$stmtToday = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM completed_orders
    WHERE delivery_partner_id = ?
      AND DATE(completed_at) = CURDATE()
");
$stmtToday->bind_param('i', $delivery_id);
$stmtToday->execute();
$resToday = $stmtToday->get_result();
if ($resToday) {
    $todaysDeliveries = (int) $resToday->fetch_assoc()['total'];
}
$stmtToday->close();

/* 2. ASSIGNED ORDERS (Awaiting partner acceptance) */
$assignedOrders = 0;
$stmtAssigned = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE delivery_partner_id = ? AND order_status = 'assigned_to_delivery'
");
$stmtAssigned->bind_param('i', $delivery_id);
$stmtAssigned->execute();
$resAssigned = $stmtAssigned->get_result();
if ($resAssigned) {
    $assignedOrders = (int) $resAssigned->fetch_assoc()['total'];
}
$stmtAssigned->close();

/* 3. PENDING ORDERS (Accepted & actively in delivery process) */
$pendingOrders = 0;
$stmtPending = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE delivery_partner_id = ? AND order_status IN ('delivery_accepted', 'out_for_delivery')
");
$stmtPending->bind_param('i', $delivery_id);
$stmtPending->execute();
$resPending = $stmtPending->get_result();
if ($resPending) {
    $pendingOrders = (int) $resPending->fetch_assoc()['total'];
}
$stmtPending->close();

/* 4. COMPLETED DELIVERIES (archived and legacy unarchived rows) */
$completedDeliveries = 0;
$stmtComplete = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE delivery_partner_id = ? AND order_status IN ('delivered', 'completed')
      AND NOT EXISTS (SELECT 1 FROM completed_orders c WHERE c.order_id = orders.id)
");
$stmtComplete->bind_param('i', $delivery_id);
$stmtComplete->execute();
$resComplete = $stmtComplete->get_result();
if ($resComplete) {
    $completedDeliveries = (int) $resComplete->fetch_assoc()['total'];
}
$stmtComplete->close();
$stmtArchived = $conn->prepare("SELECT COUNT(*) AS total FROM completed_orders WHERE delivery_partner_id = ?");
$stmtArchived->bind_param('i', $delivery_id);
$stmtArchived->execute();
$resArchived = $stmtArchived->get_result();
if ($resArchived) {
    $completedDeliveries += (int) $resArchived->fetch_assoc()['total'];
}
$stmtArchived->close();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-wrapper">

    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="admin-content">

        <div class="page-title-section mb-4">
            <h2 class="fw-bold mb-1">Dashboard</h2>
            <p class="text-muted">Welcome to Darjibhai Delivery Partner Portal.</p>
        </div>

        <div class="row g-4 dashboard-statistics">

            <!-- 2. ASSIGNED ORDERS -->
            <div class="col-xl-3 col-md-6">
                <div class="dashboard-stat">
                    <div class="stat-info">
                        <span>Assigned Orders</span>
                        <strong><?= $assignedOrders ?></strong>
                    </div>
                    <div class="stat-icon" style="background: #eff6ff; color: #2563eb;">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                    </div>
                </div>
            </div>

            <!-- 3. PENDING ORDERS -->
            <div class="col-xl-3 col-md-6">
                <div class="dashboard-stat">
                    <div class="stat-info">
                        <span>Pending Orders</span>
                        <strong><?= $pendingOrders ?></strong>
                    </div>
                    <div class="stat-icon" style="background: #fffbeb; color: #d97706;">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                </div>
            </div>

            <!-- 4. COMPLETED DELIVERIES -->
            <div class="col-xl-3 col-md-6">
                <div class="dashboard-stat">
                    <div class="stat-info">
                        <span>Completed Orders</span>
                        <strong><?= $completedDeliveries ?></strong>
                    </div>
                    <div class="stat-icon" style="background: #f0fdf4; color: #16a34a;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

</div>
