<?php

require_once __DIR__ . '/../config/database.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $address = trim($_POST['address'] ?? '');

    // Basic validation
    if (
        $name === '' ||
        $email === '' ||
        $phone === '' ||
        $password === '' ||
        $confirm_password === ''
    ) {
        $message = 'Please fill all required fields.';
        $message_type = 'error';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $message_type = 'error';

    } elseif ($password !== $confirm_password) {
        $message = 'Password and confirm password do not match.';
        $message_type = 'error';

    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
        $message_type = 'error';

    } else {

        // Check duplicate email or phone
        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1"
        );

        $check->bind_param("ss", $email, $phone);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = 'Email or phone number is already registered.';
            $message_type = 'error';

        } else {

            // Hash password
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Insert user
            $stmt = $conn->prepare(
                "INSERT INTO users
                (name, email, phone, password, actual_password, address)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssssss",
                $name,
                $email,
                $phone,
                $hashed_password,
                $password,
                $address
            );

            if ($stmt->execute()) {

                header("Location: user-login.php?registered=1");
                exit;

            } else {

                $message = 'Registration failed. Please try again.';
                $message_type = 'error';
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Registration - Darjibhai</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <link rel="stylesheet"
          href="../assets/css/auth.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<div class="auth-page">

    <div class="auth-container">

        <!-- Left Section -->
        <div class="auth-brand">

            <div class="brand-logo">
                <span>Darji</span><strong>bhai</strong>
            </div>

            <h1>Welcome to Darjibhai</h1>

            <p>
                Create your account and manage your
                tailoring orders with ease.
            </p>

            <div class="brand-features">

                <div class="feature-item">
                    <i class="fa-solid fa-shirt"></i>
                    <span>Manage Tailoring Orders</span>
                </div>

                <div class="feature-item">
                    <i class="fa-solid fa-ruler-combined"></i>
                    <span>Save Your Measurements</span>
                </div>

                <div class="feature-item">
                    <i class="fa-solid fa-truck"></i>
                    <span>Track Your Orders</span>
                </div>

            </div>

            <a href="../index.php" class="auth-home-link">
                <i class="fa-solid fa-arrow-left"></i>
                Back
            </a>

        </div>


        <!-- Right Section -->
        <div class="auth-form-section">

            <div class="auth-form">

                <div class="form-heading">
                    <h2>Create Account</h2>
                    <p>Register as a Darjibhai customer</p>
                </div>


                <?php if ($message !== ''): ?>

                    <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>

                <?php endif; ?>


                <form method="POST" action="">

                    <!-- Name -->
                    <div class="form-group">

                        <label for="name">
                            <i class="fa-solid fa-user"></i>
                            Full Name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Enter your full name"
                            value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                            required
                        >

                    </div>


                    <!-- Email -->
                    <div class="form-group">

                        <label for="email">
                            <i class="fa-solid fa-envelope"></i>
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            required
                        >

                    </div>


                    <!-- Phone -->
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
                            required
                        >

                    </div>


                    <!-- Password -->
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
                                placeholder="Create a password"
                                autocomplete="new-password"
                                minlength="6"
                                data-error-message="Use at least 6 characters for your password."
                                required
                            >
                            <button type="button" class="password-visibility-btn" id="togglePassword" aria-label="Show password" aria-controls="password" aria-pressed="false" title="Show password">
                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>

                    </div>


                    <!-- Confirm Password -->
                    <div class="form-group">

                        <label for="confirm_password">
                            <i class="fa-solid fa-lock"></i>
                            Confirm Password
                        </label>

                        <div class="password-input-wrap">
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                placeholder="Confirm your password"
                                autocomplete="new-password"
                                data-match="password"
                                data-match-message="Your passwords do not match."
                                required
                            >
                            <button type="button" class="password-visibility-btn" id="toggleConfirmPassword" aria-label="Show confirm password" aria-controls="confirm_password" aria-pressed="false" title="Show confirm password">
                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>

                    </div>


                    <!-- Address -->
                    <div class="form-group">

                        <label for="address">
                            <i class="fa-solid fa-location-dot"></i>
                            Address
                        </label>

                        <textarea
                            id="address"
                            name="address"
                            placeholder="Enter your address"
                            rows="3"
                        ><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>

                    </div>


                    <!-- Submit -->
                    <button type="submit" class="auth-btn">

                        <span>Create Account</span>

                        <i class="fa-solid fa-arrow-right"></i>

                    </button>

                </form>


                <div class="auth-footer">

                    <p>
                        Already have an account?
                        <a href="user-login.php">
                            Login here
                        </a>
                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
document.querySelectorAll('.password-visibility-btn').forEach(function (button) {
    button.addEventListener('click', function () {
        var input = document.getElementById(this.getAttribute('aria-controls'));
        var icon = this.querySelector('i');
        var showPassword = input.type === 'password';

        input.type = showPassword ? 'text' : 'password';
        this.setAttribute('aria-pressed', String(showPassword));
        this.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
        this.title = showPassword ? 'Hide password' : 'Show password';
        icon.className = showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
    });
});
</script>

<script src="/darjibhai/assets/js/form-validation.js" defer></script>
</body>
</html>
