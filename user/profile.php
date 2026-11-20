<?php

require_once __DIR__ . '/../includes/user-auth.php';
require_once __DIR__ . '/../config/database.php';

$user_id = $_SESSION['user_id'];

$message = '';
$message_type = '';

/*
|--------------------------------------------------------------------------
| Update Profile
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '' || $email === '' || $phone === '') {

        $message = 'Name, email and phone are required.';
        $message_type = 'error';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = 'Please enter a valid email address.';
        $message_type = 'error';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Check Duplicate Email / Phone
        |--------------------------------------------------------------------------
        */

        $check = $conn->prepare(
            "SELECT id
             FROM users
             WHERE (email = ? OR phone = ?)
             AND id != ?
             LIMIT 1"
        );

        $check->bind_param(
            "ssi",
            $email,
            $phone,
            $user_id
        );

        $check->execute();

        $duplicate = $check->get_result();

        if ($duplicate->num_rows > 0) {

            $message = 'Email or phone number is already used by another account.';
            $message_type = 'error';

        } else {

            /*
            |--------------------------------------------------------------------------
            | Update User
            |--------------------------------------------------------------------------
            */

            $update = $conn->prepare(
                "UPDATE users
                 SET name = ?,
                     email = ?,
                     phone = ?,
                     address = ?
                 WHERE id = ?"
            );

            $update->bind_param(
                "ssssi",
                $name,
                $email,
                $phone,
                $address,
                $user_id
            );

            if ($update->execute()) {

                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_phone'] = $phone;

                $message = 'Profile updated successfully.';
                $message_type = 'success';

            } else {

                $message = 'Unable to update profile.';
                $message_type = 'error';
            }

            $update->close();
        }

        $check->close();
    }
}


