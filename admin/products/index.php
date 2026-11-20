<?php

require_once __DIR__ . '/../../includes/admin-auth.php';
require_once __DIR__ . '/../../config/database.php';

$page_title = 'Products';
$admin_page = true;
$extra_css = '<style>
    #productsTable { min-width: 640px; table-layout: fixed; }
    #productsTable th:nth-child(1), #productsTable td:nth-child(1) { width: 10%; }
    #productsTable th:nth-child(2), #productsTable td:nth-child(2) { width: 35%; }
    #productsTable th:nth-child(3), #productsTable td:nth-child(3) { width: 20%; text-align: center; }
    #productsTable th:nth-child(4), #productsTable td:nth-child(4) { width: 35%; text-align: center; }
    #productsTable tbody td:nth-child(2) { overflow-wrap: anywhere; }
    #productsTable tbody td:nth-child(3) .status-badge { display: flex; width: max-content; margin: 0 auto; }
    #productsTable .table-actions { justify-content: center; flex-wrap: nowrap; gap: 8px; }
</style>';

$extra_js = '
<script src="/darjibhai/assets/js/admin/table.js"></script>
';

$successMessage = $_SESSION['product_success'] ?? '';
$errorMessage   = $_SESSION['product_error'] ?? '';

unset($_SESSION['product_success']);
unset($_SESSION['product_error']);

