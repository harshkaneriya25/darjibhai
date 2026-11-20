<?php

require_once __DIR__ . '/../../includes/admin-auth.php';
require_once __DIR__ . '/../../config/database.php';

$page_title = 'Roles';
$admin_page = true;

$extra_js = '
<script src="/darjibhai/assets/js/admin/table.js"></script>
';

$successMessage = $_SESSION['role_success'] ?? '';
$errorMessage   = $_SESSION['role_error'] ?? '';

unset($_SESSION['role_success']);
unset($_SESSION['role_error']);

/*
|--------------------------------------------------------------------------
| HANDLE ADD / EDIT / DELETE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $action = $_POST['action'];

    /* ADD ROLE */
    if ($action === 'add') {

        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            $_SESSION['role_error'] = 'Please enter a role name.';
        } else {
            $insert = $conn->prepare("INSERT INTO roles (name) VALUES (?)");
            $insert->bind_param('s', $name);

            if ($insert->execute()) {
                $_SESSION['role_success'] = 'Role added successfully.';
            } else {
                $_SESSION['role_error'] = 'Something went wrong while adding the role.';
            }

            $insert->close();
        }
    }

    /* EDIT ROLE */
    elseif ($action === 'edit') {

        $id   = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($id <= 0) {
            $_SESSION['role_error'] = 'Invalid role.';
        } elseif ($name === '') {
            $_SESSION['role_error'] = 'Please enter a role name.';
        } else {
            $update = $conn->prepare("UPDATE roles SET name = ? WHERE id = ?");
            $update->bind_param('si', $name, $id);

            if ($update->execute()) {
                $_SESSION['role_success'] = 'Role updated successfully.';
            } else {
                $_SESSION['role_error'] = 'Something went wrong while updating the role.';
            }

            $update->close();
        }
    }

    /* DELETE ROLE */
    elseif ($action === 'delete') {

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['role_error'] = 'Invalid role.';
        } else {
            $delete = $conn->prepare("DELETE FROM roles WHERE id = ?");
            $delete->bind_param('i', $id);

            if ($delete->execute()) {
                $_SESSION['role_success'] = 'Role deleted successfully.';
            } else {
                $_SESSION['role_error'] = 'Something went wrong while deleting the role.';
            }

            $delete->close();
        }
    }

    header('Location: /darjibhai/admin/roles/index.php');
    exit;
}

/* GET ROLES */
$rolesResult = $conn->query(
    "SELECT
        roles.id,
        roles.name,
        roles.created_at
     FROM roles
     ORDER BY roles.created_at DESC"
);

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

?>

<div class="main-wrapper">

    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="admin-content">

        <div class="page-title-section">
            <h3>Roles</h3>
            <p><b>Dashboard</b> > Roles</p>
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
                        placeholder="Search roles..."
                        data-table-search="rolesTable"
                    >
                </div>

                <button
                    type="button"
                    class="btn btn-orange"
                    data-bs-toggle="modal"
                    data-bs-target="#addRoleModal"
                >
                    <i class="fa-solid fa-plus"></i>
                    Add Role
                </button>
            </div>

            <div class="table-responsive">
                <table class="table admin-table" id="rolesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Role Name</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php if ($rolesResult && $rolesResult->num_rows > 0): ?>
                            <?php while ($role = $rolesResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= (int) $role['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($role['name']) ?></strong>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars(date('d M Y, h:i A', strtotime($role['created_at']))) ?>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <button
                                                type="button"
                                                class="table-action-btn table-action-view"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewRoleModal"
                                                title="View"
                                                data-id="<?= (int) $role['id'] ?>"
                                                data-name="<?= htmlspecialchars($role['name']) ?>"
                                                data-created="<?= htmlspecialchars(date('d M Y, h:i A', strtotime($role['created_at']))) ?>"
                                            >
                                                <i class="fa-solid fa-eye"></i>
                                            </button>

                                            <button
                                                type="button"
                                                class="table-action-btn table-action-edit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editRoleModal"
                                                title="Edit"
                                                data-id="<?= (int) $role['id'] ?>"
                                                data-name="<?= htmlspecialchars($role['name']) ?>"
                                            >
                                                <i class="fa-solid fa-pen"></i>
                                            </button>

                                            <button
                                                type="button"
                                                class="table-action-btn table-action-delete"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteRoleModal"
                                                title="Delete"
                                                data-id="<?= (int) $role['id'] ?>"
                                                data-name="<?= htmlspecialchars($role['name']) ?>"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">
                                    <div class="table-empty">
                                        <i class="fa-solid fa-user-shield"></i>
                                        <p>No Roles Found</p>
                                        <span>There are currently no roles configured.</span>
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

<!-- ADD ROLE MODAL -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" autocomplete="off">
                <input type="hidden" name="action" value="add">

                <div class="modal-header">
                    <h5 class="modal-title">Add Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Role Name</label>
                        <input type="text" name="name" class="admin-form-control" placeholder="Enter role name" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-orange">Add Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- VIEW ROLE MODAL -->
<div class="modal fade" id="viewRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Role Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="admin-form-group">
                    <label class="admin-form-label">Role ID</label>
                    <p class="mb-0" id="viewRoleId"></p>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Role Name</label>
                    <p class="mb-0" id="viewRoleName"></p>
                </div>
                <div class="mb-0">
                    <label class="admin-form-label">Created At</label>
                    <p class="mb-0" id="viewRoleCreated"></p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- EDIT ROLE MODAL -->
<div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" autocomplete="off">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editRoleId" value="">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Role Name</label>
                        <input type="text" name="name" id="editRoleName" class="admin-form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-orange">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE ROLE MODAL -->
<div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteRoleId" value="">

                <div class="modal-header">
                    <h5 class="modal-title">Delete Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="delete-modal-icon"><i class="fa-solid fa-trash"></i></div>
                    <div class="delete-modal-content">
                        <h5>Delete Role</h5>
                        <p>Are you sure you want to delete <strong id="deleteRoleName"></strong>? This action cannot be undone.</p>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    var viewRoleModal = document.getElementById('viewRoleModal');
    if (viewRoleModal) {
        viewRoleModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            document.getElementById('viewRoleId').textContent = btn.getAttribute('data-id');
            document.getElementById('viewRoleName').textContent = btn.getAttribute('data-name');
            document.getElementById('viewRoleCreated').textContent = btn.getAttribute('data-created');
        });
    }

    var editRoleModal = document.getElementById('editRoleModal');
    if (editRoleModal) {
        editRoleModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            document.getElementById('editRoleId').value = btn.getAttribute('data-id');
            document.getElementById('editRoleName').value = btn.getAttribute('data-name');
        });
    }

    var deleteRoleModal = document.getElementById('deleteRoleModal');
    if (deleteRoleModal) {
        deleteRoleModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            document.getElementById('deleteRoleId').value = btn.getAttribute('data-id');
            document.getElementById('deleteRoleName').textContent = btn.getAttribute('data-name');
        });
    }
});
</script>

?>
<script src="/darjibhai/assets/js/form-validation.js" defer></script>
<?php include __DIR__ . '/../../includes/footer.php';

?>