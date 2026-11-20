<?php

require_once __DIR__ . '/../../includes/admin-auth.php';
require_once __DIR__ . '/../../config/database.php';

$page_title = 'Job Descriptions';
$admin_page = true;

$extra_js = '
<script src="/darjibhai/assets/js/admin/table.js"></script>
';

$successMessage = $_SESSION['job_description_success'] ?? '';
$errorMessage   = $_SESSION['job_description_error'] ?? '';

unset($_SESSION['job_description_success']);
unset($_SESSION['job_description_error']);

/*
|--------------------------------------------------------------------------
| HANDLE ADD / EDIT / DELETE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $action = $_POST['action'];

    /* ADD JOB DESCRIPTION */
    if ($action === 'add') {

        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status      = isset($_POST['status']) ? 1 : 0;

        if ($name === '') {

            $_SESSION['job_description_error'] =
                'Please enter a job description name.';

        } else {

            $insert = $conn->prepare(
                "INSERT INTO job_descriptions
                (
                    name,
                    description,
                    status
                )
                VALUES (?, ?, ?)"
            );

            $insert->bind_param('ssi', $name, $description, $status);

            if ($insert->execute()) {

                $_SESSION['job_description_success'] =
                    'Job description added successfully.';

            } else {

                $_SESSION['job_description_error'] =
                    'Something went wrong while adding the job description.';
            }

            $insert->close();
        }

        header('Location: http://localhost/darjibhai/admin/job-description/index.php');
        exit;
    }

    /* EDIT JOB DESCRIPTION */
    elseif ($action === 'edit') {

        $id          = (int) ($_POST['id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status      = isset($_POST['status']) ? 1 : 0;

        if ($id <= 0) {

            $_SESSION['job_description_error'] = 'Invalid job description.';

        } elseif ($name === '') {

            $_SESSION['job_description_error'] =
                'Please enter a job description name.';

        } else {

            $update = $conn->prepare(
                "UPDATE job_descriptions
                 SET
                    name = ?,
                    description = ?,
                    status = ?
                 WHERE id = ?"
            );

            $update->bind_param('ssii', $name, $description, $status, $id);

            if ($update->execute()) {

                $_SESSION['job_description_success'] =
                    'Job description updated successfully.';

            } else {

                $_SESSION['job_description_error'] =
                    'Something went wrong while updating the job description.';
            }

            $update->close();
        }

        header('Location: http://localhost/darjibhai/admin/job-description/index.php');
        exit;
    }

    /* DELETE JOB DESCRIPTION */
    elseif ($action === 'delete') {

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {

            $_SESSION['job_description_error'] = 'Invalid job description.';

        } else {

            $delete = $conn->prepare(
                "DELETE FROM job_descriptions
                 WHERE id = ?"
            );

            $delete->bind_param('i', $id);

            if ($delete->execute()) {

                $_SESSION['job_description_success'] =
                    'Job description deleted successfully.';

            } else {

                $_SESSION['job_description_error'] =
                    'Something went wrong while deleting the job description.';
            }

            $delete->close();
        }

        header('Location: http://localhost/darjibhai/admin/job-description/index.php');
        exit;
    }
}

/* GET JOB DESCRIPTIONS (ASC ORDER) */
$jobDescriptionsResult = $conn->query(
    "SELECT
        job_descriptions.id,
        job_descriptions.name,
        job_descriptions.description,
        job_descriptions.status,
        job_descriptions.created_at
     FROM job_descriptions
     ORDER BY job_descriptions.created_at ASC"
);

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

?>

<style>
/* Equal spacing & structured columns */
#jobDescriptionsTable {
    table-layout: fixed;
    width: 100%;
}

#jobDescriptionsTable th,
#jobDescriptionsTable td {
    vertical-align: middle;
    padding: 14px 16px;
    word-wrap: break-word;
}

#jobDescriptionsTable th.col-sr,
#jobDescriptionsTable td.col-sr {
    width: 10%;
    text-align: center;
}

#jobDescriptionsTable th.col-name,
#jobDescriptionsTable td.col-name {
    width: 45%;
}

#jobDescriptionsTable th.col-status,
#jobDescriptionsTable td.col-status {
    width: 25%;
}

#jobDescriptionsTable th.col-action,
#jobDescriptionsTable td.col-action {
    width: 20%;
    text-align: center;
}

#jobDescriptionsTable .table-actions {
    justify-content: center;
}

</style>

