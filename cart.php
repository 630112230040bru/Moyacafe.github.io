<?php
session_start();
require_once __DIR__ . "/server.php";

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $product_id = filter_input(
        INPUT_POST,
        "product_id",
        FILTER_VALIDATE_INT
    );

    if ($action === "remove" && $product_id) {
        unset($_SESSION["cart"][$product_id]);
        $_SESSION["shop_message"] = "นำสินค้าออกจากตะกร้าแล้ว";

    } elseif ($action === "update") {
        $quantities = $_POST["quantities"] ?? [];

        if (is_array($quantities)) {
            foreach ($quantities as $id => $quantity) {
                $id = filter_var($id, FILTER_VALIDATE_INT);
                $quantity = filter_var(
                    $quantity,
                    FILTER_VALIDATE_INT
                );

                if (!$id || !isset($_SESSION["cart"][$id])) {
                    continue;
                }

                if ($quantity === false || $quantity < 1) {
                    unset($_SESSION["cart"][$id]);
                } else {
                    $_SESSION["cart"][$id] = min(99, $quantity);
                }
            }
        }

        $_SESSION["shop_message"] = "อัปเดตตะกร้าสินค้าแล้ว";
    }

    header("Location: cart.php");
    exit;
}

$cart = $_SESSION["cart"];
$products = [];
$total = 0.0;

if (!empty($cart)) {
    $stmt = $conn->prepare(
        "SELECT id, name, price, image_url
         FROM products
         WHERE id = ? AND is_available = 1"
    );

    foreach ($cart as $id => $quantity) {
        $id = (int)$id;
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        if (!$product) {
            unset($_SESSION["cart"][$id]);
            continue;
        }

        $quantity = max(1, min(99, (int)$quantity));
        $subtotal = (float)$product["price"] * $quantity;

        $product["quantity"] = $quantity;
        $product["subtotal"] = $subtotal;

        $products[] = $product;
        $total += $subtotal;
    }

    $stmt->close();
}

$message = $_SESSION["shop_message"] ?? "";
unset($_SESSION["shop_message"]);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Moya Cafe</title>
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
    <h1 class="page-title">Shopping Cart</h1>
    <p class="subtitle">ตรวจสอบรายการสินค้าก่อนสั่งซื้อ</p>

    <?php if ($message !== ""): ?>
        <div class="message success"><?= h($message) ?></div>
    <?php endif; ?>

    <?php if (empty($products)): ?>
        <div class="empty">
            <h2>ตะกร้าสินค้ายังว่างอยู่</h2>
            <p>เลือกกาแฟและขนมที่คุณชื่นชอบได้เลย</p>
            <a class="btn" href="products.php">เลือกซื้อสินค้า</a>
        </div>
    <?php else: ?>
        <div class="panel">
            <form method="POST" action="cart.php">
                <input type="hidden" name="action" value="update">

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>สินค้า</th>
                                <th>ราคา</th>
                                <th>จำนวน</th>
                                <th>รวม</th>
                                <th>ลบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= h($product["name"]) ?></td>
                                    <td>
                                        ฿<?= number_format(
                                            (float)$product["price"], 2
                                        ) ?>
                                    </td>
                                    <td style="min-width:100px">
                                        <input
                                            type="number"
                                            name="quantities[<?= (int)$product["id"] ?>]"
                                            value="<?= (int)$product["quantity"] ?>"
                                            min="0"
                                            max="99"
                                            aria-label="จำนวน <?= h($product["name"]) ?>"
                                        >
                                        <small>ใส่ 0 เพื่อลบสินค้า</small>
                                    </td>
                                    <td>
                                        ฿<?= number_format(
                                            $product["subtotal"], 2
                                        ) ?>
                                    </td>
                                    <td>
                                        <button
                                            type="submit"
                                            form="remove<?= (int)$product["id"] ?>"
                                            class="btn btn-danger"
                                        >
                                            ลบ
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <p class="total">
                    ยอดรวม ฿<?= number_format($total, 2) ?>
                </p>

                <button type="submit" class="btn btn-light">
                    อัปเดตตะกร้า
                </button>
                <a class="btn" href="checkout.php">ดำเนินการสั่งซื้อ</a>
                <a class="btn btn-light" href="products.php">เลือกซื้อเพิ่ม</a>
            </form>

            <?php foreach ($products as $product): ?>
                <form
                    id="remove<?= (int)$product["id"] ?>"
                    method="POST"
                    action="cart.php"
                    hidden
                >
                    <input type="hidden" name="action" value="remove">
                    <input
                        type="hidden"
                        name="product_id"
                        value="<?= (int)$product["id"] ?>"
                    >
                </form>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

</body>
</html>