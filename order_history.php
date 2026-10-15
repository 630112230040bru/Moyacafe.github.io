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

$user_id = (int)$_SESSION["cafe_user_id"];

$stmt = $conn->prepare(
    "SELECT id, total_amount, status, created_at
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC, id DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | Moya Cafe</title>
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
    <h1 class="page-title">My Orders</h1>
    <p class="subtitle">ตรวจสอบคำสั่งซื้อและสถานะของคุณ</p>

    <div class="panel">
        <?php if ($result->num_rows === 0): ?>
            <div class="empty">
                <h2>ยังไม่มีประวัติคำสั่งซื้อ</h2>
                <p>เมื่อคุณสั่งซื้อสินค้า รายการจะปรากฏที่หน้านี้</p>
                <a class="btn" href="products.php">เลือกซื้อสินค้า</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>เลขที่คำสั่งซื้อ</th>
                            <th>วันที่สั่งซื้อ</th>
                            <th>ยอดรวม</th>
                            <th>สถานะ</th>
                            <th>รายละเอียด</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= (int)$order["id"] ?></td>
                                <td>
                                    <?= h(date(
                                        "d/m/Y H:i",
                                        strtotime($order["created_at"])
                                    )) ?>
                                </td>
                                <td>
                                    ฿<?= number_format(
                                        (float)$order["total_amount"], 2
                                    ) ?>
                                </td>
                                <td>
                                    <span class="status">
                                        <?= h(status_text($order["status"])) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="order_detail.php?id=<?= (int)$order["id"] ?>">
                                        ดูรายละเอียด
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>

<?php $stmt->close(); ?>