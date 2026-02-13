<?php
require_once( __DIR__. '/../session.php');

// $admin_its = array('50476733', '40444857', '20372359', '30419712');

// if(!in_array($user_its, $admin_its)) {
    //     header('location:index.php');
    // }

    $current_page = 'Manage_link';
    require_once( __DIR__. '/../inc/header.php');

if (isset($_POST['update'])) {
    $title = $_POST['title'];
    $id = $_POST['id'];

    $query = "UPDATE `my_links` SET `title` = '$title' WHERE `id` = '$id'";
    try {
        $result = mysqli_query($mysqli, $query);
        $message['text'] = "Update Record  Successfully";
        $message['tag'] = 'success';
    } catch (Exception $e) {
        $message = array('text' => $e->getMessage(), 'tag' => 'danger');
    }

}

if (isset($_POST['delete'])) {
    $id = $_POST['id'];

    $deleteQuery = "DELETE FROM my_links WHERE `id` = '$id'";
    try {
        $result = mysqli_query($mysqli, $deleteQuery);
        $message['text'] = "Delete Record  Successfully";
        $message['tag'] = 'success';
    } catch (Exception $e) {
        $message = array('text' => $e->getMessage(), 'tag' => 'danger');
    }

}

if (isset($_POST['submit'])) {
        $title = $_POST['title'];
        $link = $_POST['link'];
        $date = date('Y-m-d');

        $query = "INSERT INTO `my_links` (`upload_date`, `title`, `link`, `added_its`) VALUES ('$date', '$title', '$link', '$user_its')";

        try {
            $result = mysqli_query($mysqli, $query);
            $message['text'] = "File Upload Successfully";
            $message['tag'] = 'success';
        } catch (Exception $e) {
            $message = array('text' => $e->getMessage(), 'tag' => 'danger');
        }
}

$query = "SELECT * FROM `my_links`";
$result = mysqli_query($mysqli, $query);
$row = $result->fetch_all(MYSQLI_ASSOC);

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
                                    <h5 class="card-title">Upload Download Link</h5>
                                    <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
                                    <ul>
                                        <li>All fields marked with <span class="required_star">*</span> are required.</li>
                                    </ul>
                                    <!-- Horizontal Form -->
                                    <form method="post" enctype="multipart/form-data">
                                        <div class="row mb-3">
                                            <label for="title" class="col-sm-4 col-form-label">Title<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="title" name='title' placeholder="title" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="link" class="col-sm-4 col-form-label">Link<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control file" name="link" placeholder="link" id="link" required>
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
                            <h5 class="card-title">Previously Uploaded Reports</h5>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Upload date</th>
                                        <th scope="col">Title</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        foreach ($row as $key => $data) {
                                    ?>
                                            <form method="post" enctype="multipart/form-data">
                                                <tr>
                                                    <th scope="row"><?= $data['id'] ?></th>
                                                    <td><?= $data['upload_date'] ?></td>
                                                    <td>
                                                        <input type="text" name="title" class="form-control" placeholder="Expertise" value="<?= $data['title'] ?>" required />
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="id" value="<?= $data['id'] ?>" />
                                                        <input type="submit" name="update" value="Update" class="btn btn-outline-primary" />
                                                        <!--<input type="submit" name="delete" value="Delete" class="btn btn-outline-danger" />-->
                                                        <input type="submit" name="delete" value="Delete" class="btn btn-outline-danger" onclick="return confirmDelete();" />
                                                    </td>
                                                </tr>
                                            </form>
                                    <?php
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
<script>
    function confirmDelete() {
        return confirm("Are you sure you want to delete this link?");
    }
</script>
