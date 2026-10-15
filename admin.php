
<?php
session_start();
require_once __DIR__ . "/server.php";

// ตรวจสอบสิทธิ์ผู้ดูแลระบบ
if (
    !isset($_SESSION["cafe_user_id"]) ||
    !isset($_SESSION["cafe_user_role"]) ||
    $_SESSION["cafe_user_role"] !== "admin"
) {
    header("Location: index.php");
    exit;
}

// สรุปข้อมูลจากฐานข้อมูล
$totalProducts = $conn->query(
    "SELECT COUNT(*) AS total FROM products"
)->fetch_assoc()["total"];

$totalUsers = $conn->query(
    "SELECT COUNT(*) AS total FROM users"
)->fetch_assoc()["total"];

$totalOrders = $conn->query(
    "SELECT COUNT(*) AS total FROM orders"
)->fetch_assoc()["total"];

$pendingOrders = $conn->query(
    "SELECT COUNT(*) AS total FROM orders WHERE status = 'pending'"
)->fetch_assoc()["total"];

$totalRevenue = $conn->query(
    "SELECT COALESCE(SUM(total_amount), 0) AS total
     FROM orders
     WHERE status IN ('completed', 'delivered')"
)->fetch_assoc()["total"];

// คำสั่งซื้อล่าสุด
$recentOrders = $conn->query(
    "SELECT id, customer_name, total_amount, status, created_at
     FROM orders
     ORDER BY created_at DESC
     LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Moya Cafe</title>

    <link rel="stylesheet" href="style.css">

    <style>
        body {
            margin: 0;
            background: #f7f3ec;
            color: #493629;
            font-family: Arial, Tahoma, sans-serif;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 230px;
            flex-shrink: 0;
            background: #493629;
            color: #fff;
            padding: 28px 18px;
            box-sizing: border-box;
        }

        .brand {
            font-size: 23px;
            font-weight: bold;
            margin-bottom: 35px;
        }

        .sidebar a {
            display: block;
            color: #f8eee2;
            text-decoration: none;
            padding: 13px 14px;
            margin: 7px 0;
            border-radius: 8px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #80634d;
        }

        .main-content {
            flex: 1;
            min-width: 0;
            padding: 30px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 28px;
        }

        .topbar h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .muted {
            color: #897969;
            font-size: 14px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 4px 15px #4936290d;
        }

        .stat-card p {
            color: #897969;
            margin: 0 0 14px;
        }

        .stat-card h2 {
            margin: 0;
            font-size: 29px;
        }

        .section-card {
            background: #fff;
            border-radius: 14px;
            padding: 22px;
            overflow-x: auto;
        }

        .section-card h2 {
            margin-top: 0;
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 620px;
        }

        th, td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #eee5dc;
        }

        th {
            background: #fbf7f1;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            background: #f4e7d4;
            font-size: 12px;
        }

        .logout {
            color: #ffd9c7 !important;
            margin-top: 30px !important;
        }

        .btn {
            display: inline-block;
            background: #493629;
            color: #fff;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
        }

        @media (max-width: 900px) {
            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .admin-layout {
                display: block;
            }

            .sidebar {
                width: 100%;
            }

            .main-content {
                padding: 18px;
            }

            .stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
<div class="admin-layout">

    <aside class="sidebar">
        <div class="brand">☕ Moya Cafe</div>

        <a href="admin.php" class="active">📊 Dashboard</a>
        <a href="admin_products.php">☕ จัดการสินค้า</a>
        <a href="admin_orders.php">🧾 จัดการคำสั่งซื้อ</a>
        <a href="admin_users.php">👥 จัดการสมาชิก</a>
        <a href="home.php">🏠 กลับหน้าเว็บไซต์</a>
        <a href="logout.php" class="logout">ออกจากระบบ</a>
    </aside>

    <main class="main-content">

        <div class="topbar">
            <div>
                <h1>Dashboard</h1>
                <div class="muted">ภาพรวมการจัดการร้าน Moya Cafe</div>
            </div>

            <div>
                ผู้ดูแล: <?= htmlspecialchars(
                    $_SESSION["cafe_user_name"] ?? "Admin",
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <p>☕ สินค้าทั้งหมด</p>
                <h2><?= number_format((int)$totalProducts) ?></h2>
            </div>

            <div class="stat-card">
                <p>👥 สมาชิกทั้งหมด</p>
                <h2><?= number_format((int)$totalUsers) ?></h2>
            </div>

            <div class="stat-card">
                <p>🧾 คำสั่งซื้อทั้งหมด</p>
                <h2><?= number_format((int)$totalOrders) ?></h2>
            </div>

            <div class="stat-card">
                <p>⏳ คำสั่งซื้อรอดำเนินการ</p>
                <h2><?= number_format((int)$pendingOrders) ?></h2>
            </div>

            <div class="stat-card">
                <p>💰 ยอดขายจากออเดอร์ที่เสร็จสิ้น</p>
                <h2>฿<?= number_format((float)$totalRevenue, 2) ?></h2>
            </div>
        </div>

        <section class="section-card">
            <h2>คำสั่งซื้อล่าสุด</h2>

            <table>
                <thead>
                    <tr>
                        <th>เลขที่คำสั่งซื้อ</th>
                        <th>ชื่อลูกค้า</th>
                        <th>ยอดรวม</th>
                        <th>สถานะ</th>
                        <th>วันที่สั่งซื้อ</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($recentOrders->num_rows > 0): ?>
                    <?php while ($order = $recentOrders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= (int)$order["id"] ?></td>
                            <td><?= htmlspecialchars(
                                $order["customer_name"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?></td>
                            <td>฿<?= number_format(
                                (float)$order["total_amount"],
                                2
                            ) ?></td>
                            <td>
                                <span class="status">
                                    <?= htmlspecialchars(
                                        $order["status"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars(
                                $order["created_at"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">ยังไม่มีคำสั่งซื้อ</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>

    </main>
</div>
</body>
</html>
