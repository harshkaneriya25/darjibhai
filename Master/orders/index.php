<?php

require_once __DIR__ . '/../../includes/master-auth.php';
require_once __DIR__ . '/../../config/database.php';

$page_title = 'Assigned Tasks';
$admin_page = true;

$extra_js = '
<script src="/darjibhai/assets/js/admin/table.js"></script>
';

$master_id = (int) $_SESSION['admin_id'];

$successMessage = $_SESSION['master_order_success'] ?? '';
$errorMessage   = $_SESSION['master_order_error'] ?? '';

unset($_SESSION['master_order_success'], $_SESSION['master_order_error']);

/*
|--------------------------------------------------------------------------
| HANDLE MASTER TASK ACTIONS (ACCEPT, DECLINE, COMPLETE)
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $action  = $_POST['action'];
    $orderId = (int) ($_POST['order_id'] ?? 0);

    if ($orderId <= 0) {
        $_SESSION['master_order_error'] = 'Invalid order selected.';
    } else {

        /* 1. MASTER ACCEPTS ORDER */
        if ($action === 'accept') {

            $stmt = $conn->prepare("
                UPDATE orders 
                SET order_status = 'master_accepted' 
                WHERE id = ? AND master_id = ? AND order_status = 'assigned_to_master'
            ");
            $stmt->bind_param('ii', $orderId, $master_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $hist = $conn->prepare("INSERT INTO order_status_history (order_id, status, description) VALUES (?, 'master_accepted', 'Master tailor accepted the task.')");
                $hist->bind_param('i', $orderId);
                $hist->execute();
                $hist->close();

                $_SESSION['master_order_success'] = 'Order accepted.';
            } else {
                $_SESSION['master_order_error'] = 'Failed to accept.';
            }
            $stmt->close();
        }

        /* 2. MASTER DECLINES ORDER */
        elseif ($action === 'decline') {

            $stmt = $conn->prepare("
                UPDATE orders 
                SET order_status = 'master_declined', master_id = NULL 
                WHERE id = ? AND master_id = ? AND order_status = 'assigned_to_master'
            ");
            $stmt->bind_param('ii', $orderId, $master_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $hist = $conn->prepare("INSERT INTO order_status_history (order_id, status, description) VALUES (?, 'master_declined', 'Master tailor declined the assigned task.')");
                $hist->bind_param('i', $orderId);
                $hist->execute();
                $hist->close();

                $_SESSION['master_order_success'] = 'Order declined and returned to Admin queue.';
            } else {
                $_SESSION['master_order_error'] = 'Failed to decline order.';
            }
            $stmt->close();
        }

        /* 3. MASTER COMPLETES PRODUCT */
        elseif ($action === 'complete') {

            $stmt = $conn->prepare("
                UPDATE orders 
                SET order_status = 'master_completed' 
                WHERE id = ? AND master_id = ? AND order_status = 'master_accepted'
            ");
            $stmt->bind_param('ii', $orderId, $master_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $hist = $conn->prepare("INSERT INTO order_status_history (order_id, status, description) VALUES (?, 'master_completed', 'Master tailor marked the product as completed.')");
                $hist->bind_param('i', $orderId);
                $hist->execute();
                $hist->close();

                $_SESSION['master_order_success'] = 'Product Completed.';
            } else {
                $_SESSION['master_order_error'] = 'Something Went Wrong!.';
            }
            $stmt->close();
        }
    }

    header('Location: /darjibhai/Master/orders/index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| FETCH ORDERS ASSIGNED TO THIS MASTER (EXCLUDING NULL MASTER_ID)
|--------------------------------------------------------------------------
*/

$tasksQuery = $conn->prepare("
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
    WHERE o.master_id = ?
    ORDER BY o.id DESC
");
$tasksQuery->bind_param('i', $master_id);
$tasksQuery->execute();
$ordersResult = $tasksQuery->get_result();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<style>
/* ---------------------------------------------------------
   OVERLAP FIX: PREVENT TABLE & CARD FROM CLIPPING DROPDOWNS
--------------------------------------------------------- */
.admin-table-card {
    overflow: visible !important;
}

.table-responsive {
    overflow: visible !important;
}

.admin-table {
    overflow: visible !important;
}

/* Status Badges */
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

/* Borderless Action Dropdown Button */
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

.action-dropdown-btn:hover,
.action-dropdown-btn:focus,
.action-dropdown-btn:active,
.show > .action-dropdown-btn {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    background-color: #f1f5f9 !important;
    color: #0f172a !important;
}

.action-dropdown-btn::after {
    display: none !important;
}

/* Dropdown Menu Floating & Overlapping */
.action-dropdown-menu {
    border-radius: 12px;
    border: 1px solid #edf2f7;
    box-shadow: 0 14px 35px -5px rgba(15, 23, 42, 0.15), 0 8px 12px -6px rgba(15, 23, 42, 0.08);
    padding: 6px;
    min-width: 180px;
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
    transition: all 0.15s ease;
}

.action-dropdown-menu .dropdown-item:hover {
    background-color: #f8fafc;
    color: #0f172a;
}

.action-dropdown-menu .dropdown-item.text-danger:hover {
    background-color: #fef2f2;
    color: #dc2626;
}

/* Modal Styling */
.view-order-card {
    background: #ffffff;
    border: 1px solid #edf2f7;
    border-radius: 14px;
    padding: 18px 20px;
    height: 100%;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
}

.view-card-title {
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: 0.6px;
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
.info-item:last-child {
    margin-bottom: 0;
}

.info-icon-bubble {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
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
    letter-spacing: 0.4px;
}
.info-text strong {
    font-size: 0.94rem;
    color: #1e293b;
    font-weight: 600;
}

.view-measurement-group {
    background: #f8fafc;
    border: 1px solid #edf2f7;
    border-radius: 12px;
    padding: 14px 16px;
    height: 100%;
}
.view-measurement-header {
    font-size: 0.82rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #ff7700;
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid #fed7aa;
    display: flex;
    align-items: center;
    gap: 6px;
}
.measure-chip {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    border-bottom: 1px dashed #e2e8f0;
    font-size: 0.86rem;
}
.measure-chip:last-child {
    border-bottom: none;
}
.measure-chip span {
    color: #475569;
    text-transform: capitalize;
}
.measure-chip strong {
    color: #0f172a;
    font-weight: 700;
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
.order-header-bar h5 {
    margin: 0;
    font-weight: 800;
    color: #9a3412;
}
</style>

<div class="main-wrapper">

    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="admin-content">

        <div class="page-title-section">
            <h3>Master Orders</h3>
            <p><b>Master Portal</b> > Assigned Tasks</p>
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
                        placeholder="Search tasks..."
                        data-table-search="masterOrdersTable"
                    >
                </div>
            </div>

            <!-- TABLE -->
            <div class="table-responsive">
                <table class="table admin-table" id="masterOrdersTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Sr No.</th>
                            <th>Order No</th>
                            <th>Customer</th>
                            <th>Phone Number</th>
                            <th>Status</th>
                            <th style="width: 70px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php if ($ordersResult && $ordersResult->num_rows > 0): ?>
                            <?php 
                            $srNo = 1; 
                            while ($ord = $ordersResult->fetch_assoc()): 
                                
                                // Map internal status to clean status label and color badge
                                $statusLabel = 'Assigned';
                                $statusClass = 'status-assigned';

                                switch ($ord['order_status']) {
                                    case 'master_accepted':
                                        $statusLabel = 'Accepted';
                                        $statusClass = 'status-accepted';
                                        break;

                                    case 'master_declined':
                                        $statusLabel = 'Declined';
                                        $statusClass = 'status-declined';
                                        break;

                                    case 'master_completed':
                                        $statusLabel = 'Ready for Delivery';
                                        $statusClass = 'status-completed';
                                        break;

                                    case 'assigned_to_delivery':
                                    case 'delivery_accepted':
                                    case 'delivered':
                                    case 'completed':
                                        $statusLabel = 'Completed';
                                        $statusClass = 'status-completed';
                                        break;

                                    case 'received_from_master':
                                        $statusLabel = 'Received Product';
                                        $statusClass = 'status-accepted';
                                        break;

                                    case 'assigned_to_master':
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
                                    </td>
                                    <td><?= htmlspecialchars($ord['customer_phone'] ?? '-') ?></td>
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
                                                title="Actions"
                                            >
                                                <i class="fa-solid fa-ellipsis-vertical fs-5"></i>
                                            </button>
                                            
                                            <ul class="dropdown-menu dropdown-menu-end action-dropdown-menu shadow">
                                                
                                                <!-- Action: Accept Task -->
                                                <?php if ($ord['order_status'] === 'assigned_to_master'): ?>
                                                    <li>
                                                        <form method="POST" class="m-0 p-0">
                                                            <input type="hidden" name="action" value="accept">
                                                            <input type="hidden" name="order_id" value="<?= (int)$ord['id'] ?>">
                                                            <button type="submit" class="dropdown-item text-primary border-0 bg-transparent w-100">
                                                                <i class="fa-solid fa-check"></i>
                                                                <span>Accept Task</span>
                                                            </button>
                                                        </form>
                                                    </li>

                                                    <!-- Action: Decline Task -->
                                                    <li>
                                                        <form method="POST" class="m-0 p-0">
                                                            <input type="hidden" name="action" value="decline">
                                                            <input type="hidden" name="order_id" value="<?= (int)$ord['id'] ?>">
                                                            <button type="submit" class="dropdown-item text-danger border-0 bg-transparent w-100">
                                                                <i class="fa-solid fa-xmark"></i>
                                                                <span>Decline Task</span>
                                                            </button>
                                                        </form>
                                                    </li>
                                                
                                                <!-- Action: Mark Complete -->
                                                <?php elseif ($ord['order_status'] === 'master_accepted'): ?>
                                                    <li>
                                                        <form method="POST" class="m-0 p-0">
                                                            <input type="hidden" name="action" value="complete">
                                                            <input type="hidden" name="order_id" value="<?= (int)$ord['id'] ?>">
                                                            <button type="submit" class="dropdown-item text-success border-0 bg-transparent w-100">
                                                                <i class="fa-solid fa-circle-check"></i>
                                                                <span>Product Complete</span>
                                                            </button>
                                                        </form>
                                                    </li>
                                                <?php endif; ?>

                                                <!-- View Order Measurements & Details -->
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
                                                        <i class="fa-solid fa-eye text-muted"></i>
                                                        <span>View Details</span>
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
                                        <i class="fa-solid fa-scissors"></i>
                                        <p>No Assigned Orders</p>
                                        <span>You currently have no tasks assigned to you.</span>
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

<!-- =========================================================
     VIEW TASK & MEASUREMENTS MODAL
========================================================== -->
<div class="modal fade" id="viewOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Order & Measurements</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body pt-3">

                <!-- Header Banner -->
                <div class="order-header-bar">
                    <div>
                        <span class="text-muted small d-block">Order Identification</span>
                        <h5 id="viewOrderTitle" class="fw-bold mb-0">ORD-0000</h5>
                    </div>
                    <div class="text-end">
                        <span id="viewOrderStatusBadge" class="status-pill status-assigned mb-1">Assigned</span>
                        <div class="small text-muted">
                            <i class="fa-regular fa-calendar me-1"></i> Target: <strong id="viewDeliveryDate" class="text-dark">-</strong>
                        </div>
                    </div>
                </div>

                <!-- Info Cards -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="view-order-card">
                            <span class="view-card-title">
                                <i class="fa-solid fa-user text-primary"></i> Customer Details
                            </span>
                            <div class="info-item">
                                <div class="info-icon-bubble bubble-blue"><i class="fa-solid fa-id-card"></i></div>
                                <div class="info-text">
                                    <span>Full Name</span>
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
                                    <strong id="viewCustomerAddress">-</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="view-order-card">
                            <span class="view-card-title">
                                <i class="fa-solid fa-shirt text-warning"></i> Garment Specifications
                            </span>
                            <div class="info-item">
                                <div class="info-icon-bubble bubble-orange"><i class="fa-solid fa-box-open"></i></div>
                                <div class="info-text">
                                    <span>Product</span>
                                    <strong id="viewProductName">-</strong>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon-bubble bubble-purple"><i class="fa-solid fa-list-check"></i></div>
                                <div class="info-text">
                                    <span>Service (Job)</span>
                                    <strong id="viewJobName">-</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Demo Image Preview -->
                <div id="viewDemoImageContainer" class="mb-3 d-none">
                    <div class="view-order-card">
                        <span class="view-card-title">
                            <i class="fa-solid fa-image text-warning"></i> Demo Reference Image
                        </span>
                        <div class="text-center p-2 bg-light rounded">
                            <img id="viewDemoImage" src="" alt="Demo Design" class="img-thumbnail shadow-sm" style="max-height: 220px;">
                        </div>
                    </div>
                </div>

                <!-- Categorized Measurements -->
                <div class="view-order-card">
                    <span class="view-card-title">
                        <i class="fa-solid fa-ruler-combined text-warning"></i> Full Body Measurements (Inches)
                    </span>
                    <div id="viewMeasurementsContent" class="row g-3"></div>
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

            // Status Badge
            var statusPill = document.getElementById('viewOrderStatusBadge');
            statusPill.textContent = displayStatus;
            statusPill.className = 'status-pill ' + statusClass;

            // Customer & Product details
            document.getElementById('viewCustomerName').textContent = data.customer_name || 'N/A';
            document.getElementById('viewCustomerPhone').textContent = data.customer_phone || '-';
            document.getElementById('viewCustomerAddress').textContent = data.customer_address || 'No Address Provided';
            document.getElementById('viewProductName').textContent = data.product_name || '-';
            document.getElementById('viewJobName').textContent = data.job_description_name || '-';

            // Demo Image Preview
            var imgContainer = document.getElementById('viewDemoImageContainer');
            var imgTag = document.getElementById('viewDemoImage');
            if (data.demo_image) {
                imgTag.src = '/darjibhai/assets/images/orders/' + data.demo_image;
                imgContainer.classList.remove('d-none');
            } else {
                imgContainer.classList.add('d-none');
            }

            // Body Measurements Render
            var measureContainer = document.getElementById('viewMeasurementsContent');
            measureContainer.innerHTML = '';

            try {
                var m = JSON.parse(data.body_measurements || '{}');
                var sections = [
                    { key: 'upper_body', title: 'Upper Body', icon: 'fa-shirt' },
                    { key: 'middle_body', title: 'Middle Body', icon: 'fa-arrows-split-up-and-left' },
                    { key: 'lower_body', title: 'Lower Body', icon: 'fa-person-walking' }
                ];
                var hasSections = false;

                sections.forEach(function (sec) {
                    if (m[sec.key] && Object.keys(m[sec.key]).length > 0) {
                        hasSections = true;
                        var col = document.createElement('div');
                        col.className = 'col-md-4';
                        
                        var html = '<div class="view-measurement-group">';
                        html += '<div class="view-measurement-header"><i class="fa-solid ' + sec.icon + '"></i> ' + sec.title + '</div>';
                        
                        var count = 0;
                        for (var part in m[sec.key]) {
                            var val = m[sec.key][part];
                            if (val !== '' && val !== null) {
                                count++;
                                html += '<div class="measure-chip"><span>' + part.replace(/_/g, ' ') + '</span><strong>' + val + ' in</strong></div>';
                            }
                        }

                        if (count === 0) {
                            html += '<span class="text-muted small">None recorded</span>';
                        }

                        html += '</div>';
                        col.innerHTML = html;
                        measureContainer.appendChild(col);
                    }
                });

                if (!hasSections) {
                    measureContainer.innerHTML = '<div class="col-12"><p class="text-muted small mb-0">No measurements specified for this order.</p></div>';
                }
            } catch (e) {
                measureContainer.innerHTML = '<div class="col-12"><p class="text-muted small mb-0">Invalid or empty measurements format.</p></div>';
            }
        });
    }

});
</script>

<?php

$tasksQuery->close();
?>
<script src="/darjibhai/assets/js/form-validation.js" defer></script>
<?php
include __DIR__ . '/../../includes/footer.php';

?>
