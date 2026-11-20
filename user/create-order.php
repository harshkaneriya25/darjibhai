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

$successMessage = '';
$errorMessage   = '';

// Handle Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId        = (int) ($_POST['product_id'] ?? 0);
    $jobDescriptionId = (int) ($_POST['job_description_id'] ?? 0);
    $deliveryDate     = trim($_POST['delivery_date'] ?? '');

    // Collect full body measurements
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

    // Handle optional Demo Image
    $demoImagePath = null;
    if (isset($_FILES['demo_image']) && $_FILES['demo_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/orders/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['demo_image']['name']);
        if (move_uploaded_file($_FILES['demo_image']['tmp_name'], $uploadDir . $fileName)) {
            $demoImagePath = $fileName;
        }
    }

    if ($productId <= 0 || $jobDescriptionId <= 0 || empty($deliveryDate)) {
        $errorMessage = 'Please select a garment, service, and delivery date.';
    } else {
        // Generate sequential order number (ORD-0001)[cite: 1]
        $nextNum = 1;
        $seqQuery = $conn->query("SELECT order_no FROM orders WHERE order_no REGEXP '^ORD-[0-9]+$' ORDER BY id DESC LIMIT 1");
        if ($seqQuery && $lastOrder = $seqQuery->fetch_assoc()) {
            $parts = explode('-', $lastOrder['order_no']);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $nextNum = ((int) $parts[1]) + 1;
            }
        }
        $orderNo = 'ORD-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        // Bind user_id strictly to logged-in user
        $stmt = $conn->prepare("
            INSERT INTO orders 
            (order_no, user_id, product_id, job_description_id, demo_image, delivery_date, body_measurements, order_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'created')
        ");
        $stmt->bind_param('siiisss', $orderNo, $user_id, $productId, $jobDescriptionId, $demoImagePath, $deliveryDate, $measurementsJson);

        $conn->begin_transaction();
        try {
            if (!$stmt->execute()) {
                throw new RuntimeException('Could not save order.');
            }
            $newOrderId = (int) $stmt->insert_id;

            // Keep the selected product, service, quantity and measurements
            // together in the order_items table for item-level order details.
            $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, job_description_id, quantity, body_measurements) VALUES (?, ?, ?, 1, ?)");
            if (!$itemStmt) {
                throw new RuntimeException('Could not prepare order item.');
            }
            $itemStmt->bind_param('iiis', $newOrderId, $productId, $jobDescriptionId, $measurementsJson);
            if (!$itemStmt->execute()) {
                $itemStmt->close();
                throw new RuntimeException('Could not save order item.');
            }
            $itemStmt->close();

            // Audit log in status history.
            $hist = $conn->prepare("INSERT INTO order_status_history (order_id, status, description) VALUES (?, 'created', 'Order placed by customer.')");
            if (!$hist) {
                throw new RuntimeException('Could not prepare order history.');
            }
            $hist->bind_param('i', $newOrderId);
            if (!$hist->execute()) {
                $hist->close();
                throw new RuntimeException('Could not save order history.');
            }
            $hist->close();
            $conn->commit();

            $_SESSION['order_placed_success'] = "Order #{$orderNo} placed successfully!";
            header("Location: /darjibhai/user/orders.php");
            exit;
        } catch (Throwable $exception) {
            $conn->rollback();
            $errorMessage = 'Failed to submit order. Please try again.';
        }
        $stmt->close();
    }
}

