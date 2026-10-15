
<?php
session_start();
require_once __DIR__ . "/server.php";

// ตรวจสอบสถานะการเข้าสู่ระบบ
if (empty($_SESSION["cafe_user_id"])) {
    header("Location: index.php");
    exit;
}

// สร้างตะกร้าสินค้าหากยังไม่มี
if (!isset($_SESSION["cart"]) || !is_array($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

// รายการเมนูที่แสดงบนหน้า Home
$products = [
    [
        "id" => 1,
        "name" => "Espresso",
        "description" => "กาแฟเข้มข้น หอมละมุน",
        "price" => 50,
        "icon" => "☕",
        "category" => "HOT COFFEE"
    ],
    [
        "id" => 2,
        "name" => "Cafe Latte",
        "description" => "กาแฟนมรสนุ่มละมุน",
        "price" => 65,
        "icon" => "🥛",
        "category" => "MILK COFFEE"
    ],
    [
        "id" => 3,
        "name" => "Caramel Macchiato",
        "description" => "หอมหวานด้วยคาราเมล",
        "price" => 75,
        "icon" => "🍮",
        "category" => "SIGNATURE"
    ],
    [
        "id" => 4,
        "name" => "Iced Americano",
        "description" => "สดชื่นด้วยกาแฟดำเย็น",
        "price" => 60,
        "icon" => "🧋",
        "category" => "ICED COFFEE"
    ]
];

// เพิ่มสินค้าลงตะกร้าจริงใน Session
if ($_SERVER["REQUEST_METHOD"] === "POST"
    && ($_POST["action"] ?? "") === "add_to_cart") {

    $product_id = filter_input(
        INPUT_POST,
        "product_id",
        FILTER_VALIDATE_INT
    );

    $found_product = false;

    foreach ($products as $product) {
        if ($product["id"] === $product_id) {
            $found_product = true;
            break;
        }
    }

    if ($found_product) {
        // ตรวจสอบว่าสินค้ามีอยู่ในฐานข้อมูลจริง
        $stmt = $conn->prepare(
            "SELECT id FROM products
             WHERE id = ? AND is_available = 1
             LIMIT 1"
        );

        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $current_quantity =
                $_SESSION["cart"][$product_id] ?? 0;

            $_SESSION["cart"][$product_id] = min(
                99,
                $current_quantity + 1
            );

            $_SESSION["shop_message"] =
                "เพิ่มสินค้าในตะกร้าเรียบร้อยแล้ว";
        } else {
            $_SESSION["shop_message"] =
                "ไม่พบเมนูนี้ในฐานข้อมูล กรุณาตรวจสอบรายการสินค้า";
        }

        $stmt->close();
    }

    header("Location: home.php#menu");
    exit;
}

// จำนวนสินค้าทั้งหมดในตะกร้า
$cartCount = array_sum($_SESSION["cart"]);

// ข้อความแจ้งเตือน
$message = $_SESSION["shop_message"] ?? "";
unset($_SESSION["shop_message"]);
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Moya Cafe | Home</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: "Segoe UI", Tahoma, sans-serif;
    background: #faf6f0;
    color: #493326;
}

a {
    text-decoration: none;
    color: inherit;
}

.navbar {
    height: 76px;
    padding: 0 7%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fffdf9;
    border-bottom: 1px solid #eee3d6;
}

.logo {
    font-family: Georgia, serif;
    font-size: 27px;
    font-weight: bold;
    color: #593b2b;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 28px;
    font-size: 14px;
}

.nav-links a:hover {
    color: #a47752;
}

.logout {
    padding: 10px 17px;
    background: #593b2b;
    color: white;
    border-radius: 8px;
}

.hero {
    margin: 32px auto;
    width: 86%;
    min-height: 390px;
    border-radius: 22px;
    overflow: hidden;
    display: flex;
    align-items: center;
    background:
        linear-gradient(90deg,
        rgba(44, 29, 20, 0.88),
        rgba(44, 29, 20, 0.15)),
        url("https://images.unsplash.com/photo-1442512595331-e89e73853f31?auto=format&fit=crop&w=1600&q=85")
        center 55% / cover no-repeat;
    color: white;
}

.hero-content {
    max-width: 520px;
    padding: 48px;
}

.eyebrow {
    font-size: 12px;
    letter-spacing: 3px;
    color: #e7c5a2;
    margin-bottom: 17px;
}

.hero h1 {
    font-family: Georgia, serif;
    font-size: clamp(36px, 5vw, 58px);
    line-height: 1.15;
    margin-bottom: 18px;
}

.hero p {
    font-size: 15px;
    line-height: 1.9;
    color: #f1e6dc;
    margin-bottom: 25px;
}

.primary-btn {
    display: inline-block;
    background: #e8c6a4;
    color: #493326;
    padding: 13px 24px;
    border-radius: 9px;
    font-size: 14px;
    font-weight: bold;
}

.primary-btn:hover {
    background: #f4dcc2;
}

.container {
    width: 86%;
    max-width: 1250px;
    margin: 55px auto;
}

.section-heading {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 16px;
    margin-bottom: 25px;
}

.section-heading h2 {
    font-family: Georgia, serif;
    font-size: 32px;
    margin-bottom: 8px;
}

.section-heading p {
    color: #958172;
    font-size: 14px;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 22px;
}

.product-card {
    background: #fffdf9;
    border: 1px solid #eee3d6;
    border-radius: 17px;
    overflow: hidden;
    transition: 0.25s;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(83, 54, 35, 0.10);
}

.product-picture {
    min-height: 175px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 75px;
    background: #f0e4d5;
}

.product-info {
    padding: 20px;
}

.category {
    font-size: 10px;
    letter-spacing: 1.5px;
    color: #a17b5c;
    margin-bottom: 10px;
}

