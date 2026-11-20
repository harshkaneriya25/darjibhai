<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Guard: User must be logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../auth/user-login.php");
    exit;
}

$user_id   = (int) $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Customer';

$successMessage = $_SESSION['order_placed_success'] ?? '';
unset($_SESSION['order_placed_success']);

// Retrieve ONLY orders belonging to this logged-in user
$stmt = $conn->prepare("
    SELECT 
        o.id, o.order_no, o.user_id, o.product_id, o.job_description_id,
        o.demo_image, o.delivery_date, o.body_measurements, o.order_status,
        o.master_id, o.delivery_partner_id, o.created_at,
        p.name AS product_name,
        j.name AS job_description_name,
        m.name AS master_name,
        d.name AS delivery_partner_name
    FROM orders o
    LEFT JOIN products p ON p.id = o.product_id
    LEFT JOIN job_descriptions j ON j.id = o.job_description_id
    LEFT JOIN admins m ON m.id = o.master_id
    LEFT JOIN admins d ON d.id = o.delivery_partner_id
    WHERE o.user_id = ?
    ORDER BY o.id DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$ordersResult = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Darjibhai</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/user-orders.css">
    <style>
        .status-pill {
            padding: 4px 10px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 6px;
        }
        .status-created { background: #f1f5f9; color: #475569; }
        .status-assigned { background: #e0f2fe; color: #0284c7; }
        .status-accepted { background: #fef9c3; color: #a16207; }
        .status-completed { background: #dcfce7; color: #15803d; }
        .status-ready { background: #f3e8ff; color: #7e22ce; }
        .status-attention { background: #fee2e2; color: #b91c1c; }
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
                        <li class="nav-item"><a class="nav-link" href="/darjibhai/index.php#home">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="/darjibhai/user/dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link active" href="/darjibhai/user/orders.php" aria-current="page">My Orders</a></li>
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
                <h1>My Orders</h1>
                <p>Follow your tailoring orders and view their details in one place.</p>
            </div>
            <a href="/darjibhai/user/create-order.php" class="btn-orange orders-new-button">
                <i class="fa-solid fa-plus"></i> Place New Order
            </a>
        </section>

        <?php if ($successMessage): ?>
            <div class="alert alert-success alert-dismissible fade show orders-alert" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($successMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <section class="orders-panel">
            <div class="orders-panel-heading">
                <div>
                    <h2>Your order history</h2>
                    <p>Orders List</p>
                </div>
                <span class="orders-count"><i class="fa-solid fa-box"></i> <?= $ordersResult ? (int) $ordersResult->num_rows : 0 ?> orders</span>
            </div>

            <?php if ($ordersResult && $ordersResult->num_rows > 0): ?>
                <div class="table-responsive orders-table-wrap">
                    <table class="table orders-table">
                        <thead>
                            <tr>
                                <th class="orders-number-col">Order</th>
                                <th>Product</th>
                                <th class="orders-date-col">Required by</th>
                                <th class="orders-status-col">Status</th>
                                <th class="orders-action-col text-end">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $srNo = 1; while ($ord = $ordersResult->fetch_assoc()):
                                $rawStatus = $ord['order_status'];
                                $displayStatus = $rawStatus === 'delivered'
                                    ? 'COMPLETED'
                                    : ($rawStatus === 'received_from_master'
                                        ? 'RECEIVED PRODUCT'
                                        : ($rawStatus === 'master_completed' ? 'READY FOR DELIVERY' : strtoupper(str_replace('_', ' ', $rawStatus))));
                                $statusClass = 'status-created';
                                if (strpos($rawStatus, 'assigned') !== false) {
                                    $statusClass = 'status-assigned';
                                } elseif (strpos($rawStatus, 'accepted') !== false || $rawStatus === 'out_for_delivery') {
                                    $statusClass = 'status-accepted';
                                } elseif (in_array($rawStatus, ['master_completed', 'received_from_master'], true)) {
                                    $statusClass = 'status-ready';
                                } elseif (in_array($rawStatus, ['delivered', 'completed'], true)) {
                                    $statusClass = 'status-completed';
                                } elseif (strpos($rawStatus, 'declined') !== false || $rawStatus === 'cancelled') {
                                    $statusClass = 'status-attention';
                                }
                            ?>
                                <tr>
                                    <td class="orders-number-cell">
                                        <span class="orders-number-label">#<?= str_pad((string) $srNo++, 2, '0', STR_PAD_LEFT) ?></span>
                                        <strong><?= htmlspecialchars($ord['order_no']) ?></strong>
                                    </td>
                                    <td class="orders-product-cell">
                                        <strong><?= htmlspecialchars($ord['product_name'] ?? 'Product unavailable') ?></strong>
                                    </td>
                                    <td class="orders-date-cell">
                                        <i class="fa-regular fa-calendar"></i>
                                        <?= !empty($ord['delivery_date']) ? date('d M Y', strtotime($ord['delivery_date'])) : 'Not set' ?>
                                    </td>
                                    <td><span class="status-pill <?= $statusClass ?>"><span class="status-dot"></span><?= htmlspecialchars($displayStatus) ?></span></td>
                                    <td class="text-end">
                                        <button type="button" class="orders-view-button" data-bs-toggle="modal" data-bs-target="#orderModal" data-order='<?= json_encode($ord, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>' aria-label="View order <?= htmlspecialchars($ord['order_no']) ?> details">
                                            <i class="fa-regular fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="orders-empty-state">
                    <div class="orders-empty-icon"><i class="fa-solid fa-shirt"></i></div>
                    <h3>No orders yet</h3>
                    <p>Your tailoring orders will appear here after you place one.</p>
                    <a href="/darjibhai/user/create-order.php" class="btn-orange orders-new-button"><i class="fa-solid fa-plus"></i> Place Your First Order</a>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<!-- Modal for Measurement & Order Details -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="mOrderNo">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="order-detail-grid">
                    <div class="order-detail-item"><small>Product</small><strong id="mProduct">-</strong></div>
                    <div class="order-detail-item"><small>Service</small><strong id="mJob">-</strong></div>
                    <div class="order-detail-item"><small>Required by</small><strong id="mDate">-</strong></div>
                    <div class="order-detail-item"><small>Master</small><strong id="mMaster">-</strong></div>
                    <div class="order-detail-item"><small>Delivery partner</small><strong id="mDelivery">-</strong></div>
                    <div class="order-detail-item"><small>Product status</small><strong id="mProductStatus">-</strong></div>
                </div>
                <div class="measurements-heading"><i class="fa-solid fa-ruler-combined"></i><h6>Body measurements</h6></div>
                <div id="mMeasurements" class="measurement-list"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('orderModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function (e) {
            var data = JSON.parse(e.relatedTarget.getAttribute('data-order'));
            document.getElementById('mOrderNo').textContent = data.order_no;
            document.getElementById('mProduct').textContent = data.product_name || '-';
            document.getElementById('mJob').textContent = data.job_description_name || '-';
            document.getElementById('mDate').textContent = data.delivery_date || '-';
            document.getElementById('mMaster').textContent = data.master_name || 'Not assigned yet';
            document.getElementById('mDelivery').textContent = data.delivery_partner_name || 'Not assigned yet';
            var isProductComplete = ['master_completed', 'received_from_master', 'assigned_to_delivery', 'delivery_accepted', 'out_for_delivery', 'delivered', 'completed'].indexOf(data.order_status) !== -1;
            document.getElementById('mProductStatus').textContent = isProductComplete ? 'Completed' : 'In progress';

            var mBox = document.getElementById('mMeasurements');
            mBox.innerHTML = '';
            try {
                var m = JSON.parse(data.body_measurements || '{}');
                var hasMeasurements = false;
                var groups = [
                    ['upper_body', 'Upper body'],
                    ['middle_body', 'Middle body'],
                    ['lower_body', 'Lower body']
                ];
                groups.forEach(function (groupInfo) {
                    var group = groupInfo[0];
                    if (!m[group] || typeof m[group] !== 'object') return;
                    var section = document.createElement('section');
                    section.className = 'measurement-group';
                    var heading = document.createElement('h6');
                    heading.textContent = groupInfo[1];
                    section.appendChild(heading);
                    var items = document.createElement('div');
                    items.className = 'measurement-group-items';
                    for (var part in m[group]) {
                        if (m[group][part]) {
                            hasMeasurements = true;
                            var item = document.createElement('div');
                            item.className = 'measurement-chip';
                            var label = document.createElement('span');
                            label.textContent = part.replace(/_/g, ' ');
                            var value = document.createElement('strong');
                            value.textContent = m[group][part] + ' in';
                            item.append(label, value);
                            items.appendChild(item);
                        }
                    }
                    if (items.children.length) { section.appendChild(items); mBox.appendChild(section); hasMeasurements = true; }
                });
                if (!hasMeasurements) mBox.textContent = 'No measurements provided.';
            } catch(err) {
                mBox.textContent = 'No measurements provided.';
            }
        });
    }
});
</script>
</body>
</html>
