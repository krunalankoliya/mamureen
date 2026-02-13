<?php
require_once( __DIR__. '/../session.php');

// $admin_its = array('50476733', '40444857', '20372359', '30419712');

// if(!in_array($user_its, $admin_its)) {
//     header('location:index.php');
// }

$current_page = 'manage_google_forms';
require_once( __DIR__. '/../inc/header.php');

if (isset($_POST['update'])) {
    $form_title = mysqli_real_escape_string($mysqli, $_POST['form_title']);
    $form_link = mysqli_real_escape_string($mysqli, $_POST['form_link']);
    $id = $_POST['id'];

    $query = "UPDATE `google_forms` SET `form_title` = '$form_title', `form_link` = '$form_link' WHERE `id` = '$id'";
    try {
        $result = mysqli_query($mysqli, $query);
        $message['text'] = "Form Updated Successfully";
        $message['tag'] = 'success';
    } catch (Exception $e) {
        $message = array('text' => $e->getMessage(), 'tag' => 'danger');
    }
}

if (isset($_POST['delete'])) {
    $id = $_POST['id'];

    $deleteQuery = "DELETE FROM google_forms WHERE `id` = '$id'";
    try {
        $result = mysqli_query($mysqli, $deleteQuery);
        $message['text'] = "Form Deleted Successfully";
        $message['tag'] = 'success';
    } catch (Exception $e) {
        $message = array('text' => $e->getMessage(), 'tag' => 'danger');
    }
}

if (isset($_POST['submit'])) {
    $form_title = mysqli_real_escape_string($mysqli, $_POST['form_title']);
    $form_link = mysqli_real_escape_string($mysqli, $_POST['form_link']);
    $upload_date = date('Y-m-d');

    $query = "INSERT INTO `google_forms` (`upload_date`, `form_title`, `form_link`, `added_its`) VALUES ('$upload_date', '$form_title', '$form_link', '$user_its')";

    try {
        $result = mysqli_query($mysqli, $query);
        $message['text'] = "Form Added Successfully";
        $message['tag'] = 'success';
    } catch (Exception $e) {
        $message = array('text' => $e->getMessage(), 'tag' => 'danger');
    }
}

$query = "SELECT * FROM `google_forms` ORDER BY id DESC";
$result = mysqli_query($mysqli, $query);
$rows = $result->fetch_all(MYSQLI_ASSOC);

?>

<main id="main" class="main">

    <section class="section dashboard">
        <div class="row">
            <?php require_once(__DIR__ . '/../inc/messages.php'); ?>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">

                            <div class="card">
                                <div class="card-body pt-4">
                                    <h5 class="card-title">Add Feedback Forms</h5>
                                    <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
                                    <ul>
                                        <li>All fields marked with <span class="required_star">*</span> are required.</li>
                                        <li>For the form link, paste the complete Google Form iframe code.</li>
                                        <li>In the generated link, replace the actual values with these placeholders:</li>
                                       <li>- Replace the ITS ID value with `ITS_ID` (keep in capital letters)</li>
                                       <li>- Replace the Name value with `NAME` (keep in capital letters)</li>
                                       <li>- Replace the Mauze value with `MAUZE` (keep in capital letters)</li>
                                    </ul>
                                    <!-- Horizontal Form -->
                                    <form method="post" enctype="multipart/form-data">
                                        <div class="row mb-3">
                                            <label for="form_title" class="col-sm-4 col-form-label">Form Title<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="form_title" name='form_title' placeholder="Form Title" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="form_link" class="col-sm-4 col-form-label">Form link<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <textarea class="form-control" name="form_link" id="form_link" rows="5" placeholder="Paste Google Form link here" required></textarea>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-8"></div>
                                            <div class="col-md-2">
                                                <div class="text-center">
                                                    <div class="d-grid gap-2">
                                                        <input type="reset" value="Reset Form" name="reset" class="btn btn-outline-secondary" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="text-center">
                                                    <div class="d-grid gap-2">
                                                        <input type="submit" value="Submit" name="submit" class="btn btn-primary" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form><!-- End Horizontal Form -->

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title">Manage Feedback Forms</h5>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Upload Date</th>
                                        <th scope="col">Form Title</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (count($rows) > 0) {
                                        foreach ($rows as $data) {
                                    ?>
                                            <tr>
                                                <th scope="row"><?= $data['id'] ?></th>
                                                <td><?= $data['upload_date'] ?></td>
                                                <td><?= $data['form_title'] ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $data['id'] ?>">
                                                        Edit
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $data['id'] ?>">
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editModal<?= $data['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <form method="post">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Form: <?= $data['form_title'] ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row mb-3">
                                                                    <label for="form_title<?= $data['id'] ?>" class="col-sm-3 col-form-label">Form Title</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" name="form_title" class="form-control" id="form_title<?= $data['id'] ?>" value="<?= htmlspecialchars($data['form_title']) ?>" required />
                                                                    </div>
                                                                </div>
                                                                <div class="row mb-3">
                                                                    <label for="form_link<?= $data['id'] ?>" class="col-sm-3 col-form-label">Form Link</label>
                                                                    <div class="col-sm-9">
                                                                        <textarea class="form-control" name="form_link" id="form_link<?= $data['id'] ?>" rows="8" required><?= htmlspecialchars($data['form_link']) ?></textarea>
                                                                    </div>
                                                                </div>
                                                                <input type="hidden" name="id" value="<?= $data['id'] ?>" />
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" name="update" class="btn btn-primary">Save Changes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="deleteModal<?= $data['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="post">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Are you sure you want to delete the form: <strong><?= htmlspecialchars($data['form_title']) ?></strong>?</p>
                                                                <input type="hidden" name="id" value="<?= $data['id'] ?>" />
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" name="delete" class="btn btn-danger">Delete</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                    <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="4" class="text-center">No forms found</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

</main><!-- End #main -->

<?php
require_once( __DIR__. '/../inc/footer.php');
?>