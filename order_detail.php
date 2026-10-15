<?php
session_start();
require_once __DIR__ . "/server.php";

if (empty($_SESSION["cafe_user_id"])) {
    header("Location: index.php");
    exit;
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function status_text($status) {
    $statuses = [
        "pending" => "รอดำเนินการ",
        "processing" => "กำลังเตรียมสินค้า",
        "completed" => "เสร็จสิ้น",
        "cancelled" => "ยกเลิก"
    ];

    return $statuses[$status] ?? "ไม่ทราบสถานะ";
}

$order_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$order_id || $order_id < 1) {
    http_response_code(400);
    exit("เลขที่คำสั่งซื้อไม่ถูกต้อง");
}

$user_id = (int)$_SESSION["cafe_user_id"];

$stmt = $conn->prepare(
    "SELECT id, customer_name, phone, address, note,
            total_amount, status, created_at
     FROM orders
     WHERE id = ? AND user_id = ?
     LIMIT 1"
);

$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    http_response_code(404);
    exit("ไม่พบคำสั่งซื้อ หรือคุณไม่มีสิทธิ์เข้าถึงข้อมูลนี้");
}

$item_stmt = $conn->prepare(
    "SELECT product_name, unit_price, quantity, subtotal
     FROM order_items
     WHERE order_id = ?
     ORDER BY id ASC"
);

$item_stmt->bind_param("i", $order_id);
$item_stmt->execute();
$items = $item_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details | Moya Cafe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <a class="brand" href="products.php">☕ Moya Cafe</a>
    <div class="nav-links">
        <a href="products.php">เมนู</a>
        <a href="cart.php">ตะกร้า</a>
        <a href="order_history.php">ประวัติคำสั่งซื้อ</a>
    </div>
</nav>

<main class="container">
    <h1 class="page-title">Order Details</h1>
    <p class="subtitle">
        เลขที่คำสั่งซื้อ #<?= (int)$order["id"] ?>
    </p>

    <div class="panel">
        <h2>ข้อมูลคำสั่งซื้อ</h2>

        <p>
            <strong>วันที่สั่งซื้อ:</strong>
            <?= h(date(
                "d/m/Y H:i",
                strtotime($order["created_at"])
            )) ?>
        </p>

        <p>
            <strong>สถานะ:</strong>
            <span class="status">
                <?= h(status_text($order["status"])) ?>
            </span>
        </p>

        <p><strong>ชื่อผู้สั่งซื้อ:</strong>
            <?= h($order["customer_name"]) ?></p>

        <p><strong>เบอร์โทรศัพท์:</strong>
            <?= h($order["phone"]) ?></p>

        <p><strong>ที่อยู่/สถานที่รับสินค้า:</strong>
            <?= nl2br(h($order["address"] ?: "-")) ?></p>

        <p><strong>หมายเหตุ:</strong>
            <?= nl2br(h($order["note"] ?: "-")) ?></p>
    </div>

    <div class="panel">
        <h2>รายการสินค้า</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>เมนู</th>
                        <th>ราคา/ชิ้น</th>
                        <th>จำนวน</th>
                        <th>รวม</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $items->fetch_assoc()): ?>
                        <tr>
                            <td><?= h($item["product_name"]) ?></td>
                            <td>฿<?= number_format(
                                (float)$item["unit_price"], 2
                            ) ?></td>
                            <td><?= (int)$item["quantity"] ?></td>
                            <td>฿<?= number_format(
                                (float)$item["subtotal"], 2
                            ) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <p class="total">
            ยอดรวม ฿<?= number_format(
                (float)$order["total_amount"], 2
            ) ?>
        </p>

        <a class="btn" href="order_history.php">
            กลับไปประวัติคำสั่งซื้อ
        </a>

        <a class="btn btn-light" href="products.php">
            กลับไปเลือกเมนู
        </a>
    </div>
</main>

</body>
</html>

<?php $item_stmt->close(); ?>