.product-info h3 {
    font-family: Georgia, serif;
    font-size: 21px;
    margin-bottom: 9px;
}

.description {
    color: #958172;
    font-size: 13px;
    min-height: 22px;
    margin-bottom: 19px;
}

.product-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.price {
    font-size: 19px;
    font-weight: bold;
    color: #593b2b;
}

.add-btn {
    border: none;
    background: #593b2b;
    color: white;
    border-radius: 8px;
    width: 38px;
    height: 38px;
    font-size: 22px;
    cursor: pointer;
}

.add-btn:hover {
    background: #79543b;
}

.promo {
    background: #e9ddcf;
    padding: 35px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.promo h2 {
    font-family: Georgia, serif;
    font-size: 27px;
    margin-bottom: 10px;
}

.promo p {
    font-size: 14px;
    line-height: 1.8;
    color: #795f4c;
}

footer {
    background: #493326;
    color: #f8eee4;
    padding: 32px 7%;
    text-align: center;
    font-size: 13px;
    line-height: 2;
}

footer p {
    color: #d8c4b2;
}

#cart-count {
    display: inline-block;
    background: #e8c6a4;
    color: #493326;
    border-radius: 20px;
    padding: 2px 8px;
    font-size: 12px;
}

@media (max-width: 900px) {
    .product-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .navbar {
        padding: 0 5%;
    }

    .nav-links {
        gap: 12px;
    }

    .hero, .container {
        width: 90%;
    }
}

@media (max-width: 560px) {
    .navbar {
        height: auto;
        min-height: 70px;
        flex-wrap: wrap;
        gap: 12px;
        padding: 16px 5%;
    }

    .logo {
        font-size: 23px;
    }

    .nav-links {
        font-size: 12px;
        gap: 10px;
    }

    .hero {
        min-height: 340px;
    }

    .hero-content {
        padding: 28px;
    }

    .section-heading h2 {
        font-size: 26px;
    }

    .product-grid {
        gap: 12px;
    }

    .product-info {
        padding: 13px;
    }

    .product-picture {
        min-height: 125px;
        font-size: 55px;
    }

    .product-info h3 {
        font-size: 17px;
    }

    .description {
        font-size: 12px;
    }

    .promo {
        flex-direction: column;
        align-items: flex-start;
        padding: 25px;
    }
}
</style>
</head>

<body>

<nav class="navbar">
    <a href="home.php" class="logo">☕ Moya Cafe</a>

    
    <div class="nav-links">
        <a href="home.php#home">Home</a>
        <a href="products.php">Our Menu</a>
        <a href="home.php#about">About</a>
        <a href="cart.php">
            Cart <span id="cart-count">
                <?= (int)$cartCount ?>
            </span>
        </a>

        <a href="order_history.php">My Orders</a>
    <button type="button" id="themeToggle" class="theme-toggle">
    🌙 Dark Mode
    </button>
        <a href="logout.php" class="logout">Logout</a>
    </div>
</nav>


<section class="hero" id="home">
    <div class="hero-content">
        <div class="eyebrow">YOUR DAILY COFFEE</div>

        <h1>Good Coffee.<br>Good Mood.</h1>

        <p>
            เติมความสุขให้ทุกวันด้วยกาแฟแก้วโปรด
            คัดสรรความหอมละมุนในทุกแก้ว
            พร้อมเสิร์ฟความสุขให้คุณเสมอ
        </p>

        <a href="#menu" class="primary-btn">Explore Our Menu →</a>
    </div>
</section>

<section class="container" id="menu">

    <div class="section-heading">
        <div>
            <h2>Our Best Sellers</h2>
            <p>เมนูยอดนิยมที่อยากให้คุณได้ลอง</p>
        </div>
    </div>

    <div class="product-grid">

        <?php foreach ($products as $product): ?>
            <article class="product-card">

                <div class="product-picture">
                    <?= $product["icon"] ?>
                </div>

                <div class="product-info">

                    <div class="category">
                        <?= htmlspecialchars($product["category"]) ?>
                    </div>

                    <h3>
                        <?= htmlspecialchars($product["name"]) ?>
                    </h3>

                    <p class="description">
                        <?= htmlspecialchars($product["description"]) ?>
                    </p>

                    <div class="product-bottom">
                        <span class="price">
                            ฿<?= number_format($product["price"]) ?>
                        </span>




                        
                     
<form method="POST" action="home.php#menu">
    <input type="hidden" name="action" value="add_to_cart">

    <input
        type="hidden"
        name="product_id"
        value="<?= (int)$product["id"] ?>"
    >

    <button
        type="submit"
        class="add-btn"
        aria-label="เพิ่ม <?= htmlspecialchars(
            $product["name"],
            ENT_QUOTES,
            "UTF-8"
        ) ?> ลงตะกร้า"
    >+</button>
</form>

                    </div>

                </div>
            </article>
        <?php endforeach; ?>

    </div>
</section>

<section class="container" id="about">
    <div class="promo">
        <div>
            <h2>Moya Cafe  ☕</h2>
            <p>
                เพราะเราเชื่อว่ากาแฟดี ๆ สามารถทำให้วันธรรมดา
                องตนเองในอนาคต
            </p>
        </div>

        <a href="#menu" class="primary-btn">Discover More</a>
    </div>
</section>
<footer>
    <strong>☕ Moya Cafe</strong>
    <p>Good Coffee • Good Mood • Good Day</p>
    <p>© 2026 Moya Cafe. All rights reserved.</p>
</footer>


<script>

   {
        cartCount++;

        document.getElementById("cart-count").textContent = cartCount;

        alert("เพิ่ม " + productName + " ลงในรายการแล้ว");
    }
</script>




</body>
</html>
