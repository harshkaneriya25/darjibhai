<?php
require_once __DIR__ . '/../includes/user-auth.php';
require_once __DIR__ . '/../config/database.php';

$userId = (int) $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'Customer';

$countStmt = $conn->prepare("SELECT COUNT(*) AS total, SUM(order_status IN ('delivered', 'completed')) AS completed FROM orders WHERE user_id = ?");
$countStmt->bind_param('i', $userId);
$countStmt->execute();
$orderCounts = $countStmt->get_result()->fetch_assoc() ?: ['total' => 0, 'completed' => 0];
$countStmt->close();
$totalOrders = (int) $orderCounts['total'];
$completedOrders = (int) $orderCounts['completed'];
$activeOrders = $totalOrders - $completedOrders;

$recentStmt = $conn->prepare("SELECT o.order_no, o.order_status, o.delivery_date, o.created_at, p.name AS product_name FROM orders o LEFT JOIN products p ON p.id = o.product_id WHERE o.user_id = ? ORDER BY o.created_at DESC, o.id DESC LIMIT 5");
$recentStmt->bind_param('i', $userId);
$recentStmt->execute();
$recentOrders = $recentStmt->get_result();

function customerOrderStatus(string $status): array
{
    $label = $status === 'delivered'
        ? 'COMPLETED'
        : ($status === 'received_from_master'
            ? 'RECEIVED PRODUCT'
            : ($status === 'master_completed' ? 'READY FOR DELIVERY' : strtoupper(str_replace('_', ' ', $status))));
    $class = 'status-created';
    if (strpos($status, 'assigned') !== false) {
        $class = 'status-assigned';
    } elseif (strpos($status, 'accepted') !== false || $status === 'out_for_delivery') {
        $class = 'status-accepted';
    } elseif (in_array($status, ['master_completed', 'received_from_master'], true)) {
        $class = 'status-ready';
    } elseif (in_array($status, ['delivered', 'completed'], true)) {
        $class = 'status-completed';
    } elseif (strpos($status, 'declined') !== false || $status === 'cancelled') {
        $class = 'status-attention';
    }
    return [$label, $class];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Darjibhai</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/user-orders.css">
    <style>
        .dashboard-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px; }
        .dashboard-stat { display: flex; align-items: center; gap: 15px; padding: 21px 24px; border: 1px solid #ececec; border-radius: 16px; background: #fff; box-shadow: 0 8px 28px rgba(0,0,0,.04); transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
        .dashboard-stat:hover { transform: translateY(-3px); border-color: #f4c39f; box-shadow: 0 14px 30px rgba(0,0,0,.09); }
        .dashboard-stat-icon { display: grid; width: 48px; height: 48px; flex: 0 0 48px; place-items: center; border-radius: 14px; background: #fff1e6; color: #ed7115; font-size: 19px; }
        .dashboard-stat strong { display: block; color: #222; font-size: 24px; line-height: 1.1; }
        .dashboard-stat span { display: block; margin-top: 5px; color: #777; font-size: 12px; }
        .dashboard-empty { padding: 42px 20px; color: #777; text-align: center; }
        .dashboard-empty i { display: block; margin-bottom: 12px; color: #f58220; font-size: 30px; }
        @media (max-width: 700px) { .dashboard-stats { grid-template-columns: 1fr; gap: 11px; } .dashboard-stat { padding: 16px 19px; } }
    </style>
</head>
<body class="orders-body">
<header class="site-header orders-site-header">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand darji-logo" href="/darjibhai/index.php">
                <span class="logo-icon"><i class="fa-solid fa-scissors"></i></span>
                <span>Darji<span>bhai</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#ordersNavbar" aria-controls="ordersNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="ordersNavbar">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="/darjibhai/index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="/darjibhai/user/dashboard.php" aria-current="page">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/darjibhai/user/orders.php">My Orders</a></li>
                </ul>
                <div class="navbar-buttons">
                    <a href="/darjibhai/user/profile.php" class="btn-login"><i class="fa-regular fa-user me-1"></i> My Profile</a>
                    <a href="/darjibhai/auth/user-logout.php" class="btn-register">Logout</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<div class="orders-page-shell">
    <main class="orders-content">
        <section class="orders-page-heading">
            <div class="orders-heading-copy">
                <span class="orders-eyebrow"><i class="fa-solid fa-scissors"></i> DARJIBHAI CUSTOMER PORTAL</span>
                <h1>Welcome, <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></h1>
                <p>Track your tailoring orders.</p>
            </div>
            <a href="/darjibhai/user/create-order.php" class="btn-orange orders-new-button"><i class="fa-solid fa-plus"></i> Place New Order</a>
        </section>

        <section class="dashboard-stats" aria-label="Order counts">
            <div class="dashboard-stat"><div class="dashboard-stat-icon"><i class="fa-solid fa-box"></i></div><div><strong><?= $totalOrders ?></strong><span>Total orders</span></div></div>
            <div class="dashboard-stat"><div class="dashboard-stat-icon"><i class="fa-solid fa-person-running"></i></div><div><strong><?= $activeOrders ?></strong><span>In progress</span></div></div>
            <div class="dashboard-stat"><div class="dashboard-stat-icon"><i class="fa-solid fa-circle-check"></i></div><div><strong><?= $completedOrders ?></strong><span>Completed</span></div></div>
        </section>

        <section class="orders-panel">
            <div class="orders-panel-heading">
                <div><h2>Recent orders</h2><p>Your latest five orders</p></div>
                <a href="/darjibhai/user/orders.php" class="orders-view-button">View all orders <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>
                <div class="table-responsive orders-table-wrap">
                    <table class="table orders-table">
                        <thead><tr><th>Order</th><th>Product</th><th>Required by</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php while ($order = $recentOrders->fetch_assoc()): [$statusLabel, $statusClass] = customerOrderStatus($order['order_status']); ?>
                            <tr>
                                <td class="orders-number-cell"><strong><?= htmlspecialchars($order['order_no'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td class="orders-product-cell"><strong><?= htmlspecialchars($order['product_name'] ?? 'Product unavailable', ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td class="orders-date-cell"><i class="fa-regular fa-calendar"></i><?= !empty($order['delivery_date']) ? date('d M Y', strtotime($order['delivery_date'])) : 'Not set' ?></td>
                                <td><span class="status-pill <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>"><span class="status-dot"></span><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="dashboard-empty"><i class="fa-solid fa-shirt"></i><strong>No orders yet</strong><p class="mb-0 mt-2">Your recent orders will appear here after you place one.</p></div>
            <?php endif; ?>
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $recentStmt->close(); ?>
