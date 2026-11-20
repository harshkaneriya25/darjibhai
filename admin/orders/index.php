<?php

require_once __DIR__ . '/../../includes/admin-auth.php';
require_once __DIR__ . '/../../config/database.php';

$page_title = 'Orders';
$admin_page = true;

$extra_js = '
<script src="/darjibhai/assets/js/admin/table.js"></script>
';

$successMessage = $_SESSION['order_success'] ?? '';
$errorMessage   = $_SESSION['order_error'] ?? '';

unset($_SESSION['order_success'], $_SESSION['order_error']);

/*
|--------------------------------------------------------------------------
| HANDLE ACTIONS (ADD, ASSIGN MASTER, RECEIVE, ASSIGN DELIVERY, DELETE)
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $action = $_POST['action'];

    /* 1. ADD ORDER (ADMIN MANUAL CREATION) */
    if ($action === 'add') {

        $userId           = (int) ($_POST['user_id'] ?? 0);
        $productId        = (int) ($_POST['product_id'] ?? 0);
        $jobDescriptionId = (int) ($_POST['job_description_id'] ?? 0);
        $deliveryDate     = trim($_POST['delivery_date'] ?? '');

        // Measurements Array
        $measurements = [
            'upper_body' => [
                'neck'          => filter_var($_POST['neck'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'shoulder'      => filter_var($_POST['shoulder'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'chest'         => filter_var($_POST['chest'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'sleeve_length' => filter_var($_POST['sleeve_length'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'armhole'       => filter_var($_POST['armhole'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'bicep'         => filter_var($_POST['bicep'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)
            ],
            'middle_body' => [
                'waist'         => filter_var($_POST['waist'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'stomach'       => filter_var($_POST['stomach'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'torso_length'  => filter_var($_POST['torso_length'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)
            ],
            'lower_body' => [
                'hip'           => filter_var($_POST['hip'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'thigh'         => filter_var($_POST['thigh'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'knee'          => filter_var($_POST['knee'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'inseam'        => filter_var($_POST['inseam'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'outseam_length'=> filter_var($_POST['outseam_length'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'bottom_hem'    => filter_var($_POST['bottom_hem'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)
            ]
        ];
        $measurementsJson = json_encode($measurements);

        // Handle Demo Image Upload
        $demoImagePath = null;
        if (isset($_FILES['demo_image']) && $_FILES['demo_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../assets/images/orders/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['demo_image']['name']);
            if (move_uploaded_file($_FILES['demo_image']['tmp_name'], $uploadDir . $fileName)) {
                $demoImagePath = $fileName;
            }
        }

        if ($userId <= 0) {
            $_SESSION['order_error'] = 'Select an existing customer from the dropdown to create the order.';
        } else {
            $customerCheck = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
            $customerCheck->bind_param('i', $userId);
            $customerCheck->execute();
            $customerExists = $customerCheck->get_result()->num_rows > 0;
            $customerCheck->close();
            if (!$customerExists) {
                $_SESSION['order_error'] = 'The selected customer does not exist. Choose an existing customer from the dropdown.';
            }
        }

        if (!empty($_SESSION['order_error'])) {
            // Validation error is shown on the orders page after redirect.
        } elseif ($productId <= 0) {
            $_SESSION['order_error'] = 'Please select a product.';
        } elseif ($jobDescriptionId <= 0) {
            $_SESSION['order_error'] = 'Please select a service (Job Description).';
        } elseif ($deliveryDate === '') {
            $_SESSION['order_error'] = 'Please enter a target delivery date.';
        } else {
            // Sequential order number (ORD-0001, ORD-0002, ...)
            $nextNum = 1;
            $seqQuery = $conn->query("
                SELECT order_no 
                FROM orders 
                WHERE order_no REGEXP '^ORD-[0-9]+$' 
                ORDER BY id DESC 
                LIMIT 1
            ");
            if ($seqQuery && $lastOrder = $seqQuery->fetch_assoc()) {
                $parts = explode('-', $lastOrder['order_no']);
                if (isset($parts[1]) && is_numeric($parts[1])) {
                    $nextNum = ((int) $parts[1]) + 1;
                }
            }
            $orderNo = 'ORD-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $stmt = $conn->prepare("
                INSERT INTO orders 
                (order_no, user_id, product_id, job_description_id, demo_image, delivery_date, body_measurements, order_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'created')
            ");
            $stmt->bind_param('siiisss', $orderNo, $userId, $productId, $jobDescriptionId, $demoImagePath, $deliveryDate, $measurementsJson);

            if ($stmt->execute()) {
                $newOrderId = $stmt->insert_id;

                $hist = $conn->prepare("INSERT INTO order_status_history (order_id, status, description) VALUES (?, 'created', 'Admin manually created the order.')");
                $hist->bind_param('i', $newOrderId);
                $historySaved = $hist->execute();
                $hist->close();

                if ($historySaved) {
                    $_SESSION['order_success'] = 'Order created successfully.';
                } else {
                    error_log('Admin order: status history insert failed: ' . $conn->error);
                    $_SESSION['order_error'] = 'The order was created, but its status history could not be saved.';
                }
            } else {
                error_log('Admin order: order creation failed: ' . $stmt->error);
                $_SESSION['order_error'] = 'Failed to create order.';
            }
            $stmt->close();
        }
    }

    /* 2. ASSIGN TO MASTER */
    elseif ($action === 'assign_master') {

        $orderId  = (int) ($_POST['order_id'] ?? 0);
        $masterId = (int) ($_POST['master_id'] ?? 0);

        if ($orderId > 0 && $masterId > 0) {
            $stmt = $conn->prepare("UPDATE orders SET master_id = ?, order_status = 'assigned_to_master' WHERE id = ?");
            $stmt->bind_param('ii', $masterId, $orderId);

            if ($stmt->execute()) {
                $hist = $conn->prepare("INSERT INTO order_status_history (order_id, status, description) VALUES (?, 'assigned_to_master', 'Admin assigned order to Master.')");
                $hist->bind_param('i', $orderId);
                $hist->execute();
                $hist->close();

                $_SESSION['order_success'] = 'Order assigned to Master.';
            } else {
                $_SESSION['order_error'] = 'Failed to assign Master.';
            }
            $stmt->close();
        }
    }

    /* 3. RECEIVE PRODUCT FROM MASTER */
    elseif ($action === 'receive_from_master') {

        $orderId = (int) ($_POST['order_id'] ?? 0);

        if ($orderId > 0) {
            $stmt = $conn->prepare("UPDATE orders SET order_status = 'received_from_master' WHERE id = ?");
            $stmt->bind_param('i', $orderId);

            if ($stmt->execute()) {
                $hist = $conn->prepare("INSERT INTO order_status_history (order_id, status, description) VALUES (?, 'received_from_master', 'Admin received complete product from Master.')");
                $hist->bind_param('i', $orderId);
                $hist->execute();
                $hist->close();

                $_SESSION['order_success'] = 'Product received sucessfully';
            } else {
                $_SESSION['order_error'] = 'Failed to update order status.';
            }
            $stmt->close();
        }
    }

    /* 4. ASSIGN TO DELIVERY PARTNER */
    elseif ($action === 'assign_delivery') {

        $orderId           = (int) ($_POST['order_id'] ?? 0);
        $deliveryPartnerId = (int) ($_POST['delivery_partner_id'] ?? 0);

        if ($orderId > 0 && $deliveryPartnerId > 0) {
            $stmt = $conn->prepare("UPDATE orders SET delivery_partner_id = ?, order_status = 'assigned_to_delivery' WHERE id = ?");
            $stmt->bind_param('ii', $deliveryPartnerId, $orderId);

            if ($stmt->execute()) {
                $hist = $conn->prepare("INSERT INTO order_status_history (order_id, status, description) VALUES (?, 'assigned_to_delivery', 'Admin assigned order to Delivery Partner.')");
                $hist->bind_param('i', $orderId);
                $hist->execute();
                $hist->close();

                $_SESSION['order_success'] = 'Order assigned to Delivery Partner.';
            } else {
                $_SESSION['order_error'] = 'Failed to assign Delivery Partner.';
            }
            $stmt->close();
        }
    }

    /* 5. DELETE ORDER */
    elseif ($action === 'delete') {

        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $delete = $conn->prepare("DELETE FROM orders WHERE id = ?");
            $delete->bind_param('i', $id);

            if ($delete->execute()) {
                $_SESSION['order_success'] = 'Order deleted successfully.';
            } else {
                $_SESSION['order_error'] = 'Failed to delete order.';
            }
            $delete->close();
        }
    }

    header('Location: /darjibhai/admin/orders/index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| FETCH DATA FOR DROPDOWNS & TABLE
|--------------------------------------------------------------------------
*/

$customerLookup = [];
$customerLookupResult = $conn->query("SELECT id, name, phone, email, address FROM users ORDER BY name ASC, id DESC");
if ($customerLookupResult) {
    while ($customerRow = $customerLookupResult->fetch_assoc()) {
        $customerLookup[] = $customerRow;
    }
}
$productsList = $conn->query("SELECT id, name, status FROM products ORDER BY name ASC");
$jobsList = $conn->query("SELECT id, name, status FROM job_descriptions ORDER BY name ASC");

// Masters list (role_id = 2)
$mastersResult = $conn->query("SELECT id, name FROM admins WHERE role_id = 2 AND status = 1 ORDER BY name ASC");
$mastersList = $mastersResult ? $mastersResult->fetch_all(MYSQLI_ASSOC) : [];

// Delivery partners list (role_id = 4 or name lookup)
$deliveryResult = $conn->query("
    SELECT id, name FROM admins 
    WHERE status = 1 AND (role_id = 4 OR role_id = (SELECT id FROM roles WHERE name LIKE '%Delivery%' LIMIT 1))
    ORDER BY name ASC
");
$deliveryList = $deliveryResult ? $deliveryResult->fetch_all(MYSQLI_ASSOC) : [];

// Orders query with customer, product, master & delivery info
$ordersResult = $conn->query("
    SELECT 
        o.id, o.order_no, o.user_id, o.product_id, o.job_description_id,
        o.demo_image, o.delivery_date, o.body_measurements, o.order_status,
        o.master_id, o.delivery_partner_id, o.created_at,
        u.name AS customer_name,
        u.phone AS customer_phone,
        u.email AS customer_email,
        u.address AS customer_address,
        p.name AS product_name,
        j.name AS job_description_name,
        m.name AS master_name,
        d.name AS delivery_partner_name
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    LEFT JOIN products p ON p.id = o.product_id
    LEFT JOIN job_descriptions j ON j.id = o.job_description_id
    LEFT JOIN admins m ON m.id = o.master_id
    LEFT JOIN admins d ON d.id = o.delivery_partner_id
    ORDER BY o.id DESC
");

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
.status-created { background: #f1f5f9; color: #475569; }
.status-assigned { background: #e0f2fe; color: #0284c7; }
.status-accepted { background: #fef9c3; color: #a16207; }
.status-completed { background: #dcfce7; color: #15803d; }
.status-declined { background: #fee2e2; color: #b91c1c; }

/* ---------------------------------------------------------
   BORDERLESS ACTION DROPDOWN BUTTON
--------------------------------------------------------- */
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
    z-index: 1060 !important; /* Ensures it renders above all elements */
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

/* =========================================================
   ENHANCED VIEW ORDER MODAL STYLING
========================================================= */
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
.bubble-gray { background: #f8fafc; color: #475569; }

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

/* Measurement View Styling */
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

/* Modal Header Bar */
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
            <h3>Orders</h3>
            <p><b>Dashboard</b> > Orders Management</p>
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
                        placeholder="Search orders..."
                        data-table-search="ordersTable"
                    >
                </div>

                <button
                    type="button"
                    class="btn btn-orange"
                    data-bs-toggle="modal"
                    data-bs-target="#addOrderModal"
                >
                    <i class="fa-solid fa-plus"></i>
                    Add Order
                </button>
            </div>

            <!-- ORDERS TABLE -->
            <div class="table-responsive">
                <table class="table admin-table" id="ordersTable">
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
                                $statusClass = 'status-created';
                                $displayStatus = $ord['order_status'] === 'delivered'
                                    ? 'COMPLETED'
                                    : ($ord['order_status'] === 'received_from_master'
                                        ? 'RECEIVED PRODUCT'
                                        : ($ord['order_status'] === 'master_completed' ? 'READY FOR DELIVERY' : strtoupper(str_replace('_', ' ', $ord['order_status']))));
                                if (strpos($ord['order_status'], 'assigned') !== false) {
                                    $statusClass = 'status-assigned';
                                } elseif (strpos($ord['order_status'], 'accepted') !== false) {
                                    $statusClass = 'status-accepted';
                                } elseif (strpos($ord['order_status'], 'completed') !== false || $ord['order_status'] === 'delivered') {
                                    $statusClass = 'status-completed';
                                } elseif (strpos($ord['order_status'], 'declined') !== false) {
                                    $statusClass = 'status-declined';
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
                                        <?= !empty($ord['delivery_date']) ? date('d M, Y', strtotime($ord['delivery_date'])) : '-' ?>
                                    </td>
                                    <td>
                                        <span class="status-pill <?= $statusClass ?>">
                                            <?= htmlspecialchars($displayStatus) ?>
                                        </span>
                                    </td>

                                    <!-- BORDERLESS DROPDOWN ACTION MENU WITH OVERLAP STRATEGY -->
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
                                                
                                                <!-- Workflow Action: Assign Master -->
                                                <?php if (in_array($ord['order_status'], ['created', 'master_declined'])): ?>
                                                    <li>
                                                        <a 
                                                            class="dropdown-item" 
                                                            href="javascript:void(0);"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#assignMasterModal"
                                                            data-order-id="<?= (int)$ord['id'] ?>"
                                                            data-order-no="<?= htmlspecialchars($ord['order_no']) ?>"
                                                            data-customer-name="<?= htmlspecialchars($ord['customer_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>"
                                                            data-customer-phone="<?= htmlspecialchars($ord['customer_phone'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"
                                                            data-customer-address="<?= htmlspecialchars($ord['customer_address'] ?? 'No address provided', ENT_QUOTES, 'UTF-8') ?>"
                                                            data-product-name="<?= htmlspecialchars($ord['product_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"
                                                            data-job-name="<?= htmlspecialchars($ord['job_description_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"
                                                            data-master-name="<?= htmlspecialchars($ord['master_name'] ?? 'Not assigned', ENT_QUOTES, 'UTF-8') ?>"
                                                        >
                                                            <i class="fa-solid fa-user-gear text-primary"></i>
                                                            <span>Assign Master</span>
                                                        </a>
                                                    </li>
                                                
                                                <!-- Workflow Action: Receive from Master -->
                                                <?php elseif ($ord['order_status'] === 'master_completed'): ?>
                                                    <li>
                                                        <form method="POST" class="m-0 p-0">
                                                            <input type="hidden" name="action" value="receive_from_master">
                                                            <input type="hidden" name="order_id" value="<?= (int)$ord['id'] ?>">
                                                            <button type="submit" class="dropdown-item text-success border-0 bg-transparent w-100">
                                                                <i class="fa-solid fa-box-archive"></i>
                                                                <span>Receive Product</span>
                                                            </button>
                                                        </form>
                                                    </li>

                                                <!-- Workflow Action: Assign Delivery Partner -->
                                                <?php elseif (in_array($ord['order_status'], ['received_from_master', 'delivery_declined'])): ?>
                                                    <li>
                                                        <a 
                                                            class="dropdown-item" 
                                                            href="javascript:void(0);"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#assignDeliveryModal"
                                                            data-order-id="<?= (int)$ord['id'] ?>"
                                                            data-order-no="<?= htmlspecialchars($ord['order_no']) ?>"
                                                            data-customer-name="<?= htmlspecialchars($ord['customer_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>"
                                                            data-customer-phone="<?= htmlspecialchars($ord['customer_phone'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"
                                                            data-customer-address="<?= htmlspecialchars($ord['customer_address'] ?? 'No address provided', ENT_QUOTES, 'UTF-8') ?>"
                                                            data-product-name="<?= htmlspecialchars($ord['product_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"
                                                            data-job-name="<?= htmlspecialchars($ord['job_description_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"
                                                            data-master-name="<?= htmlspecialchars($ord['master_name'] ?? 'Not assigned', ENT_QUOTES, 'UTF-8') ?>"
                                                        >
                                                            <i class="fa-solid fa-truck-fast text-warning"></i>
                                                            <span>Assign Delivery</span>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>

                                                <!-- View Order Details -->
                                                <li>
                                                    <a 
                                                        class="dropdown-item" 
                                                        href="javascript:void(0);"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewOrderModal"
                                                        data-order='<?= json_encode($ord, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                                    >
                                                        <i class="fa-solid fa-eye text-muted"></i>
                                                        <span>View Details</span>
                                                    </a>
                                                </li>

                                            

                                                <!-- Delete Order -->
                                                <li>
                                                    <a 
                                                        class="dropdown-item text-danger" 
                                                        href="javascript:void(0);"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteOrderModal"
                                                        data-id="<?= (int) $ord['id'] ?>"
                                                        data-orderno="<?= htmlspecialchars($ord['order_no']) ?>"
                                                    >
                                                        <i class="fa-solid fa-trash"></i>
                                                        <span>Delete Order</span>
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
                                        <i class="fa-solid fa-cart-shopping"></i>
                                        <p>No Orders Found</p>
                                        <span>There are currently no orders registered in the system.</span>
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
     1. ADD ORDER MODAL (ADMIN)
========================================================== -->
<style>
    #addOrderModal .order-form-section {
        padding: 20px;
        margin-bottom: 16px;
        border: 1px solid #e8edf3;
        border-radius: 20px;
        background: linear-gradient(145deg, #ffffff, #fbfcfe);
        box-shadow: 0 4px 16px rgba(15, 23, 42, .045);
    }
    #addOrderModal .order-form-section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 0 0 16px;
        color: #1e293b;
        font-weight: 750;
    }
</style>
<div class="modal fade" id="addOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="action" value="add">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create New Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <section class="order-form-section">
                        <h6 class="order-form-section-title"><i class="fa-solid fa-user text-primary"></i> Customer Details</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="admin-form-label" for="orderCustomerSelect">Existing Customer <span class="text-danger">*</span></label>
                                <select name="user_id" id="orderCustomerSelect" class="admin-form-control" required>
                                    <option value="">Select a customer...</option>
                                    <?php foreach ($customerLookup as $customer): ?>
                                        <option value="<?= (int) $customer['id'] ?>" data-phone="<?= htmlspecialchars($customer['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-email="<?= htmlspecialchars($customer['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-address="<?= htmlspecialchars($customer['address'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($customer['name'] ?? 'Unnamed customer') ?><?= !empty($customer['phone']) ? ' — ' . htmlspecialchars($customer['phone']) : '' ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="admin-form-label" for="orderCustomerPhone">Phone Number</label>
                                <input type="tel" id="orderCustomerPhone" class="admin-form-control" placeholder="Select a customer" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="admin-form-label" for="orderCustomerEmail">Email Address</label>
                                <input type="email" id="orderCustomerEmail" class="admin-form-control" placeholder="Select a customer" readonly>
                            </div>
                            <div class="col-12">
                                <label class="admin-form-label" for="orderCustomerAddress">Address</label>
                                <textarea id="orderCustomerAddress" class="admin-form-control" placeholder="Address" rows="2" readonly></textarea>
                            </div>
                        </div>
                    </section>

                    <!-- Measurement Sections -->
                    <section class="order-form-section">
                        <h6 class="order-form-section-title"><i class="fa-solid fa-ruler-combined text-warning"></i> Measurements <small class="text-muted fw-normal">(inches)</small></h6>

                        <!-- Upper Body -->
                        <div class="measurement-group-card">
                            <div class="measurement-section-title"><i class="fa-solid fa-shirt"></i> Upper Body</div>
                            <div class="row g-2">
                                <div class="col-md-4 col-sm-6">
                                    <label class="small text-muted">Neck</label>
                                    <input type="number" step="0.1" min="0" name="neck" class="admin-form-control" placeholder="15.5">
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="small text-muted">Shoulder Width</label>
                                    <input type="number" step="0.1" min="0" name="shoulder" class="admin-form-control" placeholder="18.0">
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="small text-muted">Chest / Bust</label>
                                    <input type="number" step="0.1" min="0" name="chest" class="admin-form-control" placeholder="38.5">
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="small text-muted">Sleeve Length</label>
                                    <input type="number" step="0.1" min="0" name="sleeve_length" class="admin-form-control" placeholder="24.0">
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="small text-muted">Arm Hole</label>
                                    <input type="number" step="0.1" min="0" name="armhole" class="admin-form-control" placeholder="17.0">
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="small text-muted">Bicep</label>
                                    <input type="number" step="0.1" min="0" name="bicep" class="admin-form-control" placeholder="13.0">
                                </div>
                            </div>
                        </div>

                        <!-- Middle Body -->
                        <div class="measurement-group-card">
                            <div class="measurement-section-title"><i class="fa-solid fa-arrows-split-up-and-left"></i> Middle Body</div>
                            <div class="row g-2">
                                <div class="col-md-4 col-sm-6">
                                    <label class="small text-muted">Waist</label>
                                    <input type="number" step="0.1" min="0" name="waist" class="admin-form-control" placeholder="32.0">
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="small text-muted">Stomach / Belly</label>
                                    <input type="number" step="0.1" min="0" name="stomach" class="admin-form-control" placeholder="34.0">
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="small text-muted">Torso Length</label>
                                    <input type="number" step="0.1" min="0" name="torso_length" class="admin-form-control" placeholder="39.5">
                                </div>
                            </div>
                        </div>

                        <!-- Lower Body -->
                        <div class="measurement-group-card">
                            <div class="measurement-section-title"><i class="fa-solid fa-person-walking"></i> Lower Body</div>
                            <div class="row g-2">
                                <div class="col-md-4 col-sm-6">
                                    <label class="small text-muted">Hip</label>
                                    <input type="number" step="0.1" min="0" name="hip" class="admin-form-control" placeholder="39.0">
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="small text-muted">Thigh</label>
                                    <input type="number" step="0.1" min="0" name="thigh" class="admin-form-control" placeholder="22.0">
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="small text-muted">Knee</label>
                                    <input type="number" step="0.1" min="0" name="knee" class="admin-form-control" placeholder="16.0">
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="small text-muted">Inseam</label>
                                    <input type="number" step="0.1" min="0" name="inseam" class="admin-form-control" placeholder="30.0">
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="small text-muted">Outseam Length</label>
                                    <input type="number" step="0.1" min="0" name="outseam_length" class="admin-form-control" placeholder="40.5">
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="small text-muted">Bottom Hem</label>
                                    <input type="number" step="0.1" min="0" name="bottom_hem" class="admin-form-control" placeholder="14.0">
                                </div>
                            </div>
                        </div>

                    </section>

                    <section class="order-form-section mb-0">
                        <h6 class="order-form-section-title"><i class="fa-solid fa-box text-warning"></i> Product and Delivery Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="admin-form-label">Product <span class="text-danger">*</span></label>
                                <select name="product_id" class="admin-form-control" required>
                                    <option value="">Select Product...</option>
                                    <?php if ($productsList): $productsList->data_seek(0); while ($p = $productsList->fetch_assoc()): ?>
                                        <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="admin-form-label">Job Description <span class="text-danger">*</span></label>
                                <select name="job_description_id" class="admin-form-control" required>
                                    <option value="">Select Service...</option>
                                    <?php if ($jobsList): $jobsList->data_seek(0); while ($j = $jobsList->fetch_assoc()): ?>
                                        <option value="<?= (int)$j['id'] ?>"><?= htmlspecialchars($j['name']) ?></option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="admin-form-label">Required Delivery Date <span class="text-danger">*</span></label>
                                <input type="date" name="delivery_date" class="admin-form-control" min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="admin-form-label">Upload Image</label>
                                <input type="file" name="demo_image" class="admin-form-control" accept="image/*">
                            </div>
                        </div>
                    </section>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-orange">Create Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =========================================================
     2. ASSIGN MASTER MODAL
========================================================== -->
<div class="modal fade" id="assignMasterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" autocomplete="off">
                <input type="hidden" name="action" value="assign_master">
                <input type="hidden" name="order_id" id="assignMasterOrderId" value="">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Assign Order to Master</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-light border d-flex justify-content-between align-items-center mb-4">
                        <span>Order No.</span>
                        <strong id="assignMasterOrderNo" class="text-primary"></strong>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <section class="view-order-card assign-master-section">
                                <span class="view-card-title"><i class="fa-solid fa-user text-primary"></i> Customer Details</span>
                                <div class="row g-3">
                                    <div class="col-md-4"><small class="d-block text-muted">Customer Name</small><strong id="assignMasterCustomerName">-</strong></div>
                                    <div class="col-md-4"><small class="d-block text-muted">Customer Number</small><strong id="assignMasterCustomerPhone">-</strong></div>
                                    <div class="col-md-4"><small class="d-block text-muted">Address</small><strong id="assignMasterCustomerAddress">-</strong></div>
                                </div>
                            </section>
                        </div>

                        <div class="col-12">
                            <section class="view-order-card assign-master-section">
                                <span class="view-card-title"><i class="fa-solid fa-box text-warning"></i> Product and Service</span>
                                <div class="row g-3">
                                    <div class="col-md-6"><small class="d-block text-muted">Product</small><strong id="assignMasterProduct">-</strong></div>
                                    <div class="col-md-6"><small class="d-block text-muted">Job Description / Service</small><strong id="assignMasterJob">-</strong></div>
                                </div>
                            </section>
                        </div>

                        <div class="col-12">
                            <section class="view-order-card assign-master-section">
                                <span class="view-card-title"><i class="fa-solid fa-user-gear text-success"></i> Select Master</span>
                                <div class="admin-form-group mb-0">
                                    <label class="admin-form-label" for="assignMasterSelect">Master Tailor</label>
                                    <select name="master_id" id="assignMasterSelect" class="admin-form-control" required>
                                        <option value="">Choose Master...</option>
                                        <?php foreach ($mastersList as $m): ?>
                                            <option value="<?= (int)$m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-orange">Confirm Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #assignMasterModal .assign-master-section,
    #assignDeliveryModal .assign-delivery-section {
        border-radius: 20px;
        border-color: #e8edf3;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.045);
    }
</style>

<!-- =========================================================
     3. ASSIGN DELIVERY PARTNER MODAL
========================================================== -->
<div class="modal fade" id="assignDeliveryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" autocomplete="off">
                <input type="hidden" name="action" value="assign_delivery">
                <input type="hidden" name="order_id" id="assignDeliveryOrderId" value="">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Ready For Delivery</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-light border d-flex justify-content-between align-items-center mb-4">
                        <span>Order No.</span>
                        <strong id="assignDeliveryOrderNo" class="text-primary"></strong>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <section class="view-order-card assign-delivery-section">
                                <span class="view-card-title"><i class="fa-solid fa-user text-primary"></i> Customer Details</span>
                                <div class="row g-3">
                                    <div class="col-md-4"><small class="d-block text-muted">Customer Name</small><strong id="assignDeliveryCustomerName">-</strong></div>
                                    <div class="col-md-4"><small class="d-block text-muted">Customer Number</small><strong id="assignDeliveryCustomerPhone">-</strong></div>
                                    <div class="col-md-4"><small class="d-block text-muted">Address</small><strong id="assignDeliveryCustomerAddress">-</strong></div>
                                </div>
                            </section>
                        </div>

                        <div class="col-12">
                            <section class="view-order-card assign-delivery-section">
                                <span class="view-card-title"><i class="fa-solid fa-box text-warning"></i> Product and Service</span>
                                <div class="row g-3">
                                    <div class="col-md-4"><small class="d-block text-muted">Product</small><strong id="assignDeliveryProduct">-</strong></div>
                                    <div class="col-md-4"><small class="d-block text-muted">Job Description</small><strong id="assignDeliveryJob">-</strong></div>
                                    <div class="col-md-4"><small class="d-block text-muted">Assigend Master</small><strong id="assignDeliveryMasterName">-</strong></div>
                                </div>
                            </section>
                        </div>

                        <div class="col-12">
                            <section class="view-order-card assign-delivery-section">
                                <span class="view-card-title"><i class="fa-solid fa-truck-fast text-success"></i> Select Delivery Partner</span>
                                <div class="admin-form-group mb-0">
                                    <label class="admin-form-label" for="assignDeliveryPartnerSelect">Delivery Partner</label>
                                    <select name="delivery_partner_id" id="assignDeliveryPartnerSelect" class="admin-form-control" required>
                                        <option value="">Choose Delivery Partner...</option>
                                        <?php foreach ($deliveryList as $d): ?>
                                            <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-orange">Confirm Delivery Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =========================================================
     4. ATTRACTIVE VIEW ORDER DETAILS MODAL
========================================================== -->
<div class="modal fade" id="viewOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Order Overview</h5>
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
                        <span id="viewOrderStatusBadge" class="status-pill status-created mb-1">CREATED</span>
                        <div class="small text-muted">
                            <i class="fa-regular fa-calendar me-1"></i> Target: <strong id="viewDeliveryDate" class="text-dark">-</strong>
                        </div>
                    </div>
                </div>

                <!-- Summary Grid -->
                <div class="row g-3 mb-3">
                    
                    <!-- Customer Details Card -->
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
                                <div class="info-icon-bubble bubble-purple"><i class="fa-solid fa-envelope"></i></div>
                                <div class="info-text">
                                    <span>Email Address</span>
                                    <strong id="viewCustomerEmail">-</strong>
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

                    <!-- Assignment Details Card -->
                    <div class="col-md-6">
                        <div class="view-order-card">
                            <span class="view-card-title">
                                <i class="fa-solid fa-diagram-project text-success"></i> Assignment Details
                            </span>
                            <div class="info-item">
                                <div class="info-icon-bubble bubble-orange"><i class="fa-solid fa-scissors"></i></div>
                                <div class="info-text">
                                    <span>Assigned Master</span>
                                    <strong id="viewMasterName" class="text-dark">Not Assigned</strong>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon-bubble bubble-blue"><i class="fa-solid fa-truck"></i></div>
                                <div class="info-text">
                                    <span>Assigned Delivery Partner</span>
                                    <strong id="viewDeliveryName" class="text-dark">Not Assigned</strong>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon-bubble bubble-gray"><i class="fa-solid fa-shirt"></i></div>
                                <div class="info-text">
                                    <span>Product & Service</span>
                                    <strong id="viewProductAndJob">-</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Demo Image Preview -->
                <div id="viewDemoImageContainer" class="mb-3 d-none">
                    <div class="view-order-card">
                        <span class="view-card-title">
                            <i class="fa-solid fa-image text-warning"></i> Demo Product Reference Image
                        </span>
                        <div class="text-center p-2 bg-light rounded">
                            <img id="viewDemoImage" src="" alt="Demo Design" class="img-thumbnail shadow-sm" style="max-height: 220px;">
                        </div>
                    </div>
                </div>

                <!-- Categorized Measurements -->
                <div class="view-order-card">
                    <span class="view-card-title">
                        <i class="fa-solid fa-ruler-combined text-warning"></i> Customer Body Measurements (Inches)
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

<!-- =========================================================
     5. DELETE ORDER MODAL
========================================================== -->
<div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteOrderId" value="">

                <div class="modal-header">
                    <h5 class="modal-title">Delete Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="delete-modal-icon">
                        <i class="fa-solid fa-trash"></i>
                    </div>

                    <div class="delete-modal-content">
                        <h5>Delete Order</h5>
                        <p>
                            Are you sure you want to delete order <strong id="deleteOrderNo"></strong>?
                            This action cannot be undone.
                        </p>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =========================================================
     JAVASCRIPT MODAL EVENT BINDINGS
========================================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Selecting an existing customer fills their saved contact details.
    var customerSelect = document.getElementById('orderCustomerSelect');
    var customerPhoneInput = document.getElementById('orderCustomerPhone');
    var customerEmailInput = document.getElementById('orderCustomerEmail');
    var customerAddressInput = document.getElementById('orderCustomerAddress');
    if (customerSelect) {
        customerSelect.addEventListener('change', function () {
            var selectedCustomer = customerSelect.selectedOptions[0];
            customerPhoneInput.value = selectedCustomer.dataset.phone || '';
            customerEmailInput.value = selectedCustomer.dataset.email || '';
            customerAddressInput.value = selectedCustomer.dataset.address || '';
        });
    }

    // Assign Master Modal Binding
    var assignMasterModal = document.getElementById('assignMasterModal');
    if (assignMasterModal) {
        assignMasterModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            document.getElementById('assignMasterOrderId').value = btn.getAttribute('data-order-id');
            document.getElementById('assignMasterOrderNo').textContent = btn.getAttribute('data-order-no');
            document.getElementById('assignMasterCustomerName').textContent = btn.getAttribute('data-customer-name') || 'N/A';
            document.getElementById('assignMasterCustomerPhone').textContent = btn.getAttribute('data-customer-phone') || '-';
            document.getElementById('assignMasterCustomerAddress').textContent = btn.getAttribute('data-customer-address') || 'No address provided';
            document.getElementById('assignMasterProduct').textContent = btn.getAttribute('data-product-name') || '-';
            document.getElementById('assignMasterJob').textContent = btn.getAttribute('data-job-name') || '-';
        });
    }

    // Assign Delivery Modal Binding
    var assignDeliveryModal = document.getElementById('assignDeliveryModal');
    if (assignDeliveryModal) {
        assignDeliveryModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            document.getElementById('assignDeliveryOrderId').value = btn.getAttribute('data-order-id');
            document.getElementById('assignDeliveryOrderNo').textContent = btn.getAttribute('data-order-no');
            document.getElementById('assignDeliveryCustomerName').textContent = btn.getAttribute('data-customer-name') || 'N/A';
            document.getElementById('assignDeliveryCustomerPhone').textContent = btn.getAttribute('data-customer-phone') || '-';
            document.getElementById('assignDeliveryCustomerAddress').textContent = btn.getAttribute('data-customer-address') || 'No address provided';
            document.getElementById('assignDeliveryProduct').textContent = btn.getAttribute('data-product-name') || '-';
            document.getElementById('assignDeliveryJob').textContent = btn.getAttribute('data-job-name') || '-';
            document.getElementById('assignDeliveryMasterName').textContent = btn.getAttribute('data-master-name') || 'Not assigned';
        });
    }

    // Delete Order Modal Binding
    var deleteOrderModal = document.getElementById('deleteOrderModal');
    if (deleteOrderModal) {
        deleteOrderModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            document.getElementById('deleteOrderId').value = btn.getAttribute('data-id');
            document.getElementById('deleteOrderNo').textContent = btn.getAttribute('data-orderno');
        });
    }

    // Enhanced View Order Details Modal Binding
    var viewOrderModal = document.getElementById('viewOrderModal');
    if (viewOrderModal) {
        viewOrderModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            var data = JSON.parse(btn.getAttribute('data-order'));

            // Header info
            document.getElementById('viewOrderTitle').textContent = data.order_no || 'ORD-0000';
            document.getElementById('viewDeliveryDate').textContent = data.delivery_date ? data.delivery_date : 'Not Specified';

            // Status Badge
            var statusPill = document.getElementById('viewOrderStatusBadge');
            var rawStatus = data.order_status || 'created';
            statusPill.textContent = rawStatus === 'delivered'
                ? 'COMPLETED'
                : (rawStatus === 'received_from_master'
                    ? 'RECEIVED PRODUCT'
                    : (rawStatus === 'master_completed' ? 'READY FOR DELIVERY' : rawStatus.toUpperCase().replace(/_/g, ' ')));
            statusPill.className = 'status-pill';
            if (rawStatus.indexOf('assigned') !== -1) {
                statusPill.classList.add('status-assigned');
            } else if (rawStatus.indexOf('accepted') !== -1) {
                statusPill.classList.add('status-accepted');
            } else if (rawStatus.indexOf('completed') !== -1 || rawStatus === 'delivered') {
                statusPill.classList.add('status-completed');
            } else if (rawStatus.indexOf('declined') !== -1) {
                statusPill.classList.add('status-declined');
            } else {
                statusPill.classList.add('status-created');
            }

            // Customer details
            document.getElementById('viewCustomerName').textContent = data.customer_name || 'N/A';
            document.getElementById('viewCustomerPhone').textContent = data.customer_phone || '-';
            document.getElementById('viewCustomerEmail').textContent = data.customer_email || '-';
            document.getElementById('viewCustomerAddress').textContent = data.customer_address || 'No Address Provided';

            // Assignment & Product Details
            document.getElementById('viewMasterName').textContent = data.master_name ? data.master_name : 'Not Assigned';
            document.getElementById('viewDeliveryName').textContent = data.delivery_partner_name ? data.delivery_partner_name : 'Not Assigned';
            document.getElementById('viewProductAndJob').textContent = (data.product_name || '-') + ' (' + (data.job_description_name || '-') + ')';

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

?>
<script src="/darjibhai/assets/js/form-validation.js" defer></script>
<?php include __DIR__ . '/../../includes/footer.php';

?>