/*
|--------------------------------------------------------------------------
| Get User Data
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT
        id,
        name,
        email,
        phone,
        address,
        created_at
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    session_destroy();

    header("Location: ../auth/user-login.php");

    exit;
}

$user = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        My Profile - Darjibhai
    </title>


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


    <!-- Darjibhai CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/dashboard.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/profile.css"
    >

</head>


<body>


<div class="dashboard-page">


    <div class="dashboard-card profile-card">


        <!-- Logo -->

        <div class="dashboard-logo">

           <strong>My Profile</strong>
        </div>


        <!-- Header -->

        <div class="profile-header">

            <div class="profile-avatar">

                <?php
                echo strtoupper(
                    substr($user['name'], 0, 1)
                );
                ?>

            </div>


            <div class="profile-header-info">

                <h1>

                    <?php
                    echo htmlspecialchars(
                        $user['name']
                    );
                    ?>

                </h1>

            </div>


            <!-- Profile Actions -->

            <a
                href="orders.php"
                class="profile-edit-btn"
            >

                <i class="fa-solid fa-shirt"></i>

                My Orders

            </a>

            <button
                type="button"
                class="profile-edit-btn"
                data-bs-toggle="modal"
                data-bs-target="#editProfileModal"
            >

                <i class="fa-solid fa-pen"></i>

                Edit Profile

            </button>

        </div>


        <!-- Alert -->

        <?php if ($message !== ''): ?>

            <div
                class="alert alert-<?php echo htmlspecialchars($message_type); ?>"
            >

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <!-- Profile Information -->

        <div class="profile-details">


            <!-- Name -->

            <div class="profile-info-card">

                <div class="profile-info-icon">

                    <i class="fa-solid fa-user"></i>

                </div>

                <div>

                    <small>
                        Full Name
                    </small>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $user['name']
                        );
                        ?>
                    </strong>

                </div>

            </div>


            <!-- Email -->

            <div class="profile-info-card">

                <div class="profile-info-icon">

                    <i class="fa-solid fa-envelope"></i>

                </div>

                <div>

                    <small>
                        Email Address
                    </small>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $user['email']
                        );
                        ?>
                    </strong>

                </div>

            </div>


            <!-- Phone -->

            <div class="profile-info-card">

                <div class="profile-info-icon">

                    <i class="fa-solid fa-phone"></i>

                </div>

                <div>

                    <small>
                        Phone Number
                    </small>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $user['phone']
                        );
                        ?>
                    </strong>

                </div>

            </div>


            <!-- Address -->

            <div class="profile-info-card">

                <div class="profile-info-icon">

                    <i class="fa-solid fa-location-dot"></i>

                </div>

                <div>

                    <small>
                        Address
                    </small>

                    <strong>

                        <?php

                        if (!empty($user['address'])) {

                            echo nl2br(
                                htmlspecialchars(
                                    $user['address']
                                )
                            );

                        } else {

                            echo 'Address not added';

                        }

                        ?>

                    </strong>

                </div>

            </div>


            <!-- Created Date -->

            <div class="profile-info-card">

                <div class="profile-info-icon">

                    <i class="fa-solid fa-calendar"></i>

                </div>

                <div>

                    <small>
                        Member Since
                    </small>

                    <strong>

                        <?php

                        echo date(
                            'd M Y',
                            strtotime(
                                $user['created_at']
                            )
                        );

                        ?>

                    </strong>

                </div>

            </div>


        </div>


        <!-- Bottom Actions -->

        <div class="profile-bottom-actions">

            <a
                href="../auth/user-logout.php"
                class="profile-logout-btn"
            >

                <i class="fa-solid fa-right-from-bracket"></i>

                Logout

            </a>

        </div>


    </div>

</div>



<!-- =====================================================
     EDIT PROFILE MODAL
===================================================== -->

<div
    class="modal fade"
    id="editProfileModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content profile-modal">


            <!-- Modal Header -->

            <div class="modal-header">

                <div>

                    <h5 class="modal-title">

                        <i class="fa-solid fa-user-pen"></i>

                        Edit Profile

                    </h5>

                    <small>
                        Update your personal information
                    </small>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <!-- Modal Body -->

            <form method="POST">

                <div class="modal-body">


                    <!-- Name -->

                    <div class="modal-form-group">

                        <label>

                            <i class="fa-solid fa-user"></i>

                            Full Name

                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="<?php
                                echo htmlspecialchars(
                                    $user['name']
                                );
                            ?>"
                            required
                        >

                    </div>


                    <!-- Email -->

                    <div class="modal-form-group">

                        <label>

                            <i class="fa-solid fa-envelope"></i>

                            Email Address

                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="<?php
                                echo htmlspecialchars(
                                    $user['email']
                                );
                            ?>"
                            required
                        >

                    </div>


                    <!-- Phone -->

                    <div class="modal-form-group">

                        <label>

                            <i class="fa-solid fa-phone"></i>

                            Phone Number

                        </label>

                        <input
                            type="tel"
                            name="phone"
                            class="form-control"
                            value="<?php
                                echo htmlspecialchars(
                                    $user['phone']
                                );
                            ?>"
                            required
                        >

                    </div>


                    <!-- Address -->

                    <div class="modal-form-group">

                        <label>

                            <i class="fa-solid fa-location-dot"></i>

                            Address

                        </label>

                        <textarea
                            name="address"
                            class="form-control"
                            rows="4"
                        ><?php
                            echo htmlspecialchars(
                                $user['address'] ?? ''
                            );
                        ?></textarea>

                    </div>


                </div>


                <!-- Modal Footer -->

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                    >

                        Cancel

                    </button>


                    <button
                        type="submit"
                        class="profile-save-btn"
                    >

                        <i class="fa-solid fa-check"></i>

                        Save Changes

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<?php if ($message !== '' && $message_type === 'error'): ?>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const modalElement =
        document.getElementById('editProfileModal');

    const modal =
        new bootstrap.Modal(modalElement);

    modal.show();

});

</script>

<?php endif; ?>


<script src="/darjibhai/assets/js/form-validation.js" defer></script>
</body>

</html>
