<?php

require_once __DIR__ . '/../includes/master-auth.php';
require_once __DIR__ . '/../config/database.php';

$page_title = 'Master Dashboard';
$admin_page = true;

$master_id = (int) ($_SESSION['admin_id'] ?? 0);

/* Orders awaiting acceptance by this Master. */
$assignedOrders = 0;
$stmtAssigned = $conn->prepare(" 
    SELECT COUNT(*) AS total
    FROM orders
    WHERE master_id = ? AND order_status = 'assigned_to_master'
");
$stmtAssigned->bind_param('i', $master_id);
$stmtAssigned->execute();
$resAssigned = $stmtAssigned->get_result();
if ($resAssigned) {
    $assignedOrders = (int) $resAssigned->fetch_assoc()['total'];
}
$stmtAssigned->close();

/* Orders currently being worked on. */
$inProgressOrders = 0;
$stmtInProgress = $conn->prepare(" 
    SELECT COUNT(*) AS total
    FROM orders
    WHERE master_id = ? AND order_status = 'master_accepted'
");
$stmtInProgress->bind_param('i', $master_id);
$stmtInProgress->execute();
$resInProgress = $stmtInProgress->get_result();
if ($resInProgress) {
    $inProgressOrders = (int) $resInProgress->fetch_assoc()['total'];
}
$stmtInProgress->close();

/* COMPLETED ORDERS (Completed by this Master) */
$completedOrders = 0;
$stmtComplete = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE master_id = ? AND order_status IN ('master_completed', 'received_from_master', 'assigned_to_delivery', 'delivery_accepted', 'delivered', 'completed')
");
$stmtComplete->bind_param('i', $master_id);
$stmtComplete->execute();
$resComplete = $stmtComplete->get_result();
if ($resComplete) {
    $completedOrders = (int) $resComplete->fetch_assoc()['total'];
}
$stmtComplete->close();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-wrapper">

    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="admin-content">

        <div class="page-title-section mb-4">
            <h2 class="fw-bold mb-1">Master Dashboard</h2>
            <p class="text-muted">Welcome to your tailoring workstation.</p>
        </div>

        <div class="row g-4 dashboard-statistics">
            
            <!-- TODAY'S ORDERS -->
            <div class="col-xl-4 col-md-6">
                <div class="dashboard-stat">
                    <div class="stat-info">
                        <span>Assigned Orders</span>
                        <strong><?= $assignedOrders ?></strong>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-calendar-day"></i>
                    </div>
                </div>
            </div>

            <!-- PENDING ORDERS -->
            <div class="col-xl-4 col-md-6">
                <div class="dashboard-stat">
                    <div class="stat-info">
                        <span>In Progress</span>
                        <strong><?= $inProgressOrders ?></strong>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                </div>
            </div>

            <!-- COMPLETED ORDERS -->
            <div class="col-xl-4 col-md-6">
                <div class="dashboard-stat">
                    <div class="stat-info">
                        <span>Completed</span>
                        <strong><?= $completedOrders ?></strong>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

</div>