<div class="main-wrapper">

    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="admin-content">

        <div class="page-title-section">
            <h3>Job Descriptions</h3>
            <p><b>Dashboard</b> > Job Descriptions</p>
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
                        placeholder="Search job descriptions..."
                        data-table-search="jobDescriptionsTable"
                    >
                </div>

                <button
                    type="button"
                    class="btn btn-orange"
                    data-bs-toggle="modal"
                    data-bs-target="#addJobDescriptionModal"
                >
                    <i class="fa-solid fa-plus"></i>
                    Add Job Description
                </button>
            </div>

            <div class="table-responsive">
                <table class="table admin-table" id="jobDescriptionsTable">
                    <thead>
                        <tr>
                            <th class="col-sr">Sr No.</th>
                            <th class="col-name">Name</th>
                            <th class="col-status">Status</th>
                            <th class="col-action">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($jobDescriptionsResult && $jobDescriptionsResult->num_rows > 0): ?>
                            <?php 
                            $srNo = 1; 
                            while ($jobDescription = $jobDescriptionsResult->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td class="col-sr"><?= $srNo++ ?></td>

                                    <td class="col-name">
                                        <strong><?= htmlspecialchars($jobDescription['name']) ?></strong>
                                    </td>

                                    <td class="col-status">
                                        <?php if ($jobDescription['status']): ?>
                                            <span class="status-badge status-active">Active</span>
                                        <?php else: ?>
                                            <span class="status-badge status-inactive">Inactive</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="col-action">
                                        <div class="table-actions">
                                            <button
                                                type="button"
                                                class="table-action-btn table-action-view"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewJobDescriptionModal"
                                                title="View"
                                                data-id="<?= (int) $jobDescription['id'] ?>"
                                                data-name="<?= htmlspecialchars($jobDescription['name']) ?>"
                                                data-description="<?= htmlspecialchars($jobDescription['description'] ?? '-') ?>"
                                                data-status="<?= $jobDescription['status'] ? 'Active' : 'Inactive' ?>"
                                            >
                                                <i class="fa-solid fa-eye"></i>
                                            </button>

                                            <button
                                                type="button"
                                                class="table-action-btn table-action-edit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editJobDescriptionModal"
                                                title="Edit"
                                                data-id="<?= (int) $jobDescription['id'] ?>"
                                                data-name="<?= htmlspecialchars($jobDescription['name']) ?>"
                                                data-description="<?= htmlspecialchars($jobDescription['description'] ?? '') ?>"
                                                data-status="<?= (int) $jobDescription['status'] ?>"
                                            >
                                                <i class="fa-solid fa-pen"></i>
                                            </button>

                                            <button
                                                type="button"
                                                class="table-action-btn table-action-delete"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteJobDescriptionModal"
                                                title="Delete"
                                                data-id="<?= (int) $jobDescription['id'] ?>"
                                                data-name="<?= htmlspecialchars($jobDescription['name']) ?>"
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
                                        <i class="fa-solid fa-briefcase"></i>
                                        <p>No Job Descriptions Found</p>
                                        <span>There are currently no job descriptions available.</span>
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

<!-- ADD JOB DESCRIPTION MODAL -->
<div class="modal fade" id="addJobDescriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" autocomplete="off">
                <input type="hidden" name="action" value="add">

                <div class="modal-header">
                    <h5 class="modal-title">Add Job Description</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Name</label>
                        <input type="text" name="name" class="admin-form-control" placeholder="Enter job description name" required>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Description</label>
                        <textarea name="description" class="admin-form-control" placeholder="Enter job description" rows="4"></textarea>
                    </div>

                    <div class="form-check form-switch">
                        <input type="checkbox" name="status" class="form-check-input" id="addJobDescriptionStatus" checked>
                        <label class="form-check-label" for="addJobDescriptionStatus">Active</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-orange">Add Job Description</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- VIEW JOB DESCRIPTION MODAL -->
<div class="modal fade" id="viewJobDescriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Job Description Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="admin-form-group">
                    <label class="admin-form-label">Name</label>
                    <p class="mb-0" id="viewJobDescriptionName"></p>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Description</label>
                    <p class="mb-0" id="viewJobDescriptionDescription"></p>
                </div>
                <div class="mb-0">
                    <label class="admin-form-label">Status</label>
                    <p class="mb-0" id="viewJobDescriptionStatus"></p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- EDIT JOB DESCRIPTION MODAL -->
<div class="modal fade" id="editJobDescriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" autocomplete="off">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editJobDescriptionId" value="">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Job Description</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Name</label>
                        <input type="text" name="name" id="editJobDescriptionName" class="admin-form-control" required>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Description</label>
                        <textarea name="description" id="editJobDescriptionDescription" class="admin-form-control" rows="4"></textarea>
                    </div>

                    <div class="form-check form-switch">
                        <input type="checkbox" name="status" class="form-check-input" id="editJobDescriptionStatus">
                        <label class="form-check-label" for="editJobDescriptionStatus">Active</label>
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

<!-- DELETE JOB DESCRIPTION MODAL -->
<div class="modal fade" id="deleteJobDescriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteJobDescriptionId" value="">

                <div class="modal-header">
                    <h5 class="modal-title">Delete Job Description</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="delete-modal-icon"><i class="fa-solid fa-trash"></i></div>
                    <div class="delete-modal-content">
                        <h5>Delete Job Description</h5>
                        <p>Are you sure you want to delete <strong id="deleteJobDescriptionName"></strong>? This action cannot be undone.</p>
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

    var viewJobDescriptionModal = document.getElementById('viewJobDescriptionModal');
    if (viewJobDescriptionModal) {
        viewJobDescriptionModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            document.getElementById('viewJobDescriptionName').textContent = btn.getAttribute('data-name');
            document.getElementById('viewJobDescriptionDescription').textContent = btn.getAttribute('data-description');
            document.getElementById('viewJobDescriptionStatus').textContent = btn.getAttribute('data-status');
        });
    }

    var editJobDescriptionModal = document.getElementById('editJobDescriptionModal');
    if (editJobDescriptionModal) {
        editJobDescriptionModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            document.getElementById('editJobDescriptionId').value = btn.getAttribute('data-id');
            document.getElementById('editJobDescriptionName').value = btn.getAttribute('data-name');
            document.getElementById('editJobDescriptionDescription').value = btn.getAttribute('data-description');
            document.getElementById('editJobDescriptionStatus').checked =
                btn.getAttribute('data-status') === '1';
        });
    }

    var deleteJobDescriptionModal = document.getElementById('deleteJobDescriptionModal');
    if (deleteJobDescriptionModal) {
        deleteJobDescriptionModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            document.getElementById('deleteJobDescriptionId').value = btn.getAttribute('data-id');
            document.getElementById('deleteJobDescriptionName').textContent = btn.getAttribute('data-name');
        });
    }
});
</script>

?>
<script src="/darjibhai/assets/js/form-validation.js" defer></script>
<?php include __DIR__ . '/../../includes/footer.php';

?>
