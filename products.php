<?php
session_start();
require_once __DIR__ . "/server.php";

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_id = filter_input(
        INPUT_POST,
        "product_id",
        FILTER_VALIDATE_INT
    );

    $quantity = filter_input(
        INPUT_POST,
        "quantity",
        FILTER_VALIDATE_INT
    );

    if (!$product_id || !$quantity || $quantity < 1 || $quantity > 99) {
        $_SESSION["shop_message"] = "กรุณาเลือกจำนวนสินค้าให้ถูกต้อง";
        header("Location: products.php");
        exit;
    }

    $stmt = $conn->prepare(
        "SELECT id FROM products
         WHERE id = ? AND is_available = 1"
    );
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $current_quantity = $_SESSION["cart"][$product_id] ?? 0;
        $_SESSION["cart"][$product_id] = min(
            99,
            $current_quantity + $quantity
        );
        $_SESSION["shop_message"] = "เพิ่มสินค้าลงตะกร้าแล้ว";
    } else {
        $_SESSION["shop_message"] = "ไม่พบสินค้าที่เลือก";
    }

    $stmt->close();

    header("Location: products.php");
    exit;
}

$result = $conn->query(
    "SELECT id, name, description, price, image_url
     FROM products
     WHERE is_available = 1
     ORDER BY id ASC"
);

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$cart_count = array_sum($_SESSION["cart"]);
$message = $_SESSION["shop_message"] ?? "";
unset($_SESSION["shop_message"]);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu | Moya Cafe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <a class="brand" href="products.php">☕ Moya Cafe</a>
    <div class="nav-links">
        <a href="home.php">หน้าหลัก</a>
        <a href="products.php">เมนู</a>
        <a href="cart.php">ตะกร้า (<?= $cart_count ?>)</a>
        <a href="order_history.php">ประวัติคำสั่งซื้อ</a>
        <a href="index.php">ออกจากระบบ</a>
    </div>
</nav>

<main class="container">
    <h1 class="page-title">Our Menu</h1>
    <p class="subtitle">
        เลือกกาแฟแก้วโปรดและขนมที่คุณชื่นชอบจาก Moya Cafe
    </p>

    <?php if ($message !== ""): ?>
        <div class="message success"><?= h($message) ?></div>
    <?php endif; ?>

    <div class="product-grid">
        <?php while ($product = $result->fetch_assoc()): ?>
            <article class="product-card">
                <?php if (!empty($product["image_url"])): ?>
                    <img
                        src="<?= h($product["image_url"]) ?>"
                        alt="<?= h($product["name"]) ?>"
                        loading="lazy"
                    >
                <?php else: ?>
                    <img src="" alt="ไม่มีรูปภาพสินค้า">
                <?php endif; ?>

                <div class="product-info">
                    <h3><?= h($product["name"]) ?></h3>
                    <p class="description">
                        <?= h($product["description"]) ?>
                    </p>
                    <p class="price">
                        ฿<?= number_format((float)$product["price"], 2) ?>
                    </p>

                    <form method="POST" action="products.php">
                        <input
                            type="hidden"
                            name="product_id"
                            value="<?= (int)$product["id"] ?>"
                        >

                        <div class="quantity">
                            <label for="qty<?= (int)$product["id"] ?>">
                                จำนวน
                            </label>
                            <input
                                id="qty<?= (int)$product["id"] ?>"
                                type="number"
                                name="quantity"
                                min="1"
                                max="99"
                                value="1"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-block">
                            + เพิ่มลงตะกร้า
                        </button>
                    </form>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</main>

</body>
</html>