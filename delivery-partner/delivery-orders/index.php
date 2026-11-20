<?php

require_once __DIR__ . '/../../includes/delivery-auth.php';
require_once __DIR__ . '/../../config/database.php';

$page_title = 'Delivery Orders';
$admin_page = true;

$extra_js = '
<script src="/darjibhai/assets/js/admin/table.js"></script>
';

$delivery_id = (int) ($_SESSION['admin_id'] ?? 0);

$successMessage = $_SESSION['delivery_order_success'] ?? '';
$errorMessage   = $_SESSION['delivery_order_error'] ?? '';

unset($_SESSION['delivery_order_success'], $_SESSION['delivery_order_error']);

/*
|--------------------------------------------------------------------------
| HANDLE DELIVERY ACTIONS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $action  = $_POST['action'];
    $orderId = (int) ($_POST['order_id'] ?? 0);

    if ($orderId <= 0) {
        $_SESSION['delivery_order_error'] = 'Invalid order selected.';
    } else {

        /* 1. ACCEPT DELIVERY */
        if ($action === 'accept') {

            $stmt = $conn->prepare("
                UPDATE orders 
                SET order_status = 'delivery_accepted' 
                WHERE id = ? AND delivery_partner_id = ? AND order_status = 'assigned_to_delivery'
            ");
            $stmt->bind_param('ii', $orderId, $delivery_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $hist = $conn->prepare("INSERT INTO order_status_history (order_id, status, description) VALUES (?, 'delivery_accepted', 'Delivery partner accepted the order.')");
                $hist->bind_param('i', $orderId);
                $hist->execute();
                $hist->close();

                $_SESSION['delivery_order_success'] = 'Order Accepted.';
            } else {
                $_SESSION['delivery_order_error'] = 'Something Went Wrong.';
            }
            $stmt->close();
        }

        /* 2. DECLINE DELIVERY */
        elseif ($action === 'decline') {

            $stmt = $conn->prepare("
                UPDATE orders 
                SET order_status = 'delivery_declined', delivery_partner_id = NULL 
                WHERE id = ? AND delivery_partner_id = ? AND order_status = 'assigned_to_delivery'
            ");
            $stmt->bind_param('ii', $orderId, $delivery_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $hist = $conn->prepare("INSERT INTO order_status_history (order_id, status, description) VALUES (?, 'delivery_declined', 'Delivery partner declined the order.')");
                $hist->bind_param('i', $orderId);
                $hist->execute();
                $hist->close();

                $_SESSION['delivery_order_success'] = 'Order declined and returned to Admin queue.';
            } else {
                $_SESSION['delivery_order_error'] = 'Failed to decline order.';
            }
            $stmt->close();
        }

        /* 3. MARK AS DELIVERED / COMPLETED & STORE IN COMPLETED TABLE */
        elseif ($action === 'complete') {
            $stmt = $conn->prepare("UPDATE orders SET order_status = 'delivered' WHERE id = ? AND delivery_partner_id = ? AND order_status = 'delivery_accepted'");
            $stmt->bind_param('ii', $orderId, $delivery_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                if (archiveCompletedOrder($conn, $orderId)) {
                    $hist = $conn->prepare("INSERT INTO order_status_history (order_id, status, description) VALUES (?, 'delivered', 'Order successfully delivered to customer.')");
                    $hist->bind_param('i', $orderId);
                    $historySaved = $hist->execute();
                    $hist->close();

                    if ($historySaved) {
                        $_SESSION['delivery_order_success'] = 'Order Completed.';
                    } else {
                        $_SESSION['delivery_order_error'] = 'Something Went Wrong .';
                    }
                } else {
                    // Keep the order eligible for retry if the archive write fails.
                    $rollback = $conn->prepare("UPDATE orders SET order_status = 'delivery_accepted' WHERE id = ? AND delivery_partner_id = ? AND order_status = 'delivered'");
                    $rollback->bind_param('ii', $orderId, $delivery_id);
                    $rollback->execute();
                    $rollback->close();
                    $_SESSION['delivery_order_error'] = 'Could not archive the completed order. Please retry.';
                }
            } else {
                $_SESSION['delivery_order_error'] = 'Failed to mark order as delivered.';
            }
            $stmt->close();
        }
    }

    header('Location: /darjibhai/delivery-partner/delivery-orders/index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| FETCH ORDERS FOR THIS DELIVERY PARTNER
|--------------------------------------------------------------------------
*/

$delQuery = $conn->prepare("
    SELECT 
        o.id, o.order_no, o.user_id, o.product_id, o.job_description_id,
        o.demo_image, o.delivery_date, o.body_measurements, o.order_status,
        o.master_id, o.delivery_partner_id, o.created_at,
        u.name AS customer_name,
        u.phone AS customer_phone,
        u.email AS customer_email,
        u.address AS customer_address,
        p.name AS product_name,
        j.name AS job_description_name
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    LEFT JOIN products p ON p.id = o.product_id
    LEFT JOIN job_descriptions j ON j.id = o.job_description_id
    WHERE o.delivery_partner_id = ?
    ORDER BY o.id DESC
");
$delQuery->bind_param('i', $delivery_id);
$delQuery->execute();
$ordersResult = $delQuery->get_result();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<style>
.admin-table-card, .table-responsive, .admin-table {
    overflow: visible !important;
}

.status-pill {
    display: inline-block;
    padding: 0.28rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-radius: 6px;
}
.status-assigned { background: #e0f2fe; color: #0284c7; }
.status-accepted { background: #fef9c3; color: #a16207; }
.status-completed { background: #dcfce7; color: #15803d; }
.status-declined { background: #fee2e2; color: #b91c1c; }

.action-dropdown-btn {
    width: 34px;
    height: 34px;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    background: transparent !important;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 0;
}
.action-dropdown-btn:hover {
    background-color: #f1f5f9 !important;
    color: #0f172a !important;
}
.action-dropdown-btn::after {
    display: none !important;
}

.action-dropdown-menu {
    border-radius: 12px;
    border: 1px solid #edf2f7;
    box-shadow: 0 14px 35px -5px rgba(15, 23, 42, 0.15);
    padding: 6px;
    min-width: 190px;
    z-index: 1060 !important;
}
.action-dropdown-menu .dropdown-item {
    font-size: 0.88rem;
    font-weight: 500;
    padding: 8px 14px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #334155;
}
.action-dropdown-menu .dropdown-item:hover {
    background-color: #f8fafc;
    color: #0f172a;
}

.view-order-card {
    background: #ffffff;
    border: 1px solid #edf2f7;
    border-radius: 14px;
    padding: 18px 20px;
    height: 100%;
}
.view-card-title {
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.info-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 12px;
}
.info-icon-bubble {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.bubble-blue { background: #eff6ff; color: #2563eb; }
.bubble-green { background: #f0fdf4; color: #16a34a; }
.bubble-purple { background: #faf5ff; color: #9333ea; }
.bubble-orange { background: #fff7ed; color: #ea580c; }
.info-text span {
    display: block;
    font-size: 0.72rem;
    text-transform: uppercase;
    font-weight: 700;
    color: #94a3b8;
}
.info-text strong {
    font-size: 0.94rem;
    color: #1e293b;
    font-weight: 600;
}
.order-header-bar {
    background: #fff8f1;
    border: 1px solid #fed7aa;
    border-radius: 12px;
    padding: 12px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
</style>

<div class="main-wrapper">

    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="admin-content">

        <div class="page-title-section">
            <h3>Delivery Orders</h3>
            <p><b>Delivery Portal</b> > Assigned Orders</p>
        </div>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($successMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($errorMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="admin-table-card">

            <div class="table-header">
                <div class="table-search">
                    <i class="fa-solid fa-search"></i>
                    <input
                        type="text"
                        placeholder="Search deliveries..."
                        data-table-search="deliveryOrdersTable"
                    >
                </div>
            </div>

            <div class="table-responsive">
                <table class="table admin-table" id="deliveryOrdersTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Sr No.</th>
                            <th>Order No</th>
                            <th>Customer</th>
                            <th>Phone Number</th>
                            <th>Delivery Date</th>
                            <th>Status</th>
                            <th style="width: 70px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php if ($ordersResult && $ordersResult->num_rows > 0): ?>
                            <?php 
                            $srNo = 1; 
                            while ($ord = $ordersResult->fetch_assoc()): 
                                $statusLabel = 'Assigned';
                                $statusClass = 'status-assigned';

                                switch ($ord['order_status']) {
                                    case 'delivery_accepted':
                                        $statusLabel = 'Accepted';
                                        $statusClass = 'status-accepted';
                                        break;
                                    case 'delivery_declined':
                                        $statusLabel = 'Declined';
                                        $statusClass = 'status-declined';
                                        break;
                                    case 'delivered':
                                    case 'completed':
                                        $statusLabel = 'Completed';
                                        $statusClass = 'status-completed';
                                        break;
                                    default:
                                        $statusLabel = 'Assigned';
                                        $statusClass = 'status-assigned';
                                        break;
                                }
                            ?>
                                <tr>
                                    <td><?= $srNo++ ?></td>
                                    <td><strong><?= htmlspecialchars($ord['order_no']) ?></strong></td>
                                    <td>
                                        <strong><?= htmlspecialchars($ord['customer_name'] ?? 'N/A') ?></strong>
                                        <br>
                                    </td>
                                    <td><?= htmlspecialchars($ord['customer_phone'] ?? '-') ?></td>
                                    <td>
                                        <?= !empty($ord['delivery_date']) ? date('d M, Y', strtotime($ord['delivery_date'])) : '-' ?>
                                    </td>
                                    <td>
                                        <span class="status-pill <?= $statusClass ?>">
                                            <?= htmlspecialchars($statusLabel) ?>
                                        </span>
                                    </td>

                                    <!-- BORDERLESS DROPDOWN ACTION MENU -->
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button 
                                                type="button" 
                                                class="action-dropdown-btn dropdown-toggle" 
                                                data-bs-toggle="dropdown" 
                                                data-bs-boundary="viewport"
                                                aria-expanded="false"
                                            >
                                                <i class="fa-solid fa-ellipsis-vertical fs-5"></i>
                                            </button>
                                            
                                            <ul class="dropdown-menu dropdown-menu-end action-dropdown-menu shadow">
                                                <?php if ($ord['order_status'] === 'assigned_to_delivery'): ?>
                                                    <li>
                                                        <form method="POST" class="m-0 p-0">
                                                            <input type="hidden" name="action" value="accept">
                                                            <input type="hidden" name="order_id" value="<?= (int)$ord['id'] ?>">
                                                            <button type="submit" class="dropdown-item text-primary border-0 bg-transparent w-100">
                                                                <i class="fa-solid fa-check me-2"></i> Accept Order
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="m-0 p-0">
                                                            <input type="hidden" name="action" value="decline">
                                                            <input type="hidden" name="order_id" value="<?= (int)$ord['id'] ?>">
                                                            <button type="submit" class="dropdown-item text-danger border-0 bg-transparent w-100">
                                                                <i class="fa-solid fa-xmark me-2"></i> Decline Order
                                                            </button>
                                                        </form>
                                                    </li>
                                                <?php elseif ($ord['order_status'] === 'delivery_accepted'): ?>
                                                    <li>
                                                        <form method="POST" class="m-0 p-0">
                                                            <input type="hidden" name="action" value="complete">
                                                            <input type="hidden" name="order_id" value="<?= (int)$ord['id'] ?>">
                                                            <button type="submit" class="dropdown-item text-success border-0 bg-transparent w-100">
                                                                <i class="fa-solid fa-circle-check me-2"></i> Mark as Complete
                                                            </button>
                                                        </form>
                                                    </li>
                                                <?php endif; ?>

                                                <li>
                                                    <a 
                                                        class="dropdown-item" 
                                                        href="javascript:void(0);"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewOrderModal"
                                                        data-order='<?= json_encode($ord, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                                        data-display-status="<?= htmlspecialchars($statusLabel) ?>"
                                                        data-status-class="<?= htmlspecialchars($statusClass) ?>"
                                                    >
                                                        <i class="fa-solid fa-eye text-muted me-2"></i> View Details
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">
                                    <div class="table-empty">
                                        <i class="fa-solid fa-truck"></i>
                                        <p>No Delivery Orders</p>
                                        <span>You currently have no delivery tasks assigned.</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>

        </div>

    </main>

</div>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Delivery Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="order-header-bar">
                    <div>
                        <span class="text-muted small d-block">Order Identification</span>
                        <h5 id="viewOrderTitle" class="fw-bold mb-0">ORD-0000</h5>
                    </div>
                    <div class="text-end">
                        <span id="viewOrderStatusBadge" class="status-pill status-assigned mb-1">Assigned</span>
                        <div class="small text-muted">
                            Target: <strong id="viewDeliveryDate" class="text-dark">-</strong>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="view-order-card">
                            <span class="view-card-title"><i class="fa-solid fa-user text-primary"></i> Customer Details</span>
                            <div class="info-item">
                                <div class="info-icon-bubble bubble-blue"><i class="fa-solid fa-id-card"></i></div>
                                <div class="info-text">
                                    <span>Recipient Name</span>
                                    <strong id="viewCustomerName">-</strong>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon-bubble bubble-green"><i class="fa-solid fa-phone"></i></div>
                                <div class="info-text">
                                    <span>Contact Phone</span>
                                    <strong id="viewCustomerPhone">-</strong>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon-bubble bubble-orange"><i class="fa-solid fa-location-dot"></i></div>
                                <div class="info-text">
                                    <span>Delivery Address</span>
                                    <strong id="viewCustomerAddress" class="text-dark">-</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="view-order-card">
                            <span class="view-card-title"><i class="fa-solid fa-box text-warning"></i> Package Details</span>
                            <div class="info-item">
                                <div class="info-icon-bubble bubble-orange"><i class="fa-solid fa-shirt"></i></div>
                                <div class="info-text">
                                    <span>Product</span>
                                    <strong id="viewProductName">-</strong>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon-bubble bubble-purple"><i class="fa-solid fa-list-check"></i></div>
                                <div class="info-text">
                                    <span>Service</span>
                                    <strong id="viewJobName">-</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="viewDemoImageContainer" class="mb-3 d-none">
                    <div class="view-order-card">
                        <span class="view-card-title"><i class="fa-solid fa-image text-warning"></i> Garment Reference</span>
                        <div class="text-center p-2 bg-light rounded">
                            <img id="viewDemoImage" src="" alt="Demo Product" class="img-thumbnail shadow-sm" style="max-height: 220px;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var viewOrderModal = document.getElementById('viewOrderModal');
    if (viewOrderModal) {
        viewOrderModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            var data = JSON.parse(btn.getAttribute('data-order'));
            var displayStatus = btn.getAttribute('data-display-status') || 'Assigned';
            var statusClass = btn.getAttribute('data-status-class') || 'status-assigned';

            document.getElementById('viewOrderTitle').textContent = data.order_no || 'ORD-0000';
            document.getElementById('viewDeliveryDate').textContent = data.delivery_date ? data.delivery_date : 'Not Specified';

            var statusPill = document.getElementById('viewOrderStatusBadge');
            statusPill.textContent = displayStatus;
            statusPill.className = 'status-pill ' + statusClass;

            document.getElementById('viewCustomerName').textContent = data.customer_name || 'N/A';
            document.getElementById('viewCustomerPhone').textContent = data.customer_phone || '-';
            document.getElementById('viewCustomerAddress').textContent = data.customer_address || 'No Address Provided';

            document.getElementById('viewProductName').textContent = data.product_name || '-';
            document.getElementById('viewJobName').textContent = data.job_description_name || '-';

            var imgContainer = document.getElementById('viewDemoImageContainer');
            var imgTag = document.getElementById('viewDemoImage');
            if (data.demo_image) {
                imgTag.src = '/darjibhai/assets/images/orders/' + data.demo_image;
                imgContainer.classList.remove('d-none');
            } else {
                imgContainer.classList.add('d-none');
            }
        });
    }
});
</script>

<?php
$delQuery->close();
?>
<script src="/darjibhai/assets/js/form-validation.js" defer></script>
<?php
include __DIR__ . '/../../includes/footer.php';
?>
