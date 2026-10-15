
<?php
session_start();

// ต้องเข้าสู่ระบบก่อนจึงจะเข้าหน้าเมนูได้
if (!isset($_SESSION["cafe_user"])) {
    header("Location: index.php");
    exit;
}

$products = [
    [
        "name" => "Espresso",
        "category" => "coffee",
        "description" => "กาแฟเข้มข้น หอมกรุ่น",
        "price" => 50,
        "icon" => "☕",
        "tag" => "BEST SELLER"
    ],
    [
        "name" => "Americano",
        "category" => "coffee",
        "description" => "กาแฟดำ รสชาติเข้มกำลังดี",
        "price" => 55,
        "icon" => "🧊",
        "tag" => "POPULAR"
    ],
    [
        "name" => "Cafe Latte",
        "category" => "coffee",
        "description" => "เอสเพรสโซผสมนมสด",
        "price" => 65,
        "icon" => "🥛",
        "tag" => "RECOMMENDED"
    ],
    [
        "name" => "Mocha",
        "category" => "coffee",
        "description" => "กาแฟผสมช็อกโกแลต",
        "price" => 70,
        "icon" => "🍫",
        "tag" => ""
    ],
    [
        "name" => "Matcha Latte",
        "category" => "tea",
        "description" => "มัทฉะหอมละมุนผสมนม",
        "price" => 75,
        "icon" => "🍵",
        "tag" => "POPULAR"
    ],
    [
        "name" => "Thai Milk Tea",
        "category" => "tea",
        "description" => "ชาไทยเข้มข้น หวานมัน",
        "price" => 60,
        "icon" => "🧋",
        "tag" => ""
    ],
    [
        "name" => "Strawberry Soda",
        "category" => "other",
        "description" => "สตรอว์เบอร์รีโซดาสดชื่น",
        "price" => 55,
        "icon" => "🍓",
        "tag" => "NEW"
    ],
    [
        "name" => "Chocolate Cake",
        "category" => "dessert",
        "description" => "เค้กช็อกโกแลตเนื้อนุ่ม",
        "price" => 85,
        "icon" => "🍰",
        "tag" => "RECOMMENDED"
    ],
    [
        "name" => "Croissant",
        "category" => "dessert",
        "description" => "ครัวซองต์เนยหอมกรอบ",
        "price" => 65,
        "icon" => "🥐",
        "tag" => ""
    ],
    [
        "name" => "Blueberry Muffin",
        "category" => "dessert",
        "description" => "มัฟฟินบลูเบอร์รีอบสด",
        "price" => 60,
        "icon" => "🧁",
        "tag" => ""
    ]
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Our Menu | Cozy Cafe</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: "Segoe UI", Tahoma, sans-serif;
    background: #faf6f0;
    color: #493326;
}

a {
    color: inherit;
    text-decoration: none;
}

.navbar {
    background: #fffdf9;
    min-height: 76px;
    padding: 16px 7%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    border-bottom: 1px solid #eee3d6;
}

.logo {
    font-family: Georgia, serif;
    font-size: 27px;
    font-weight: bold;
    white-space: nowrap;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 25px;
    font-size: 14px;
}

.nav-links a:hover {
    color: #a47752;
}

.cart-link {
    background: #f0e4d5;
    padding: 9px 13px;
    border-radius: 8px;
}

.logout {
    background: #593b2b;
    color: white !important;
    padding: 10px 16px;
    border-radius: 8px;
}

.hero {
    width: 86%;
    max-width: 1250px;
    min-height: 300px;
    margin: 35px auto;
    border-radius: 22px;
    overflow: hidden;
    display: flex;
    align-items: center;
    color: white;
    background:
        linear-gradient(90deg,
            rgba(48, 30, 20, .88),
            rgba(48, 30, 20, .20)),
        url("https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=1600&q=85")
        center / cover no-repeat;
}

.hero-content {
    padding: 42px;
    max-width: 600px;
}

.eyebrow {
    color: #e8c6a4;
    letter-spacing: 3px;
    font-size: 12px;
    margin-bottom: 14px;
}

.hero h1 {
    font-family: Georgia, serif;
    font-size: clamp(36px, 5vw, 53px);
    margin-bottom: 15px;
}

.hero p {
    color: #f4e8dc;
    line-height: 1.8;
    font-size: 14px;
}

