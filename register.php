
<?php
session_start();
require_once __DIR__ . "/server.php";

$error = "";
$success = "";

$name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if (
        $name === "" ||
        $email === "" ||
        $password === "" ||
        $confirm_password === ""
    ) {
        $error = "กรุณากรอกข้อมูลให้ครบทุกช่อง";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "รูปแบบอีเมลไม่ถูกต้อง";

    } elseif (strlen($password) < 8) {
        $error = "รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร";

    } elseif ($password !== $confirm_password) {
        $error = "รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน";

    } elseif (strtolower($email) === "admin@cafe.com") {
        $error = "อีเมลนี้เป็นบัญชีตัวอย่าง กรุณาใช้อีเมลอื่น";

    } else {
        try {
            // ตรวจสอบว่าอีเมลนี้มีผู้ใช้แล้วหรือไม่
            $stmt = $conn->prepare(
                "SELECT id FROM users WHERE email = ? LIMIT 1"
            );

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "อีเมลนี้ถูกสมัครสมาชิกแล้ว";
                $stmt->close();

            } else {
                $stmt->close();

                // เข้ารหัสรหัสผ่านก่อนบันทึกลงฐานข้อมูล
                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // บันทึกสมาชิกลง MySQL
                $stmt = $conn->prepare(
                    "INSERT INTO users (name, email, password)
                     VALUES (?, ?, ?)"
                );

                $stmt->bind_param(
                    "sss",
                    $name,
                    $email,
                    $hashed_password
                );

                $stmt->execute();
                $stmt->close();

                $success = "สมัครสมาชิกสำเร็จ! สามารถเข้าสู่ระบบได้แล้ว";
            }

        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $error = "ไม่สามารถสมัครสมาชิกได้ กรุณาตรวจสอบฐานข้อมูล";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register | Moya Cafe</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Tahoma, sans-serif;
            min-height: 100vh;
            background: #f5eee5;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 25px;
            color: #493326;
        }

        .register-container {
            display: flex;
            width: 950px;
            max-width: 100%;
            min-height: 600px;
            background: #fffdf9;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(83, 54, 35, 0.15);
        }

        .cafe-side {
            width: 42%;
            background:
                linear-gradient(
                    rgba(54, 35, 24, 0.45),
                    rgba(54, 35, 24, 0.78)
                ),
                url("https://images.unsplash.com/photo-1445116572660-236099ec97a0?auto=format&fit=crop&w=1000&q=85")
                center / cover no-repeat;
            color: #fff8ef;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 40px;
        }

        .tag {
            font-size: 12px;
            letter-spacing: 2px;
            margin-bottom: 14px;
        }

        .cafe-side h1 {
            font-family: Georgia, serif;
            font-size: 40px;
            margin-bottom: 14px;
        }

        .cafe-side p {
            font-size: 14px;
            line-height: 1.8;
            color: #f3e6d8;
        }

        .form-side {
            width: 58%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px 48px;
        }

        .logo {
            font-size: 30px;
            margin-bottom: 8px;
        }

        h2 {
            font-size: 27px;
            margin-bottom: 8px;
            color: #4b3022;
        }

        .subtitle {
            font-size: 14px;
            color: #9a8879;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            height: 46px;
            padding: 0 14px;
            border: 1px solid #e5d8ca;
            border-radius: 10px;
            background: #fffdf9;
            color: #493326;
            font-size: 14px;
            outline: none;
            transition: 0.25s;
        }

        input:focus {
            border-color: #956746;
            box-shadow: 0 0 0 3px rgba(149, 103, 70, 0.12);
        }

        .register-btn {
            width: 100%;
            height: 48px;
            margin-top: 5px;
            background: #593b2b;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.25s;
        }

        .register-btn:hover {
            background: #79543b;
            transform: translateY(-1px);
        }

        .error, .success {
            padding: 11px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
            line-height: 1.6;
        }

        .error {
            background: #fff0ed;
            border: 1px solid #f0c7bf;
            color: #a33b2e;
        }

        .success {
            background: #edf8ed;
            border: 1px solid #bfdfbf;
            color: #286b35;
        }

        .login-link {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
            color: #958172;
        }

        .login-link a {
            color: #79543b;
            font-weight: bold;
            text-decoration: none;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            color: #a49383;
            font-size: 12px;
        }

        @media (max-width: 700px) {
            body {
                padding: 15px;
            }

            .register-container {
                width: 440px;
                min-height: auto;
            }

            .cafe-side {
                display: none;
            }

            .form-side {
                width: 100%;
                padding: 35px 26px;
            }
        }
    </style>
</head>

<body>

<div class="register-container">

    <div class="cafe-side">
        <div class="tag">COFFEE • COMFORT • CONNECTION</div>
        <h1>Moya Cafe</h1>
        <p>
            A little coffee, a little happiness.
            <br>
            สมัครสมาชิกเพื่อเริ่มต้นช่วงเวลาดี ๆ กับกาแฟแก้วโปรด
        </p>
    </div>

    <div class="form-side">

        <div class="logo">☕</div>

        <h2>Create Account</h2>
        <p class="subtitle">สมัครสมาชิก Moya Cafe</p>

        <?php if ($error !== ""): ?>
            <div class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div class="success">
                <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                <br>
                <a href="index.php">ไปหน้าเข้าสู่ระบบ</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">
                <label for="name">Full Name / ชื่อ-นามสกุล</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="กรอกชื่อและนามสกุล"
                    value="<?= htmlspecialchars(
                        $_POST["name"] ?? "",
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    autocomplete="name"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">Email Address / อีเมล</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="name@example.com"
                    value="<?= htmlspecialchars(
                        $_POST["email"] ?? "",
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password / รหัสผ่าน</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="อย่างน้อย 8 ตัวอักษร"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div class="form-group">
                <label for="confirm_password">
                    Confirm Password / ยืนยันรหัสผ่าน
                </label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="กรอกรหัสผ่านอีกครั้ง"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
            </div>

            <button type="submit" class="register-btn">
                Create Account →
            </button>

        </form>

        <div class="login-link">
            มีบัญชีอยู่แล้ว?
            <a href="index.php">เข้าสู่ระบบ</a>
        </div>

        <div class="footer">
            © 2026 Moya Cafe. All rights reserved.
        </div>

    </div>
</div>

</body>
</html>
