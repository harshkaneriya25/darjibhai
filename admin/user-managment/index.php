<?php

require_once __DIR__ . '/../../includes/admin-auth.php';
require_once __DIR__ . '/../../config/database.php';

$page_title = 'Users';
$admin_page = true;

$extra_js = '
<script src="/darjibhai/assets/js/admin/table.js"></script>
';

$successMessage = $_SESSION['user_success'] ?? '';
$errorMessage   = $_SESSION['user_error'] ?? '';

unset($_SESSION['user_success']);
unset($_SESSION['user_error']);

/*
|--------------------------------------------------------------------------
| HANDLE ADD / EDIT / DELETE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $action = $_POST['action'];

    /*
    |--------------------------------------------------------------------------
    | ADD USER
    |--------------------------------------------------------------------------
    */

    if ($action === 'add') {

        $name             = trim($_POST['name'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $phone            = trim($_POST['phone'] ?? '');
        $role_id          = (int) ($_POST['role_id'] ?? 0);
        $status           = isset($_POST['status']) ? 1 : 0;
        $password         = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($name === '') {
            $_SESSION['user_error'] = 'Please enter a name.';
        } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['user_error'] = 'Please enter a valid email address.';
        } elseif ($phone === '') {
            $_SESSION['user_error'] = 'Please enter a phone number.';
        } elseif ($role_id <= 0) {
            $_SESSION['user_error'] = 'Please select a role.';
        } elseif ($password === '' || strlen($password) < 6) {
            $_SESSION['user_error'] = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm_password) {
            $_SESSION['user_error'] = 'Password and confirm password do not match.';
        } else {
            $check = $conn->prepare(
                "SELECT id
                 FROM admins
                 WHERE email = ? OR phone = ?
                 LIMIT 1"
            );

            $check->bind_param('ss', $email, $phone);
            $check->execute();
            $checkResult = $check->get_result();

            if ($checkResult->num_rows > 0) {
                $_SESSION['user_error'] = 'Email or phone number is already registered.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $insert = $conn->prepare(
                    "INSERT INTO admins
                    (
                        name,
                        email,
                        phone,
                        password,
                        actual_password,
                        role_id,
                        status
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)"
                );

                $insert->bind_param(
                    'sssssii',
                    $name,
                    $email,
                    $phone,
                    $hashedPassword,
                    $password,
                    $role_id,
                    $status
                );

                if ($insert->execute()) {
                    $_SESSION['user_success'] = 'User added successfully.';
                } else {
                    $_SESSION['user_error'] = 'Something went wrong while adding the user.';
                }

                $insert->close();
            }

            $check->close();
        }

        header('Location: http://localhost/darjibhai/admin/user-managment/index.php');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT USER
    |--------------------------------------------------------------------------
    */

    elseif ($action === 'edit') {

        $id               = (int) ($_POST['id'] ?? 0);
        $name             = trim($_POST['name'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $phone            = trim($_POST['phone'] ?? '');
        $role_id          = (int) ($_POST['role_id'] ?? 0);
        $status           = isset($_POST['status']) ? 1 : 0;
        $password         = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($id <= 0) {
            $_SESSION['user_error'] = 'Invalid user.';
        } elseif ($name === '') {
            $_SESSION['user_error'] = 'Please enter a name.';
        } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['user_error'] = 'Please enter a valid email address.';
        } elseif ($phone === '') {
            $_SESSION['user_error'] = 'Please enter a phone number.';
        } elseif ($role_id <= 0) {
            $_SESSION['user_error'] = 'Please select a role.';
        } elseif ($password !== '' && strlen($password) < 6) {
            $_SESSION['user_error'] = 'Password must be at least 6 characters.';
        } elseif ($password !== '' && $password !== $confirm_password) {
            $_SESSION['user_error'] = 'Password and confirm password do not match.';
        } else {
            $check = $conn->prepare(
                "SELECT id
                 FROM admins
                 WHERE (email = ? OR phone = ?)
                 AND id != ?
                 LIMIT 1"
            );

            $check->bind_param('ssi', $email, $phone, $id);
            $check->execute();
            $checkResult = $check->get_result();

            if ($checkResult->num_rows > 0) {
                $_SESSION['user_error'] = 'Email or phone number is already used by another user.';
            } else {
                if ($password !== '') {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    $update = $conn->prepare(
                        "UPDATE admins
                         SET
                            name = ?,
                            email = ?,
                            phone = ?,
                            role_id = ?,
                            status = ?,
                            password = ?,
                            actual_password = ?
                         WHERE id = ?"
                    );

                    $update->bind_param(
                        'sssiissi',
                        $name,
                        $email,
                        $phone,
                        $role_id,
                        $status,
                        $hashedPassword,
                        $password,
                        $id
                    );
                } else {
                    $update = $conn->prepare(
                        "UPDATE admins
                         SET
                            name = ?,
                            email = ?,
                            phone = ?,
                            role_id = ?,
                            status = ?
                         WHERE id = ?"
                    );

                    $update->bind_param(
                        'sssiii',
                        $name,
                        $email,
                        $phone,
                        $role_id,
                        $status,
                        $id
                    );
                }

                if ($update->execute()) {
                    $_SESSION['user_success'] = 'User updated successfully.';
                } else {
                    $_SESSION['user_error'] = 'Something went wrong while updating the user.';
                }

                $update->close();
            }

            $check->close();
        }

        header('Location: http://localhost/darjibhai/admin/user-managment/index.php');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE USER
    |--------------------------------------------------------------------------
    */

    elseif ($action === 'delete') {

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['user_error'] = 'Invalid user.';
        } else {
            $delete = $conn->prepare("DELETE FROM admins WHERE id = ?");
            $delete->bind_param('i', $id);

            if ($delete->execute()) {
                $_SESSION['user_success'] = 'User deleted successfully.';
            } else {
                $_SESSION['user_error'] = 'Something went wrong while deleting the user.';
            }

            $delete->close();
        }

        header('Location: http://localhost/darjibhai/admin/user-managment/index.php');
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| GET USERS
|--------------------------------------------------------------------------
*/

$usersResult = $conn->query(
    "SELECT
        admins.id,
        admins.name,
        admins.email,
        admins.phone,
        admins.status,
        admins.created_at,
        admins.role_id,
        roles.name AS role_name
     FROM admins
     LEFT JOIN roles ON roles.id = admins.role_id
     ORDER BY admins.created_at ASC"
);

$rolesResult = $conn->query(
    "SELECT id, name FROM roles ORDER BY name ASC"
);

$rolesList = $rolesResult ? $rolesResult->fetch_all(MYSQLI_ASSOC) : [];

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

?>

<div class="main-wrapper">

    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="admin-content">

        <div class="page-title-section">
            <h3>Users</h3>
            <p><b>Dashboard</b> > User</p>
        </div>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($successMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($errorMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="admin-table-card">

            <div class="table-header">
                <div class="table-search">
                    <i class="fa-solid fa-search"></i>
                    <input
                        type="text"
                        placeholder="Search users..."
                        data-table-search="usersTable"
                    >
                </div>

                <button
                    type="button"
                    class="btn btn-orange"
                    data-bs-toggle="modal"
                    data-bs-target="#addUserModal"
                >
                    <i class="fa-solid fa-plus"></i>
                    Add User
                </button>
            </div>

            <!-- TABLE -->
            <div class="table-responsive">
                <table class="table admin-table" id="usersTable">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Sr No. </th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($usersResult && $usersResult->num_rows > 0): ?>
                            <?php 
                            $srNo = 1; // Auto-incrementing row counter
                            while ($user = $usersResult->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?= $srNo++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($user['name']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($user['role_name'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($user['status']): ?>
                                            <span class="status-badge status-active">Active</span>
                                        <?php else: ?>
                                            <span class="status-badge status-inactive">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <!-- VIEW -->
                                            <button
                                                type="button"
                                                class="table-action-btn table-action-view"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewUserModal"
                                                title="View"
                                                data-id="<?= (int) $user['id'] ?>"
                                                data-name="<?= htmlspecialchars($user['name']) ?>"
                                                data-email="<?= htmlspecialchars($user['email']) ?>"
                                                data-phone="<?= htmlspecialchars($user['phone'] ?? '-') ?>"
                                                data-role-name="<?= htmlspecialchars($user['role_name'] ?? '-') ?>"
                                                data-status="<?= $user['status'] ? 'Active' : 'Inactive' ?>"
                                            >
                                                <i class="fa-solid fa-eye"></i>
                                            </button>

                                            <!-- EDIT -->
                                            <button
                                                type="button"
                                                class="table-action-btn table-action-edit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editUserModal"
                                                title="Edit"
                                                data-id="<?= (int) $user['id'] ?>"
                                                data-name="<?= htmlspecialchars($user['name']) ?>"
                                                data-email="<?= htmlspecialchars($user['email']) ?>"
                                                data-phone="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                                data-role-id="<?= (int) ($user['role_id'] ?? 0) ?>"
                                                data-status="<?= (int) $user['status'] ?>"
                                            >
                                                <i class="fa-solid fa-pen"></i>
                                            </button>

                                            <!-- DELETE -->
                                            <button
                                                type="button"
                                                class="table-action-btn table-action-delete"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteUserModal"
                                                title="Delete"
                                                data-id="<?= (int) $user['id'] ?>"
                                                data-name="<?= htmlspecialchars($user['name']) ?>"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">
                                    <div class="table-empty">
                                        <i class="fa-solid fa-users"></i>
                                        <h6>No Users Found</h6>
                                        <p>There are no admin users available.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </main>

</div>

<!-- ADD USER MODAL -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" autocomplete="off">
                <input type="hidden" name="action" value="add">

                <div class="modal-header">
                    <h5 class="modal-title">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Name</label>
                        <input
                            type="text"
                            name="name"
                            class="admin-form-control"
                            placeholder="Enter full name"
                            required
                        >
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Email</label>
                        <input
                            type="email"
                            name="email"
                            class="admin-form-control"
                            placeholder="Enter email address"
                            required
                        >
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Phone</label>
                        <input
                            type="text"
                            name="phone"
                            class="admin-form-control"
                            placeholder="Enter phone number"
                            required
                        >
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Password</label>
                        <input
                            type="password"
                            name="password"
                            class="admin-form-control"
                            placeholder="Minimum 6 characters"
                            required
                        >
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Confirm Password</label>
                        <input
                            type="password"
                            name="confirm_password"
                            class="admin-form-control"
                            placeholder="Confirm password"
                            required
                        >
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Role</label>
                        <select
                            name="role_id"
                            class="admin-form-control"
                            required
                        >
                            <option value="">Select role</option>
                            <?php foreach ($rolesList as $role): ?>
                                <option value="<?= (int) $role['id'] ?>">
                                    <?= htmlspecialchars($role['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-check form-switch">
                        <input
                            type="checkbox"
                            name="status"
                            class="form-check-input"
                            id="addUserStatus"
                            checked
                        >
                        <label class="form-check-label" for="addUserStatus">
                            Active
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-orange">
                        Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- VIEW USER MODAL -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="admin-form-group">
                    <label class="admin-form-label">Name</label>
                    <p class="mb-0" id="viewUserName"></p>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Email</label>
                    <p class="mb-0" id="viewUserEmail"></p>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Phone</label>
                    <p class="mb-0" id="viewUserPhone"></p>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Role</label>
                    <p class="mb-0" id="viewUserRole"></p>
                </div>

                <div class="mb-0">
                    <label class="admin-form-label">Status</label>
                    <p class="mb-0" id="viewUserStatus"></p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- EDIT USER MODAL -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" autocomplete="off">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editUserId" value="">

                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Name</label>
                        <input
                            type="text"
                            name="name"
                            id="editUserName"
                            class="admin-form-control"
                            required
                        >
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Email</label>
                        <input
                            type="email"
                            name="email"
                            id="editUserEmail"
                            class="admin-form-control"
                            required
                        >
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Phone</label>
                        <input
                            type="text"
                            name="phone"
                            id="editUserPhone"
                            class="admin-form-control"
                            required
                        >
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Role</label>
                        <select
                            name="role_id"
                            id="editUserRole"
                            class="admin-form-control"
                            required
                        >
                            <option value="">Select role</option>
                            <?php foreach ($rolesList as $role): ?>
                                <option value="<?= (int) $role['id'] ?>">
                                    <?= htmlspecialchars($role['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">
                            New Password
                            <small class="text-muted">(leave blank to keep current password)</small>
                        </label>
                        <input
                            type="password"
                            name="password"
                            class="admin-form-control"
                            placeholder="Minimum 6 characters"
                        >
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Confirm New Password</label>
                        <input
                            type="password"
                            name="confirm_password"
                            class="admin-form-control"
                            placeholder="Confirm new password"
                        >
                    </div>

                    <div class="form-check form-switch">
                        <input
                            type="checkbox"
                            name="status"
                            class="form-check-input"
                            id="editUserStatus"
                        >
                        <label class="form-check-label" for="editUserStatus">
                            Active
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-orange">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE USER MODAL -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteUserId" value="">

                <div class="modal-header">
                    <h5 class="modal-title">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="delete-modal-icon">
                        <i class="fa-solid fa-trash"></i>
                    </div>

                    <div class="delete-modal-content">
                        <h5>Delete User</h5>
                        <p>
                            Are you sure you want to delete
                            <strong id="deleteUserName"></strong>?
                            This action cannot be undone.
                        </p>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* VIEW MODAL */
    var viewUserModal = document.getElementById('viewUserModal');
    if (viewUserModal) {
        viewUserModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            document.getElementById('viewUserName').textContent   = btn.getAttribute('data-name');
            document.getElementById('viewUserEmail').textContent  = btn.getAttribute('data-email');
            document.getElementById('viewUserPhone').textContent  = btn.getAttribute('data-phone');
            document.getElementById('viewUserRole').textContent   = btn.getAttribute('data-role-name');
            document.getElementById('viewUserStatus').textContent = btn.getAttribute('data-status');
        });
    }

    /* EDIT MODAL */
    var editUserModal = document.getElementById('editUserModal');
    if (editUserModal) {
        editUserModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            document.getElementById('editUserId').value    = btn.getAttribute('data-id');
            document.getElementById('editUserName').value  = btn.getAttribute('data-name');
            document.getElementById('editUserEmail').value = btn.getAttribute('data-email');
            document.getElementById('editUserPhone').value = btn.getAttribute('data-phone');
            document.getElementById('editUserRole').value  = btn.getAttribute('data-role-id');
            document.getElementById('editUserStatus').checked =
                btn.getAttribute('data-status') === '1';
        });
    }

    /* DELETE MODAL */
    var deleteUserModal = document.getElementById('deleteUserModal');
    if (deleteUserModal) {
        deleteUserModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            document.getElementById('deleteUserId').value   = btn.getAttribute('data-id');
            document.getElementById('deleteUserName').textContent = btn.getAttribute('data-name');
        });
    }
});
</script>

?>
<script src="/darjibhai/assets/js/form-validation.js" defer></script>
<?php include __DIR__ . '/../../includes/footer.php';

?>