// Load active catalog entries directly from the admin-managed tables.
$products = $conn->query("SELECT id, name FROM products WHERE status = 1 ORDER BY name ASC");
$jobs     = $conn->query("SELECT id, name FROM job_descriptions ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Tailoring Order - Darjibhai</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --order-orange: #f47721;
            --order-ink: #20212a;
            --order-muted: #747987;
            --order-line: #e9eaf0;
        }

        body {
            min-height: 100vh;
            color: var(--order-ink);
            background: radial-gradient(circle at top left, #fff0e4 0, transparent 34%), #f5f6fa !important;
            font-family: Inter, "Segoe UI", sans-serif;
        }

        .order-page { max-width: 980px !important; }
        .order-card {
            padding: clamp(1.25rem, 4vw, 2.5rem) !important;
            border: 1px solid rgba(32, 33, 42, .06) !important;
            border-radius: 24px !important;
            box-shadow: 0 24px 70px rgba(36, 39, 50, .09) !important;
        }
        .order-header { border-color: var(--order-line) !important; }
        .order-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: .45rem;
            color: var(--order-orange);
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .order-title { font-size: clamp(1.45rem, 3vw, 1.9rem); letter-spacing: -.04em; }
        .order-subtitle { margin: .4rem 0 0; color: var(--order-muted); font-size: .93rem; }
        .order-back {
            border-radius: 11px;
            padding: .6rem .9rem;
            font-weight: 650;
        }
        .form-label { color: #444754; font-size: .85rem; }
        .form-control, .form-select {
            min-height: 46px;
            border-color: #e3e5eb;
            border-radius: 11px;
            background-color: #fff;
        }
        .form-control:focus, .form-select:focus {
            border-color: #f6a56e;
            box-shadow: 0 0 0 .22rem rgba(244, 119, 33, .12);
        }
        .catalog-select { position: relative; }
        .catalog-select-trigger {
            display: flex; width: 100%; min-height: 46px; align-items: center;
            justify-content: space-between; gap: .75rem; padding: .65rem .85rem;
            border: 1px solid #e3e5eb; border-radius: 11px; color: #444754;
            background: #fff; text-align: left;
        }
        .catalog-select-trigger:focus { outline: 0; border-color: #f6a56e; box-shadow: 0 0 0 .22rem rgba(244, 119, 33, .12); }
        .catalog-select-menu {
            position: absolute; z-index: 20; top: calc(100% + 5px); right: 0; left: 0;
            max-height: 230px; overflow-y: auto; padding: .35rem;
            border: 1px solid var(--order-line); border-radius: 12px;
            background: #fff; box-shadow: 0 14px 30px rgba(32, 33, 42, .14);
        }
        .catalog-select-option {
            width: 100%; padding: .65rem .75rem; border: 0; border-radius: 8px;
            color: var(--order-ink); background: transparent; text-align: left;
        }
        .catalog-select-option:hover, .catalog-select-option:focus { outline: 0; color: #a64408; background: #fff0e5; }
        .measurement-intro { color: var(--order-muted); font-size: .9rem; }
        .measurement-section {
            margin-top: 1.25rem;
            padding: 1.25rem;
            border: 1px solid var(--order-line);
            border-radius: 17px;
            background: linear-gradient(135deg, #fff 0%, #fdfdff 100%);
        }
        .measurement-heading {
            display: flex;
            align-items: center;
            gap: .7rem;
            margin: 0 0 1rem;
            font-size: 1rem;
            font-weight: 750;
        }
        .measurement-icon {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border-radius: 11px;
            color: var(--order-orange);
            background: #fff0e5;
        }
        .measurement-section .row { --bs-gutter-y: 1rem; }
        .measurement-section .form-label { margin-bottom: .4rem; font-weight: 600; }
        .submit-order {
            min-height: 52px;
            border: 0;
            border-radius: 13px;
            background: linear-gradient(100deg, #f47721, #ff9b45);
            box-shadow: 0 10px 22px rgba(244, 119, 33, .22);
            font-size: .98rem;
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .submit-order:hover { background: #ed6d18; transform: translateY(-1px); box-shadow: 0 13px 25px rgba(244, 119, 33, .28); }
        @media (max-width: 575.98px) {
            .measurement-section { padding: 1rem; }
            .order-header { align-items: flex-start !important; gap: 1rem; }
            .order-back { white-space: nowrap; }
        }
    </style>
</head>
<body class="bg-light">

<div class="container order-page my-5">
    <div class="card order-card">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-4 order-header">
            <div>
                <span class="order-eyebrow"><i class="fa-solid fa-scissors"></i> Made for you</span>
                <h1 class="fw-bold mb-0 order-title">Place Your Order</h1>
                <p class="order-subtitle">Choose your garment, add measurements, and tell us when you need it.</p>
            </div>
            <a href="/darjibhai/user/orders.php" class="btn btn-outline-secondary btn-sm order-back"><i class="fa-solid fa-box me-1"></i> My Orders</a>
        </div>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="fa-solid fa-shirt me-1 text-warning"></i> Product</label>
                    <div class="catalog-select" data-catalog-select>
                        <input type="hidden" name="product_id" required>
                        <button type="button" class="catalog-select-trigger" aria-expanded="false">
                            <span>Select Product...</span><i class="fa-solid fa-chevron-down"></i>
                        </button>
                        <div class="catalog-select-menu" role="listbox" hidden>
                        <?php while ($p = $products->fetch_assoc()): ?>
                            <button type="button" class="catalog-select-option" role="option" data-value="<?= (int) $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></button>
                        <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="fa-solid fa-wand-magic-sparkles me-1 text-warning"></i> Service</label>
                    <div class="catalog-select" data-catalog-select>
                        <input type="hidden" name="job_description_id" required>
                        <button type="button" class="catalog-select-trigger" aria-expanded="false">
                            <span>Select service...</span><i class="fa-solid fa-chevron-down"></i>
                        </button>
                        <div class="catalog-select-menu" role="listbox" hidden>
                        <?php while ($j = $jobs->fetch_assoc()): ?>
                            <button type="button" class="catalog-select-option" role="option" data-value="<?= (int) $j['id'] ?>"><?= htmlspecialchars($j['name']) ?></button>
                        <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="fa-regular fa-calendar me-1 text-warning"></i> Target Delivery Date *</label>
                    <input type="date" name="delivery_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="fa-regular fa-image me-1 text-warning"></i> Design Reference (Optional)</label>
                    <input type="file" name="demo_image" class="form-control" accept="image/*">
                </div>
            </div>

            <h2 class="h5 fw-bold mt-4 mb-2"><i class="fa-solid fa-ruler-combined text-warning me-2"></i>Body Measurements <span class="text-muted fs-6 fw-normal">(inches)</span></h2>
            <p class="measurement-intro">Enter the measurements needed for your garment. Leave others blank.</p>

            <section class="measurement-section">
            <h3 class="measurement-heading"><span class="measurement-icon"><i class="fa-solid fa-person"></i></span>Upper Body</h3>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Neck</label>
                    <input type="number" min="0" step="0.1" name="neck" class="form-control" placeholder="e.g. 15">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Shoulder</label>
                    <input type="number" min="0" step="0.1" name="shoulder" class="form-control" placeholder="e.g. 18">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Chest / Bust</label>
                    <input type="number" min="0" step="0.1" name="chest" class="form-control" placeholder="e.g. 38">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Sleeve Length</label>
                    <input type="number" min="0" step="0.1" name="sleeve_length" class="form-control" placeholder="e.g. 24">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Armhole</label>
                    <input type="number" min="0" step="0.1" name="armhole" class="form-control" placeholder="e.g. 17">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Bicep</label>
                    <input type="number" min="0" step="0.1" name="bicep" class="form-control" placeholder="e.g. 13">
                </div>
            </div></section>

            <section class="measurement-section">
            <h3 class="measurement-heading"><span class="measurement-icon"><i class="fa-solid fa-person-circle-check"></i></span>Middle Body</h3>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Waist</label>
                    <input type="number" min="0" step="0.1" name="waist" class="form-control" placeholder="e.g. 32">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Stomach</label>
                    <input type="number" min="0" step="0.1" name="stomach" class="form-control" placeholder="e.g. 34">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Torso Length</label>
                    <input type="number" min="0" step="0.1" name="torso_length" class="form-control" placeholder="e.g. 26">
                </div>
            </div></section>

            <section class="measurement-section">
            <h3 class="measurement-heading"><span class="measurement-icon"><i class="fa-solid fa-person-walking"></i></span>Lower Body</h3>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Hip</label>
                    <input type="number" min="0" step="0.1" name="hip" class="form-control" placeholder="e.g. 40">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Thigh</label>
                    <input type="number" min="0" step="0.1" name="thigh" class="form-control" placeholder="e.g. 23">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Knee</label>
                    <input type="number" min="0" step="0.1" name="knee" class="form-control" placeholder="e.g. 16">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Inseam</label>
                    <input type="number" min="0" step="0.1" name="inseam" class="form-control" placeholder="e.g. 30">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Outseam / Garment Length</label>
                    <input type="number" min="0" step="0.1" name="outseam_length" class="form-control" placeholder="e.g. 40">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Bottom Hem</label>
                    <input type="number" min="0" step="0.1" name="bottom_hem" class="form-control" placeholder="e.g. 14">
                </div>
            </div></section>

            <button type="submit" class="btn btn-warning text-white fw-bold w-100 py-2 mt-4 submit-order"><i class="fa-solid fa-check me-2"></i>Confirm & Place Order</button>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('[data-catalog-select]').forEach(function (select) {
    const trigger = select.querySelector('.catalog-select-trigger');
    const valueInput = select.querySelector('input[type="hidden"]');
    const menu = select.querySelector('.catalog-select-menu');

    trigger.addEventListener('click', function () {
        const shouldOpen = menu.hidden;
        document.querySelectorAll('.catalog-select-menu').forEach(function (otherMenu) {
            otherMenu.hidden = true;
            otherMenu.previousElementSibling.setAttribute('aria-expanded', 'false');
        });
        menu.hidden = !shouldOpen;
        trigger.setAttribute('aria-expanded', String(shouldOpen));
    });

    select.querySelectorAll('.catalog-select-option').forEach(function (option) {
        option.addEventListener('click', function () {
            valueInput.value = option.dataset.value;
            trigger.querySelector('span').textContent = option.textContent.trim();
            menu.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
        });
    });
});

document.querySelector('form').addEventListener('submit', function (event) {
    for (const select of document.querySelectorAll('[data-catalog-select]')) {
        const valueInput = select.querySelector('input[type="hidden"]');
        const trigger = select.querySelector('.catalog-select-trigger');
        if (!valueInput.value) {
            alert('Please choose a ' + (valueInput.name === 'product_id' ? 'product' : 'service') + '.');
            trigger.focus();
            select.querySelector('.catalog-select-menu').hidden = false;
            trigger.setAttribute('aria-expanded', 'true');
            event.preventDefault();
            return;
        }
    }
});

document.addEventListener('click', function (event) {
    if (!event.target.closest('[data-catalog-select]')) {
        document.querySelectorAll('.catalog-select-menu').forEach(function (menu) {
            menu.hidden = true;
            menu.previousElementSibling.setAttribute('aria-expanded', 'false');
        });
    }
});
</script>

<script src="/darjibhai/assets/js/form-validation.js" defer></script>
</body>
</html>
