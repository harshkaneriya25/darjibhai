<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "darjibhai";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

/** Save a snapshot of a delivered order in completed_orders. */
function archiveCompletedOrder(mysqli $conn, int $orderId): bool
{
    $check = $conn->prepare("SELECT id FROM completed_orders WHERE order_id = ? LIMIT 1");
    if (!$check) {
        return false;
    }
    $check->bind_param('i', $orderId);
    if (!$check->execute()) {
        $check->close();
        return false;
    }
    if ($check->get_result()->num_rows > 0) {
        $check->close();
        return true;
    }
    $check->close();

    $stmt = $conn->prepare("SELECT o.id AS order_id, o.order_no, o.user_id, o.master_id,
            o.delivery_partner_id, o.demo_image, o.body_measurements,
            o.delivery_date, o.created_at, u.name AS customer_name, u.phone AS customer_phone,
            u.email AS customer_email, u.address AS customer_address, p.name AS product_name,
            j.name AS job_description_name
        FROM orders o
        LEFT JOIN users u ON u.id = o.user_id
        LEFT JOIN products p ON p.id = o.product_id
        LEFT JOIN job_descriptions j ON j.id = o.job_description_id
        WHERE o.id = ? AND o.order_status = 'delivered' LIMIT 1");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('i', $orderId);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$order) {
        return false;
    }

    $archive = $conn->prepare("INSERT INTO completed_orders
        (order_id, order_no, user_id, master_id, delivery_partner_id, product_name,
         job_description_name, customer_name, customer_phone, customer_email, customer_address,
         demo_image, body_measurements, delivery_date, created_at, completed_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    if (!$archive) {
        return false;
    }
    $archive->bind_param('isiiissssssssss',
        $order['order_id'], $order['order_no'], $order['user_id'], $order['master_id'],
        $order['delivery_partner_id'], $order['product_name'], $order['job_description_name'],
        $order['customer_name'], $order['customer_phone'], $order['customer_email'],
        $order['customer_address'], $order['demo_image'], $order['body_measurements'],
        $order['delivery_date'], $order['created_at']);
    $success = $archive->execute();
    $archive->close();
    return $success;
}