/*
|--------------------------------------------------------------------------
| HANDLE ADD / EDIT / DELETE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $action = $_POST['action'];

    /*
    |--------------------------------------------------------------------------
    | ADD PRODUCT
    |--------------------------------------------------------------------------
    */

    if ($action === 'add') {

        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status      = isset($_POST['status']) ? 1 : 0;

        if ($name === '') {

            $_SESSION['product_error'] = 'Please enter a product name.';

        } else {

            $insert = $conn->prepare(
                "INSERT INTO products
                (
                    name,
                    description,
                    status
                )
                VALUES (?, ?, ?)"
            );

            $insert->bind_param(
                'ssi',
                $name,
                $description,
                $status
            );

            if ($insert->execute()) {

                $_SESSION['product_success'] =
                    'Product added successfully.';

            } else {

                $_SESSION['product_error'] =
                    'Something went wrong while adding the product.';
            }

            $insert->close();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT PRODUCT
    |--------------------------------------------------------------------------
    */

    elseif ($action === 'edit') {

        $id          = (int) ($_POST['id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status      = isset($_POST['status']) ? 1 : 0;

        if ($id <= 0) {

            $_SESSION['product_error'] = 'Invalid product.';

        } elseif ($name === '') {

            $_SESSION['product_error'] = 'Please enter a product name.';

        } else {

            $update = $conn->prepare(
                "UPDATE products
                 SET
                    name = ?,
                    description = ?,
                    status = ?
                 WHERE id = ?"
            );

            $update->bind_param(
                'ssii',
                $name,
                $description,
                $status,
                $id
            );

            if ($update->execute()) {

                $_SESSION['product_success'] =
                    'Product updated successfully.';

            } else {

                $_SESSION['product_error'] =
                    'Something went wrong while updating the product.';
            }

            $update->close();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE PRODUCT
    |--------------------------------------------------------------------------
    */

    elseif ($action === 'delete') {

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {

            $_SESSION['product_error'] = 'Invalid product.';

        } else {

            $delete = $conn->prepare(
                "DELETE FROM products
                 WHERE id = ?"
            );

            $delete->bind_param('i', $id);

            if ($delete->execute()) {

                $_SESSION['product_success'] =
                    'Product deleted successfully.';

            } else {

                $_SESSION['product_error'] =
                    'Something went wrong while deleting the product.';
            }

            $delete->close();
        }
    }

    header('Location: /darjibhai/admin/products/index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| GET PRODUCTS
|--------------------------------------------------------------------------
*/

$productsResult = $conn->query(
    "SELECT
        products.id,
        products.name,
        products.description,
        products.status,
        products.created_at
     FROM products
     ORDER BY products.created_at DESC"
);

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

?>


<div class="main-wrapper">

    <?php include __DIR__ . '/../../includes/navbar.php'; ?>


    <main class="admin-content">


        <div class="page-title-section">

            <h3>
                Products
            </h3>

            <p>
                <b>Dashboard</b> > Product
            </p>

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
                        placeholder="Search products..."
                        data-table-search="productsTable"
                    >

                </div>


                <button
                    type="button"
                    class="btn btn-orange"
                    data-bs-toggle="modal"
                    data-bs-target="#addProductModal"
                >

                    <i class="fa-solid fa-plus"></i>

                    Add Product

                </button>

            </div>


            <!-- TABLE -->

            <div class="table-responsive">

                <table
                    class="table admin-table"
                    id="productsTable"
                >

                    <thead>

                        <tr>

                            <th>
                                Sr No.
                            </th>

                            <th>
                                Name
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (
                            $productsResult &&
                            $productsResult->num_rows > 0
                        ): ?>

                            <?php 
                            $srNo = 1; 
                            while ($product = $productsResult->fetch_assoc()): 
                            ?>

                                <tr>

                                    <td>

                                        <?= $srNo++ ?>

                                    </td>


                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $product['name']
                                            ) ?>

                                        </strong>

                                    </td>


                                    <td>
                                        <?php if (
                                            $product['status']
                                        ): ?>
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
                                                data-bs-target="#viewProductModal"
                                                title="View"
                                                data-id="<?= (int) $product['id'] ?>"
                                                data-name="<?= htmlspecialchars($product['name']) ?>"
                                                data-description="<?= htmlspecialchars($product['description'] ?? '-') ?>"
                                                data-status="<?= $product['status'] ? 'Active' : 'Inactive' ?>"
                                            >

                                                <i
                                                    class="fa-solid fa-eye"
                                                ></i>

                                            </button>


                                            <!-- EDIT -->

                                            <button
                                                type="button"
                                                class="table-action-btn table-action-edit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editProductModal"
                                                title="Edit"
                                                data-id="<?= (int) $product['id'] ?>"
                                                data-name="<?= htmlspecialchars($product['name']) ?>"
                                                data-description="<?= htmlspecialchars($product['description'] ?? '') ?>"
                                                data-status="<?= (int) $product['status'] ?>"
                                            >

                                                <i
                                                    class="fa-solid fa-pen"
                                                ></i>

                                            </button>


                                            <!-- DELETE -->

                                            <button
                                                type="button"
                                                class="table-action-btn table-action-delete"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteProductModal"
                                                title="Delete"
                                                data-id="<?= (int) $product['id'] ?>"
                                                data-name="<?= htmlspecialchars($product['name']) ?>"
                                            >

                                                <i
                                                    class="fa-solid fa-trash"
                                                ></i>

                                            </button>


                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>


                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="4"
                                >

                                    <div class="table-empty">

                                        <i
                                            class="fa-solid fa-box"
                                        ></i>

                                        <p>
                                            No Products Found
                                        </p>

                                        <span>
                                            There are currently no
                                            products available.
                                        </span>

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


<!-- =========================================================
     ADD PRODUCT MODAL
========================================================== -->

<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST" autocomplete="off">

                <input type="hidden" name="action" value="add">

                <div class="modal-header">

                    <h5 class="modal-title">
                        Add Product
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body">

                    <div class="admin-form-group">

                        <label class="admin-form-label">Name</label>

                        <input
                            type="text"
                            name="name"
                            class="admin-form-control"
                            placeholder="Enter product name"
                            required
                        >

                    </div>

                    <div class="admin-form-group">

                        <label class="admin-form-label">Description</label>

                        <textarea
                            name="description"
                            class="admin-form-control"
                            placeholder="Enter product description"
                            rows="4"
                        ></textarea>

                    </div>

                    <div class="form-check form-switch">

                        <input
                            type="checkbox"
                            name="status"
                            class="form-check-input"
                            id="addProductStatus"
                            checked
                        >

                        <label class="form-check-label" for="addProductStatus">
                            Active
                        </label>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-orange">
                        Add Product
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- =========================================================
     VIEW PRODUCT MODAL
========================================================== -->

<div class="modal fade" id="viewProductModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Product Details
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <div class="admin-form-group">
                    <label class="admin-form-label">Name</label>
                    <p class="mb-0" id="viewProductName"></p>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Description</label>
                    <p class="mb-0" id="viewProductDescription"></p>
                </div>

                <div class="mb-0">
                    <label class="admin-form-label">Status</label>
                    <p class="mb-0" id="viewProductStatus"></p>
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


<!-- =========================================================
     EDIT PRODUCT MODAL
========================================================== -->

<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST" autocomplete="off">

                <input type="hidden" name="action" value="edit">

                <input type="hidden" name="id" id="editProductId" value="">

                <div class="modal-header">

                    <h5 class="modal-title">
                        Edit Product
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body">

                    <div class="admin-form-group">

                        <label class="admin-form-label">Name</label>

                        <input
                            type="text"
                            name="name"
                            id="editProductName"
                            class="admin-form-control"
                            required
                        >

                    </div>

                    <div class="admin-form-group">

                        <label class="admin-form-label">Description</label>

                        <textarea
                            name="description"
                            id="editProductDescription"
                            class="admin-form-control"
                            rows="4"
                        ></textarea>

                    </div>

                    <div class="form-check form-switch">

                        <input
                            type="checkbox"
                            name="status"
                            class="form-check-input"
                            id="editProductStatus"
                        >

                        <label class="form-check-label" for="editProductStatus">
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


<!-- =========================================================
     DELETE PRODUCT MODAL
========================================================== -->

<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST">

                <input type="hidden" name="action" value="delete">

                <input type="hidden" name="id" id="deleteProductId" value="">

                <div class="modal-header">

                    <h5 class="modal-title">
                        Delete Product
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body">

                    <div class="delete-modal-icon">
                        <i class="fa-solid fa-trash"></i>
                    </div>

                    <div class="delete-modal-content">

                        <h5>
                            Delete Product
                        </h5>

                        <p>
                            Are you sure you want to delete
                            <strong id="deleteProductName"></strong>?
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

    var viewProductModal = document.getElementById('viewProductModal');

    if (viewProductModal) {

        viewProductModal.addEventListener('show.bs.modal', function (event) {

            var btn = event.relatedTarget;

            document.getElementById('viewProductName').textContent        = btn.getAttribute('data-name');
            document.getElementById('viewProductDescription').textContent = btn.getAttribute('data-description');
            document.getElementById('viewProductStatus').textContent      = btn.getAttribute('data-status');

        });

    }


    /* EDIT MODAL */

    var editProductModal = document.getElementById('editProductModal');

    if (editProductModal) {

        editProductModal.addEventListener('show.bs.modal', function (event) {

            var btn = event.relatedTarget;

            document.getElementById('editProductId').value          = btn.getAttribute('data-id');
            document.getElementById('editProductName').value        = btn.getAttribute('data-name');
            document.getElementById('editProductDescription').value = btn.getAttribute('data-description');

            document.getElementById('editProductStatus').checked =
                btn.getAttribute('data-status') === '1';

        });

    }


    /* DELETE MODAL */

    var deleteProductModal = document.getElementById('deleteProductModal');

    if (deleteProductModal) {

        deleteProductModal.addEventListener('show.bs.modal', function (event) {

            var btn = event.relatedTarget;

            document.getElementById('deleteProductId').value   = btn.getAttribute('data-id');
            document.getElementById('deleteProductName').textContent = btn.getAttribute('data-name');

        });

    }

});
</script>


?>
<script src="/darjibhai/assets/js/form-validation.js" defer></script>
<?php include __DIR__ . '/../../includes/footer.php';

?>
