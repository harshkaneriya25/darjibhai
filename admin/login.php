<?php

session_start();

require_once __DIR__ . '/../config/database.php';

$error = '';
$success = '';

$email = '';

/*
|--------------------------------------------------------------------------
| Already Logged In
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Registration Success Message
|--------------------------------------------------------------------------
*/

if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $success = 'Admin account created successfully. Please login.';
}

/*
|--------------------------------------------------------------------------
| Handle Login
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($email === '') {

        $error = 'Please enter your email address.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Please enter a valid email address.';

    } elseif ($password === '') {

        $error = 'Please enter your password.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Get Admin
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
            "SELECT
                admins.id,
                admins.name,
                admins.email,
                admins.phone,
                admins.password,
                admins.status,
                admins.role_id,
                roles.name AS role_name
             FROM admins
             INNER JOIN roles
                ON roles.id = admins.role_id
             WHERE admins.email = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $admin = $result->fetch_assoc();

            /*
            |--------------------------------------------------------------------------
            | Check Account Status
            |--------------------------------------------------------------------------
            */

            if ((int) $admin['status'] !== 1) {

                $error = 'Your admin account is inactive. Please contact the administrator.';

            } elseif (password_verify($password, $admin['password'])) {

                /*
                |--------------------------------------------------------------------------
                | Login Success
                |--------------------------------------------------------------------------
                */

                session_regenerate_id(true);

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_phone'] = $admin['phone'];
                $_SESSION['admin_role_id'] = $admin['role_id'];
                $_SESSION['admin_role'] = $admin['role_name'];

                header("Location: dashboard.php");
                exit;

            } else {

                $error = 'Invalid email or password.';
            }

        } else {

            $error = 'Invalid email or password.';
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Login | Darjibhai</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f6f6f6;
        }

        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }

        .auth-container {
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
            display: flex;
        }

        /* LEFT */

        .auth-left {
            width: 45%;
            background: #111111;
            color: #ffffff;
            padding: 55px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .auth-left::before {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: #f58220;
            opacity: 0.12;
            top: -120px;
            left: -100px;
        }

        .auth-left::after {
            content: "";
            position: absolute;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: #f58220;
            opacity: 0.10;
            bottom: -120px;
            right: -90px;
        }

        .brand {
            position: relative;
            z-index: 2;
            margin-bottom: 45px;
        }

        .brand-icon {
            width: 68px;
            height: 68px;
            background: #f58220;
            color: #ffffff;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 31px;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px rgba(245, 130, 32, 0.30);
        }

        .brand h1 {
            font-size: 38px;
            font-weight: 800;
            margin: 0;
        }

        .brand h1 span {
            color: #f58220;
        }

        .brand p {
            color: #bdbdbd;
            margin-top: 10px;
            font-size: 15px;
        }

        .welcome-text {
            position: relative;
            z-index: 2;
        }

        .welcome-text h3 {
            font-size: 23px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .welcome-text p {
            color: #aaaaaa;
            line-height: 1.7;
            font-size: 14px;
            margin: 0;
        }

        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            margin-top: 60px;
            transition: all 0.25s ease;
        }

        .btn-back-home:hover {
            background: #f58220;
            border-color: #f58220;
            color: #ffffff;
            transform: translateX(-3px);
        }

        /* RIGHT */

        .auth-right {
            width: 55%;
            padding: 50px 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            width: fit-content;
            background: #fff3e8;
            color: #d96600;
            border-radius: 30px;
            padding: 7px 13px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .form-title {
            margin-bottom: 28px;
        }

        .form-title h2 {
            font-size: 31px;
            font-weight: 750;
            color: #111111;
            margin-bottom: 7px;
        }

        .form-title p {
            color: #777777;
            margin: 0;
            font-size: 14px;
        }

        .form-label {
            font-weight: 600;
            color: #333333;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .input-group-custom {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999999;
            z-index: 5;
        }

        .form-control {
            min-height: 52px;
            border-radius: 12px;
            border: 1px solid #dddddd;
            padding-left: 45px;
            padding-right: 45px;
            font-size: 14px;
            transition: 0.2s ease;
        }

        .form-control:focus {
            border-color: #f58220;
            box-shadow: 0 0 0 4px rgba(245, 130, 32, 0.10);
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #999999;
            cursor: pointer;
            z-index: 5;
        }

        .password-toggle:hover {
            color: #f58220;
        }

        .login-btn {
            width: 100%;
            min-height: 52px;
            border: none;
            border-radius: 12px;
            background: #f58220;
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            transition: 0.25s ease;
            box-shadow: 0 8px 20px rgba(245, 130, 32, 0.25);
        }

        .login-btn:hover {
            background: #df6d0d;
            transform: translateY(-1px);
        }

        .register-link {
            text-align: center;
            margin-top: 22px;
            color: #777777;
            font-size: 14px;
        }

        .register-link a {
            color: #f58220;
            font-weight: 700;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 12px;
            font-size: 14px;
        }

        .alert-danger {
            background: #fff1f1;
            border-color: #ffd0d0;
            color: #b42318;
        }

        .alert-success {
            background: #edfff4;
            border-color: #b7ebc9;
            color: #16743b;
        }

        .login-info {
            margin-top: 25px;
            padding: 13px 15px;
            background: #f8f8f8;
            border-radius: 12px;
            color: #777777;
            font-size: 12px;
            text-align: center;
        }

        /* RESPONSIVE */

        @media (max-width: 800px) {

            .auth-container {
                flex-direction: column;
                max-width: 600px;
            }

            .auth-left,
            .auth-right {
                width: 100%;
            }

            .auth-left {
                padding: 35px;
                min-height: 280px;
            }

            .auth-right {
                padding: 40px 30px;
            }
        }

        @media (max-width: 500px) {

            .auth-wrapper {
                padding: 0;
            }

            .auth-container {
                min-height: 100vh;
                border-radius: 0;
            }

            .auth-left {
                padding: 30px 25px;
            }

            .auth-right {
                padding: 30px 22px;
            }

            .brand h1 {
                font-size: 30px;
            }

            .form-title h2 {
                font-size: 25px;
            }
        }

    </style>

</head>

<body>

<div class="auth-wrapper">

    <div class="auth-container">

        <!-- LEFT SIDE -->

        <div class="auth-left">

            <div class="brand">

                <div class="brand-icon">
                    <i class="fa-solid fa-scissors"></i>
                </div>

                <h1>
                    Darji<span>bhai</span>
                </h1>

                <p>
                    Sewing the Happiness
                </p>

            </div>

            <div class="welcome-text">

                <h3>
                    Welcome Back!
                </h3>

                <p>
                    Login to your Darjibhai administration
                    panel and manage your tailoring business,
                    customers, products and orders.
                </p>

                <!-- REDIRECT BUTTON -->
                <a href="/darjibhai/index.php" class="btn-back-home">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Back to Website</span>
                </a>

            </div>

        </div>

        <!-- RIGHT SIDE -->

        <div class="auth-right">

            <div class="form-title">

                <div class="admin-badge">

                    <i class="fa-solid fa-user-shield"></i>

                    ADMIN PANEL

                </div>

                <h2>
                    Admin Login
                </h2>

                <p>
                    Enter your credentials to continue.
                </p>

            </div>

            <?php if ($error !== ''): ?>

                <div class="alert alert-danger d-flex align-items-center mb-4">

                    <i class="fa-solid fa-circle-exclamation me-2"></i>

                    <div>
                        <?= htmlspecialchars($error) ?>
                    </div>

                </div>

            <?php endif; ?>

            <?php if ($success !== ''): ?>

                <div class="alert alert-success d-flex align-items-center mb-4">

                    <i class="fa-solid fa-circle-check me-2"></i>

                    <div>
                        <?= htmlspecialchars($success) ?>
                    </div>

                </div>

            <?php endif; ?>

            <form method="POST" autocomplete="off">

                <!-- EMAIL -->

                <div class="mb-4">

                    <label class="form-label">
                        Email Address
                    </label>

                    <div class="input-group-custom">

                        <i class="fa-solid fa-envelope input-icon"></i>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="Enter admin email"
                            value="<?= htmlspecialchars($email) ?>"
                            required
                            autofocus
                        >

                    </div>

                </div>

                <!-- PASSWORD -->

                <div class="mb-4">

                    <label class="form-label">
                        Password
                    </label>

                    <div class="input-group-custom">

                        <i class="fa-solid fa-lock input-icon"></i>

                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            placeholder="Enter your password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword()"
                        >
                            <i class="fa-solid fa-eye"></i>
                        </button>

                    </div>

                </div>

                <!-- LOGIN -->

                <button
                    type="submit"
                    class="login-btn"
                >

                    <i class="fa-solid fa-right-to-bracket me-2"></i>

                    Login to Admin Panel

                </button>

            </form>

        </div>

    </div>

</div>

<script>

function togglePassword() {

    const input = document.getElementById('password');
    const icon = document.querySelector('.password-toggle i');

    if (input.type === 'password') {

        input.type = 'text';

        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');

    } else {

        input.type = 'password';

        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');

    }
}

</script>

<script src="/darjibhai/assets/js/form-validation.js" defer></script>
</body>
</html>