.container {
    width: 86%;
    max-width: 1250px;
    margin: 50px auto 70px;
}

.heading {
    text-align: center;
    margin-bottom: 30px;
}

.heading h2 {
    font-family: Georgia, serif;
    font-size: 35px;
    margin-bottom: 10px;
}

.heading p {
    color: #958172;
    font-size: 14px;
}

.toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
    margin-bottom: 30px;
}

.filters {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.filter-btn {
    border: 1px solid #e3d5c6;
    background: #fffdf9;
    color: #654a37;
    padding: 10px 16px;
    border-radius: 22px;
    cursor: pointer;
    font-size: 13px;
    transition: .2s;
}

.filter-btn:hover,
.filter-btn.active {
    background: #593b2b;
    border-color: #593b2b;
    color: white;
}

.search {
    width: 235px;
    max-width: 100%;
    padding: 12px 15px;
    border: 1px solid #e3d5c6;
    border-radius: 10px;
    background: #fffdf9;
    outline: none;
    color: #493326;
}

.search:focus {
    border-color: #956746;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 22px;
}

.product-card {
    background: #fffdf9;
    border: 1px solid #eee3d6;
    border-radius: 16px;
    overflow: hidden;
    transition: .25s;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 28px rgba(83, 54, 35, .10);
}

.product-image {
    height: 175px;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #f0e4d5;
    position: relative;
    font-size: 72px;
}

.tag {
    position: absolute;
    top: 12px;
    left: 12px;
    font-size: 9px;
    letter-spacing: .5px;
    background: #fffdf9;
    color: #79543b;
    padding: 6px 9px;
    border-radius: 5px;
}

.product-info {
    padding: 18px;
}

.product-info h3 {
    font-family: Georgia, serif;
    font-size: 20px;
    margin-bottom: 8px;
}

.description {
    color: #958172;
    font-size: 12px;
    line-height: 1.7;
    min-height: 40px;
}

.product-bottom {
    margin-top: 15px;
    padding-top: 13px;
    border-top: 1px solid #eee3d6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.price {
    font-size: 19px;
    font-weight: bold;
    color: #593b2b;
}

.add-btn {
    border: none;
    border-radius: 8px;
    background: #593b2b;
    color: white;
    width: 38px;
    height: 38px;
    font-size: 23px;
    cursor: pointer;
}

.add-btn:hover {
    background: #79543b;
}

.empty {
    display: none;
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px;
    color: #958172;
}

.promo {
    background: #e9ddcf;
    padding: 35px;
    border-radius: 18px;
    text-align: center;
}

.promo h2 {
    font-family: Georgia, serif;
    font-size: 28px;
    margin-bottom: 12px;
}

.promo p {
    font-size: 14px;
    color: #795f4c;
    line-height: 1.8;
}

footer {
    background: #493326;
    color: #fff8ef;
    padding: 30px;
    text-align: center;
    line-height: 2;
    font-size: 13px;
}

footer p {
    color: #d8c4b2;
}

@media (max-width: 1000px) {
    .product-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .navbar {
        padding: 16px 5%;
    }

    .nav-links {
        gap: 12px;
    }
}

@media (max-width: 720px) {
    .navbar {
        align-items: flex-start;
        flex-direction: column;
    }

    .nav-links {
        flex-wrap: wrap;
        font-size: 12px;
    }

    .hero, .container {
        width: 92%;
    }

    .hero-content {
        padding: 28px;
    }

    .product-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 13px;
    }

    .product-image {
        height: 135px;
        font-size: 56px;
    }

    .product-info {
        padding: 13px;
    }

    .product-info h3 {
        font-size: 17px;
    }

    .toolbar {
        align-items: stretch;
        flex-direction: column;
    }

    .search {
        width: 100%;
    }
}

