<?php

require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../config/database.php';

$page_title = 'Admin Dashboard';
$admin_page = true;
$extra_css = '<style>
    .dashboard-statistics > [class*="col-"] { display: flex; }
    .dashboard-statistics .dashboard-stat { width: 100%; min-height: 132px; box-sizing: border-box; }
    .dashboard-statistics .stat-info { min-width: 0; }
    .dashboard-statistics .stat-info span { line-height: 1.35; }
    .recent-order-status { font-weight: 700; letter-spacing: .02em; white-space: nowrap; }
    .recent-status-created { background: #f1f5f9; color: #475569; }
    .recent-status-assigned { background: #e0f2fe; color: #0369a1; }
    .recent-status-progress { background: #fef3c7; color: #a16207; }
    .recent-status-completed { background: #dcfce7; color: #15803d; }
    .recent-status-declined { background: #fee2e2; color: #b91c1c; }
    .recent-status-cancelled { background: #e2e8f0; color: #334155; }
    .recent-status-default { background: #ede9fe; color: #6d28d9; }
</style>';

/* Dashboard totals */
$stats = ['total' => 0, 'today' => 0, 'pending' => 0, 'completed' => 0];
$statsResult = $conn->query("SELECT
    COUNT(*) AS total,
    (SELECT COUNT(*) FROM (
        SELECT id AS order_id FROM orders WHERE DATE(created_at) = CURDATE()
        UNION
        SELECT order_id FROM completed_orders WHERE DATE(created_at) = CURDATE()
    ) AS todays_orders) AS today,
    COALESCE(SUM(order_status NOT IN ('completed', 'delivered', 'cancelled')), 0) AS pending,
    COALESCE(SUM(order_status IN ('completed', 'delivered')), 0) AS completed
    FROM orders");
if ($statsResult) {
    $stats = array_map('intval', $statsResult->fetch_assoc());
}

/* Allow selecting any year with orders, plus the current year. */
$availableYears = [];
$yearResult = $conn->query("SELECT DISTINCT YEAR(created_at) AS order_year FROM orders WHERE created_at IS NOT NULL ORDER BY order_year DESC");
if ($yearResult) {
    while ($yearRow = $yearResult->fetch_assoc()) {
        $availableYears[] = (int) $yearRow['order_year'];
    }
}
$currentYear = (int) date('Y');
if (!in_array($currentYear, $availableYears, true)) {
    $availableYears[] = $currentYear;
    rsort($availableYears);
}
$selectedYear = filter_var($_GET['year'] ?? $currentYear, FILTER_VALIDATE_INT);
if ($selectedYear === false || !in_array((int) $selectedYear, $availableYears, true)) {
    $selectedYear = $currentYear;
}
$selectedYear = (int) $selectedYear;

$monthlyOrders = array_fill(1, 12, 0);
$chartQuery = $conn->prepare("SELECT MONTH(created_at) AS order_month, COUNT(*) AS total
    FROM orders WHERE YEAR(created_at) = ? GROUP BY MONTH(created_at)");
$chartQuery->bind_param('i', $selectedYear);
$chartQuery->execute();
$chartResult = $chartQuery->get_result();
while ($monthRow = $chartResult->fetch_assoc()) {
    $monthlyOrders[(int) $monthRow['order_month']] = (int) $monthRow['total'];
}
$chartQuery->close();

$recentOrders = [];
$recentResult = $conn->query("SELECT o.id, o.order_no, o.order_status, o.created_at,
        u.name AS customer_name, p.name AS product_name, j.name AS job_description_name
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    LEFT JOIN products p ON p.id = o.product_id
    LEFT JOIN job_descriptions j ON j.id = o.job_description_id
    ORDER BY o.created_at DESC, o.id DESC LIMIT 8");
if ($recentResult) {
    while ($recentOrder = $recentResult->fetch_assoc()) {
        $recentOrders[] = $recentOrder;
    }
}

$extra_js = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const canvas = document.getElementById("monthlyOrdersChart");
    if (!canvas || typeof Chart === "undefined") return;
    new Chart(canvas, {
        type: "bar",
        data: {
            labels: ' . json_encode(array_values(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'])) . ',
            datasets: [{
                label: "Orders",
                data: ' . json_encode(array_values($monthlyOrders)) . ',
                backgroundColor: "rgba(245, 130, 32, 0.72)",
                borderColor: "#f58220",
                borderWidth: 1,
                borderRadius: 6,
                maxBarThickness: 42
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-wrapper">

    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="admin-content">

        <div class="page-title-section mb-4">
            <h2 class="fw-bold mb-1">Dashboard</h2>
            <p class="text-muted">Welcome to Darjibhai Order Management System.</p>
        </div>

        <div class="row g-4 dashboard-statistics mb-4">
            
            <!-- TOTAL ORDERS -->
            <div class="col-xl-3 col-md-6">
                <div class="dashboard-stat">
                    <div class="stat-info">
                        <span>Total Orders</span>
                        <strong><?= $stats['total'] ?></strong>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                </div>
            </div>

            <!-- TODAY'S ORDERS -->
            <div class="col-xl-3 col-md-6">
                <div class="dashboard-stat">
                    <div class="stat-info">
                        <span>Today's Orders</span>
                        <strong><?= $stats['today'] ?></strong>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-calendar-day"></i></div>
                </div>
            </div>

            <!-- PENDING ORDERS -->
            <div class="col-xl-3 col-md-6">
                <div class="dashboard-stat">
                    <div class="stat-info">
                        <span>Pending Orders</span>
                        <strong><?= $stats['pending'] ?></strong>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                </div>
            </div>

            <!-- COMPLETED ORDERS -->
            <div class="col-xl-3 col-md-6">
                <div class="dashboard-stat">
                    <div class="stat-info">
                        <span>Completed Orders</span>
                        <strong><?= $stats['completed'] ?></strong>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>

        </div>

        <div class="row g-4">
            <div class="col-xl-7">
                <section class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                            <div>
                                <h5 class="fw-bold mb-1">Orders by Month</h5>
                                <p class="text-muted small mb-0">Order volume during the selected year</p>
                            </div>
                            <form method="GET" class="d-flex align-items-center gap-2">
                                <label for="dashboardYear" class="small text-muted">Year</label>
                                <select id="dashboardYear" name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <?php foreach ($availableYears as $year): ?>
                                        <option value="<?= $year ?>" <?= $year === $selectedYear ? 'selected' : '' ?>><?= $year ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>
                        <div style="height: 300px; position: relative;">
                            <canvas id="monthlyOrdersChart" aria-label="Monthly orders for <?= $selectedYear ?>" role="img"></canvas>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-xl-5">
                <section class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-1">Recent Orders</h5>
                                <p class="text-muted small mb-0">Latest orders</p>
                            </div>
                            <a href="/darjibhai/admin/orders/index.php" class="btn btn-sm btn-outline-warning">All Orders</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Order</th><th>Customer</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    <?php if ($recentOrders): foreach ($recentOrders as $order): ?>
                                        <?php
                                            $orderStatus = $order['order_status'] ?? 'unknown';
                                            $displayOrderStatus = $orderStatus === 'delivered'
                                                ? 'Completed'
                                                : ($orderStatus === 'received_from_master'
                                                    ? 'Received Product'
                                                    : ($orderStatus === 'master_completed' ? 'Ready for Delivery' : ucwords(str_replace('_', ' ', $orderStatus))));
                                            if (in_array($orderStatus, ['created'], true)) {
                                                $statusColor = 'recent-status-created';
                                            } elseif (in_array($orderStatus, ['assigned_to_master', 'assigned_to_delivery'], true)) {
                                                $statusColor = 'recent-status-assigned';
                                            } elseif (in_array($orderStatus, ['master_accepted', 'master_completed', 'received_from_master', 'delivery_accepted', 'out_for_delivery'], true)) {
                                                $statusColor = 'recent-status-progress';
                                            } elseif (in_array($orderStatus, ['completed', 'delivered'], true)) {
                                                $statusColor = 'recent-status-completed';
                                            } elseif (strpos($orderStatus, 'declined') !== false) {
                                                $statusColor = 'recent-status-declined';
                                            } elseif ($orderStatus === 'cancelled') {
                                                $statusColor = 'recent-status-cancelled';
                                            } else {
                                                $statusColor = 'recent-status-default';
                                            }
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($order['order_no'] ?? '-') ?></strong>
                                            </td>
                                            <td><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></td>
                                            <td><span class="badge recent-order-status <?= $statusColor ?>"><?= htmlspecialchars($displayOrderStatus) ?></span></td>
                                        </tr>
                                    <?php endforeach; else: ?>
                                        <tr><td colspan="3" class="text-center text-muted py-4">No orders yet.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>

    </main>

    ?>
<script src="/darjibhai/assets/js/form-validation.js" defer></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>

</div>
