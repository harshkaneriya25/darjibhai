<?php

session_start();

require_once __DIR__ . '/../config/database.php';

$message = '';
$message_type = '';


/*
|--------------------------------------------------------------------------
| Registration Success Message
|--------------------------------------------------------------------------
*/

if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $message = 'Registration successful! Please login to continue.';
    $message_type = 'success';
}


/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($phone === '' || $password === '') {

        $message = 'Please enter phone number and password.';
        $message_type = 'error';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Find User
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
            "SELECT id, name, email, phone, password, status
             FROM users
             WHERE phone = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $phone);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            /*
            |--------------------------------------------------------------------------
            | Check Account Status
            |--------------------------------------------------------------------------
            */

            if ((int)$user['status'] !== 1) {

                $message = 'Your account is currently inactive.';
                $message_type = 'error';

            /*
            |--------------------------------------------------------------------------
            | Verify Password
            |--------------------------------------------------------------------------
            */

            } elseif (password_verify($password, $user['password'])) {

                /*
                |--------------------------------------------------------------------------
                | Regenerate Session ID
                |--------------------------------------------------------------------------
                */

                session_regenerate_id(true);

                /*
                |--------------------------------------------------------------------------
                | Store User Session
                |--------------------------------------------------------------------------
                */

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_phone'] = $user['phone'];

                /*
                |--------------------------------------------------------------------------
                | Redirect
                |--------------------------------------------------------------------------
                */

                header("Location: ../user/dashboard.php");
                exit;

            } else {

                $message = 'Invalid phone number or password.';
                $message_type = 'error';
            }

        } else {

            $message = 'Invalid phone number or password.';
            $message_type = 'error';
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

    <title>User Login - Darjibhai</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <link rel="stylesheet"
          href="../assets/css/auth.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="auth-page">

    <div class="auth-container">

        <!-- LEFT BRANDING -->

        <div class="auth-brand">

            <div class="brand-logo">
                <span>Darji</span><strong>bhai</strong>
            </div>

            <h1>Welcome Back!</h1>

            <p>
                Login to your Darjibhai account and
                manage your tailoring orders.
            </p>

            <div class="brand-features">

                <div class="feature-item">

                    <i class="fa-solid fa-shirt"></i>

                    <span>
                        Manage Your Orders
                    </span>

                </div>

                <div class="feature-item">

                    <i class="fa-solid fa-ruler-combined"></i>

                    <span>
                        View Your Measurements
                    </span>

                </div>

                <div class="feature-item">

                    <i class="fa-solid fa-location-dot"></i>

                    <span>
                        Track Order Status
                    </span>

                </div>

            </div>

            <a href="../index.php" class="auth-home-link">
                <i class="fa-solid fa-arrow-left"></i>
                Back
            </a>

        </div>


        <!-- RIGHT LOGIN FORM -->

        <div class="auth-form-section">

            <div class="auth-form">

                <div class="form-heading">

                    <h2>Login</h2>

                    <p>
                        Login to your customer account
                    </p>

                </div>


                <!-- MESSAGE -->

                <?php if ($message !== ''): ?>

                    <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>">

                        <?php echo htmlspecialchars($message); ?>

                    </div>

                <?php endif; ?>


                <!-- LOGIN FORM -->

                <form method="POST" action="">


                    <!-- PHONE -->

                    <div class="form-group">

                        <label for="phone">

                            <i class="fa-solid fa-phone"></i>

                            Phone Number

                        </label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            placeholder="Enter your phone number"
                            value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                            autocomplete="tel"
                            required
                        >

                    </div>


                    <!-- PASSWORD -->

                    <div class="form-group">

                        <label for="password">

                            <i class="fa-solid fa-lock"></i>

                            Password

                        </label>

                        <div class="password-input-wrap">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="password-visibility-btn" id="togglePassword" aria-label="Show password" aria-controls="password" aria-pressed="false" title="Show password">
                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>

                    </div>


                    <!-- LOGIN BUTTON -->

                    <button
                        type="submit"
                        class="auth-btn"
                    >

                        <span>Login</span>

                        <i class="fa-solid fa-arrow-right"></i>

                    </button>

                </form>


                <!-- REGISTER LINK -->

                <div class="auth-footer">

                    <p>

                        Don't have an account?

                        <a href="user-register.php">
                            Create Account
                        </a>

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    var passwordInput = document.getElementById('password');
    var icon = this.querySelector('i');
    var showPassword = passwordInput.type === 'password';

    passwordInput.type = showPassword ? 'text' : 'password';
    this.setAttribute('aria-pressed', String(showPassword));
    this.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
    this.title = showPassword ? 'Hide password' : 'Show password';
    icon.className = showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
});
</script>

<script src="/darjibhai/assets/js/form-validation.js" defer></script>
</body>

</html>