@media (max-width: 360px) {
    .product-grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<nav class="navbar">
    <a class="logo" href="home.php">☕ Moya Cafe</a>

    <div class="nav-links">
        <a href="home.php">Home</a>
        <a href="cafe.php">Cafe Menu</a>
        <a href="cafe.php#menu">Products</a>
        <a class="cart-link" href="cafe.php#menu">
            🛒 Cart <span id="cart-count">0</span>
        </a>
        <a class="logout" href="logout.php">Logout</a>
    </div>
</nav>

<section class="hero">
    <div class="hero-content">
        <div class="eyebrow">MADE WITH LOVE</div>
        <h1>Our Coffee & Menu</h1>
        <p>
            ค้นพบเมนูเครื่องดื่มและขนมแสนอร่อย
            ที่คัดสรรมาเพื่อเติมเต็มวันดี ๆ ของคุณ
        </p>
    </div>
</section>

<main class="container" id="menu">

    <div class="heading">
        <h2>Explore Our Menu</h2>
        <p>เลือกเมนูโปรดของคุณจาก Cozy Cafe</p>
    </div>

    <div class="toolbar">
        <div class="filters">
            <button class="filter-btn active" data-category="all">
                All Menu
            </button>
            <button class="filter-btn" data-category="coffee">
                Coffee
            </button>
            <button class="filter-btn" data-category="tea">
                Tea
            </button>
            <button class="filter-btn" data-category="other">
                Other Drinks
            </button>
            <button class="filter-btn" data-category="dessert">
                Desserts
            </button>
        </div>

        <input
            class="search"
            type="search"
            id="search"
            placeholder="ค้นหาเมนู..."
            aria-label="ค้นหาเมนู"
        >
    </div>

    <div class="product-grid" id="product-grid">

        <?php foreach ($products as $product): ?>
            <article
                class="product-card"
                data-category="<?= htmlspecialchars($product["category"]) ?>"
                data-name="<?= htmlspecialchars($product["name"], ENT_QUOTES, 'UTF-8') ?>"
            >
                <div class="product-image">
                    <?php if ($product["tag"] !== ""): ?>
                        <span class="tag">
                            <?= htmlspecialchars($product["tag"]) ?>
                        </span>
                    <?php endif; ?>

                    <?= $product["icon"] ?>
                </div>

                <div class="product-info">
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

                        <button
                            class="add-btn"
                            type="button"
                            aria-label="เพิ่มสินค้า"
                            data-name="<?= htmlspecialchars($product["name"], ENT_QUOTES, 'UTF-8') ?>"
                            data-price="<?= (int)$product["price"] ?>"
                        >+</button>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>

        <div class="empty" id="empty">
            ไม่พบเมนูที่ค้นหา ลองค้นหาด้วยคำอื่นนะคะ
        </div>

    </div>
</main>

<section class="container">
    <div class="promo">
        <h2>Every Cup, Made with Love ☕</h2>
        <p>
            กาแฟแก้วโปรดและขนมแสนอร่อย
            พร้อมเติมเต็มช่วงเวลาพิเศษของคุณ
        </p>
    </div>
</section>

<footer>
    <strong>☕ Cozy Cafe</strong>
    <p>Good Coffee • Good Mood • Good Day</p>
    <p>© 2026 Cozy Cafe. All rights reserved.</p>
</footer>

<script>
let selectedCategory = "all";
let cartCount = 0;

const cards = [...document.querySelectorAll(".product-card")];
const filterButtons = document.querySelectorAll(".filter-btn");
const searchInput = document.getElementById("search");
const empty = document.getElementById("empty");

function filterProducts() {
    const keyword = searchInput.value.trim().toLowerCase();
    let visibleCount = 0;

    cards.forEach(card => {
        const category = card.dataset.category;
        const name = card.dataset.name.toLowerCase();

        const matchesCategory =
            selectedCategory === "all" ||
            category === selectedCategory;

        const matchesSearch = name.includes(keyword);
        const visible = matchesCategory && matchesSearch;

        card.style.display = visible ? "" : "none";

        if (visible) visibleCount++;
    });

    empty.style.display = visibleCount === 0 ? "block" : "none";
}

filterButtons.forEach(button => {
    button.addEventListener("click", () => {
        selectedCategory = button.dataset.category;

        filterButtons.forEach(item => item.classList.remove("active"));
        button.classList.add("active");

        filterProducts();
    });
});

searchInput.addEventListener("input", filterProducts);

document.querySelectorAll(".add-btn").forEach(button => {
    button.addEventListener("click", () => {
        cartCount++;
        document.getElementById("cart-count").textContent = cartCount;

        const name = button.dataset.name;
        const price = button.dataset.price;

        alert("เพิ่ม " + name + " ราคา " + price +
              " บาท ลงในรายการแล้ว");
    });
});
</script>

</body>
</html>
