
<?php
session_start();
require_once __DIR__ . "/server.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "กรุณากรอกอีเมลและรหัสผ่าน";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, name, email, password
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $stmt->bind_result(
            $user_id,
            $user_name,
            $user_email,
            $password_hash
        );

        if ($stmt->fetch() &&
            password_verify($password, $password_hash)) {

            session_regenerate_id(true);

            $_SESSION["cafe_user"] = $user_email;
            $_SESSION["cafe_user_id"] = $user_id;
            $_SESSION["cafe_user_name"] = $user_name;

            $stmt->close();

            header("Location: home.php");
            exit;
        }

        $stmt->close();
        $error = "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login | Moya Cafe</title>

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

        .login-container {
            display: flex;
            width: 900px;
            max-width: 100%;
            min-height: 550px;
            background: #fffdf9;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(83, 54, 35, 0.15);
        }

        .cafe-side {
            width: 45%;
            background:
                linear-gradient(
                    rgba(54, 35, 24, 0.45),
                    rgba(54, 35, 24, 0.75)
                ),
                url("https://images.unsplash.com/photo-1445116572660-236099ec97a0?auto=format&fit=crop&w=1000&q=85")
                center / cover no-repeat;
            color: #fff8ef;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 40px;
        }

        .cafe-side .tag {
            font-size: 12px;
            letter-spacing: 3px;
            margin-bottom: 14px;
            text-transform: uppercase;
        }

        .cafe-side h1 {
            font-family: Georgia, serif;
            font-size: 42px;
            margin-bottom: 14px;
        }

        .cafe-side p {
            font-size: 14px;
            line-height: 1.8;
            color: #f3e6d8;
        }

        .form-side {
            width: 55%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px 48px;
        }

        .logo {
            font-size: 34px;
            margin-bottom: 10px;
        }

        .form-side h2 {
            font-size: 28px;
            margin-bottom: 8px;
            color: #4b3022;
        }

        .subtitle {
            font-size: 14px;
            color: #9a8879;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 9px;
        }

        .input-box {
            position: relative;
        }

        .form-group input {
            width: 100%;
            height: 48px;
            padding: 0 14px;
            border: 1px solid #e5d8ca;
            border-radius: 10px;
            background: #fffdf9;
            color: #493326;
            font-size: 14px;
            outline: none;
            transition: 0.25s;
        }

        .form-group input:focus {
            border-color: #956746;
            box-shadow: 0 0 0 3px rgba(149, 103, 70, 0.12);
        }

        .password-input {
            padding-right: 65px !important;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #8b6a52;
            cursor: pointer;
            font-size: 12px;
        }

        .login-btn {
            width: 100%;
            height: 49px;
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

        .login-btn:hover {
            background: #79543b;
            transform: translateY(-1px);
        }

        .error {
            background: #fff0ed;
            border: 1px solid #f0c7bf;
            color: #a33b2e;
            padding: 11px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .register-link {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
            color: #958172;
        }

        .register-link a {
            color: #79543b;
            font-weight: bold;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .footer {
            margin-top: 25px;
            text-align: center;
            color: #a49383;
            font-size: 12px;
        }

        @media (max-width: 700px) {
            body {
                padding: 15px;
            }

            .login-container {
                width: 440px;
                min-height: auto;
            }

            .cafe-side {
                display: none;
            }

            .form-side {
                width: 100%;
                padding: 40px 28px;
            }
        }
    </style>
</head>

<body>

<div class="login-container">

    <div class="cafe-side">
        <div class="tag">Coffee • Comfort • Connection</div>
        <h1>Moya Cafe</h1>
        <p>
            A little coffee, a little happiness.
            <br>
            เติมเต็มทุกวันด้วยกาแฟแก้วโปรด
        </p>
    </div>

    <div class="form-side">

        <div class="logo">☕</div>

        <h2>Welcome Back!</h2>

        <p class="subtitle">
            เข้าสู่ระบบเพื่อเริ่มต้นวันดี ๆ กับ Moya Cafe
        </p>

        <?php if ($error !== ""): ?>
            <div class="error">
                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">
                <label for="email">Email Address</label>
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
                    required
                    autocomplete="username"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>

                <div class="input-box">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="password-input"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >

                    <button
                        type="button"
                        class="toggle-password"
                        onclick="togglePassword()"
                    >
                        Show
                    </button>
                </div>
            </div>

            <button type="submit" class="login-btn">
                Login →
            </button>

        </form>

        <div class="register-link">
            ยังไม่มีบัญชี?
            <a href="register.php">สมัครสมาชิก</a>
        </div>

        <div class="footer">
            © 2026 Moya Cafe. All rights reserved.
        </div>

    </div>
</div>

<script>
    function togglePassword() {
        const password = document.getElementById("password");
        const button = document.querySelector(".toggle-password");

        if (password.type === "password") {
            password.type = "text";
            button.textContent = "Hide";
        } else {
            password.type = "password";
            button.textContent = "Show";
        }
    }
</script>

</body>
</html>
