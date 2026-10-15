<?php
session_start();
require_once __DIR__ . "/server.php";

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// ต้องเข้าสู่ระบบก่อนยืนยันคำสั่งซื้อ
if (empty($_SESSION["cafe_user_id"])) {
    header("Location: index.php");
    exit;
}

if (empty($_SESSION["cart"]) || !is_array($_SESSION["cart"])) {
    header("Location: cart.php");
    exit;
}

$error = "";
$customer_name = $_SESSION["cafe_user_name"] ?? "";
$phone = "";
$address = "";
$note = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customer_name = trim($_POST["customer_name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $note = trim($_POST["note"] ?? "");

    if ($customer_name === "" || $phone === "") {
        $error = "กรุณากรอกชื่อผู้สั่งซื้อและเบอร์โทรศัพท์";
    } elseif (strlen($customer_name) > 150 || strlen($phone) > 30) {
        $error = "ชื่อหรือเบอร์โทรศัพท์ยาวเกินกำหนด";
    } elseif (strlen($address) > 5000 || strlen($note) > 2000) {
        $error = "ที่อยู่หรือหมายเหตุยาวเกินกำหนด";
    } else {
        $conn->begin_transaction();

        try {
            $user_id = (int)$_SESSION["cafe_user_id"];
            $cart = $_SESSION["cart"];
            $order_products = [];
            $total = 0.0;

            // อ่านราคาและสถานะสินค้าจากฐานข้อมูลจริง
            $product_stmt = $conn->prepare(
                "SELECT id, name, price
                 FROM products
                 WHERE id = ? AND is_available = 1"
            );

            foreach ($cart as $id => $quantity) {
                $id = filter_var($id, FILTER_VALIDATE_INT);
                $quantity = filter_var(
                    $quantity,
                    FILTER_VALIDATE_INT
                );

                if (
                    !$id ||
                    $quantity === false ||
                    $quantity < 1 ||
                    $quantity > 99
                ) {
                    throw new RuntimeException(
                        "ข้อมูลสินค้าในตะกร้าไม่ถูกต้อง"
                    );
                }

                $product_stmt->bind_param("i", $id);
                $product_stmt->execute();
                $result = $product_stmt->get_result();
                $product = $result->fetch_assoc();

                if (!$product) {
                    throw new RuntimeException(
                        "มีสินค้าบางรายการไม่พร้อมจำหน่าย กรุณากลับไปตรวจสอบตะกร้า"
                    );
                }

                $unit_price = (float)$product["price"];
                $subtotal = round($unit_price * $quantity, 2);

                $product["quantity"] = $quantity;
                $product["unit_price"] = $unit_price;
                $product["subtotal"] = $subtotal;

                $order_products[] = $product;
                $total += $subtotal;
            }

            $product_stmt->close();

            if (empty($order_products)) {
                throw new RuntimeException("ไม่พบสินค้าในคำสั่งซื้อ");
            }

            $total = round($total, 2);

            // บันทึกข้อมูลหลักของคำสั่งซื้อ
            $stmt = $conn->prepare(
                "INSERT INTO orders
                    (user_id, customer_name, phone, address,
                     note, total_amount, status)
                 VALUES (?, ?, ?, ?, ?, ?, 'pending')"
            );

            $stmt->bind_param(
                "issssd",
                $user_id,
                $customer_name,
                $phone,
                $address,
                $note,
                $total
            );

            $stmt->execute();
            $order_id = $conn->insert_id;
            $stmt->close();

            // บันทึกรายการสินค้าของคำสั่งซื้อ
            $item_stmt = $conn->prepare(
                "INSERT INTO order_items
                    (order_id, product_id, product_name,
                     unit_price, quantity, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            foreach ($order_products as $product) {
                $product_id = (int)$product["id"];
                $product_name = $product["name"];
                $unit_price = $product["unit_price"];
                $quantity = $product["quantity"];
                $subtotal = $product["subtotal"];

                $item_stmt->bind_param(
                    "iisdid",
                    $order_id,
                    $product_id,
                    $product_name,
                    $unit_price,
                    $quantity,
                    $subtotal
                );

                $item_stmt->execute();
            }

            $item_stmt->close();

            // บันทึกทุกอย่างสำเร็จแล้ว
            $conn->commit();

            $_SESSION["cart"] = [];

            header("Location: order_detail.php?id=" . $order_id);
            exit;

        } catch (Throwable $e) {
            $conn->rollback();
            error_log($e->getMessage());

            $error = $e instanceof RuntimeException
                ? $e->getMessage()
                : "ไม่สามารถบันทึกคำสั่งซื้อได้ กรุณาลองใหม่อีกครั้ง";
        }
    }
}

// แสดงรายการสินค้าและยอดรวมจากฐานข้อมูล
$products = [];
$total = 0.0;
$stmt = $conn->prepare(
    "SELECT id, name, price
     FROM products
     WHERE id = ? AND is_available = 1"
);

foreach ($_SESSION["cart"] as $id => $quantity) {
    $id = (int)$id;
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        continue;
    }

    $quantity = max(1, min(99, (int)$quantity));
    $product["quantity"] = $quantity;
    $product["subtotal"] = (float)$product["price"] * $quantity;

    $products[] = $product;
    $total += $product["subtotal"];
}

$stmt->close();

if (empty($products)) {
    header("Location: cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Moya Cafe</title>
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
    <h1 class="page-title">Checkout</h1>
    <p class="subtitle">กรอกข้อมูลเพื่อยืนยันคำสั่งซื้อของคุณ</p>

    <?php if ($error !== ""): ?>
        <div class="message error"><?= h($error) ?></div>
    <?php endif; ?>

    <div class="panel">
        <h2>สรุปรายการสินค้า</h2>

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
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= h($product["name"]) ?></td>
                            <td>฿<?= number_format($product["price"], 2) ?></td>
                            <td><?= (int)$product["quantity"] ?></td>
                            <td>฿<?= number_format($product["subtotal"], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p class="total">ยอดรวม ฿<?= number_format($total, 2) ?></p>
    </div>

    <div class="panel">
        <h2>ข้อมูลผู้สั่งซื้อ</h2>

        <form method="POST" action="checkout.php">
            <div class="form-group">
                <label for="customer_name">ชื่อผู้สั่งซื้อ *</label>
                <input
                    id="customer_name"
                    name="customer_name"
                    maxlength="150"
                    value="<?= h($customer_name) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="phone">เบอร์โทรศัพท์ *</label>
                <input
                    id="phone"
                    name="phone"
                    type="tel"
                    maxlength="30"
                    value="<?= h($phone) ?>"
                    placeholder="กรอกเบอร์โทรศัพท์"
                    required
                >
            </div>

            <div class="form-group">
                <label for="address">ที่อยู่หรือสถานที่รับสินค้า</label>
                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    maxlength="5000"
                    placeholder="ระบุที่อยู่ หรือสถานที่นัดรับสินค้า"
                ><?= h($address) ?></textarea>
            </div>

            <div class="form-group">
                <label for="note">หมายเหตุเพิ่มเติม</label>
                <textarea
                    id="note"
                    name="note"
                    rows="3"
                    maxlength="2000"
                    placeholder="เช่น ไม่หวาน แยกน้ำแข็ง"
                ><?= h($note) ?></textarea>
            </div>

            <button type="submit" class="btn btn-block">
                ยืนยันคำสั่งซื้อ
            </button>
            <p style="margin-top:15px">
                <a href="cart.php">← กลับไปแก้ไขตะกร้าสินค้า</a>
            </p>
        </form>
    </div>
</main>

</body>
</html>