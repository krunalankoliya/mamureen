<?php
require_once __DIR__ . '/../session.php';
$current_page = 'manage_sub_admin';
require_once __DIR__ . '/../inc/header.php';

// Ensure table exists
mysqli_query($mysqli, "CREATE TABLE IF NOT EXISTS `users_sub_admin` (
    `its_id`               VARCHAR(10)  NOT NULL,
    `fullname`             VARCHAR(255) DEFAULT NULL,
    `fullname_ar`          VARCHAR(255) DEFAULT NULL,
    `email_id`             VARCHAR(255) DEFAULT NULL,
    `mobile`               VARCHAR(20)  DEFAULT NULL,
    `miqaat_mauze`         VARCHAR(100) DEFAULT NULL,
    `kg_age`               INT          DEFAULT NULL,
    `miqaat_india_foreign` CHAR(1)      DEFAULT NULL,
    `added_ts`             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`its_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Handle delete
if (isset($_POST['delete'])) {
    $its_id = (int) $_POST['its_id'];
    $result = mysqli_query($mysqli, "DELETE FROM `users_sub_admin` WHERE `its_id` = '$its_id'");
    if ($result) {
        $message['text'] = "Sub Admin deleted successfully.";
        $message['tag']  = 'success';
    } else {
        $message['text'] = "Failed to delete: " . mysqli_error($mysqli);
        $message['tag']  = 'danger';
    }
}

// Handle add
if (isset($_POST['submit'])) {
    $its_id   = (int) $_POST['its_id'];
    $user_its = $_COOKIE['user_its'];
    $ver      = $_COOKIE['ver'];
    $auth     = base64_encode("$user_its:$ver");

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RESOLVE, ['www.talabulilm.com:443:66.85.132.227']);
    curl_setopt($ch, CURLOPT_URL, "https://www.talabulilm.com/api2022/core/user/getUserDetailsByItsID/$its_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic $auth"]);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($data) {
        $nationality          = $data['nationality'];
        $miqaat_india_foreign = ($nationality === 'Indian') ? 'I' : 'F';
        $birthdate            = new DateTime($data['dob']);
        $age                  = (new DateTime())->diff($birthdate)->y;

        $result = mysqli_query($mysqli, "INSERT INTO `users_sub_admin`
            (`its_id`, `fullname`, `fullname_ar`, `email_id`, `mobile`, `miqaat_mauze`, `kg_age`, `miqaat_india_foreign`)
            VALUES ('$its_id', '$data[full_name_en]', '$data[full_name_ar]', '$data[email]', '$data[mobile]', '$data[jamaat]', '$age', '$miqaat_india_foreign')");

        if ($result) {
            $message['text'] = "Sub Admin added successfully.";
            $message['tag']  = 'success';
        } else {
            $message['text'] = "Failed to add: " . mysqli_error($mysqli);
            $message['tag']  = 'danger';
        }
    } else {
        $message['text'] = "Invalid ITS ID: $its_id";
        $message['tag']  = 'danger';
    }
}

// Fetch list
$result        = mysqli_query($mysqli, "SELECT * FROM `users_sub_admin` ORDER BY `added_ts` DESC");
$sub_admin_list = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <?php require_once __DIR__ . '/../inc/messages.php'; ?>
            <div class="card">
                <div class="card-body" style="overflow-y: auto;">
                    <h5 class="card-title">Manage Sub Admin</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Add New Sub Admin</h5>
                                    <form method="post">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <input type="number" name="its_id" class="form-control" placeholder="ITS ID" required>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="d-grid">
                                                    <button class="btn btn-primary" name="submit" type="submit">Add</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mt-3">
                            <h5 class="card-title">
                                List of Sub Admins
                                <span class="badge bg-secondary ms-1"><?= count($sub_admin_list) ?></span>
                            </h5>
                            <table class="table table-striped" id="datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>ITS</th>
                                        <th>Photo</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Jamaat</th>
                                        <th>Added On</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sub_admin_list as $i => $sa): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= htmlspecialchars($sa['its_id']) ?></td>
                                            <td>
                                                <img src="https://www.talabulilm.com/mumin_images/<?= $sa['its_id'] ?>.png"
                                                     style="width:40px;height:40px;object-fit:cover;border-radius:50%;" alt="">
                                            </td>
                                            <td><?= htmlspecialchars($sa['fullname']) ?></td>
                                            <td><?= htmlspecialchars($sa['email_id'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($sa['mobile'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($sa['miqaat_mauze'] ?? '') ?></td>
                                            <td><?= date('d-M-Y', strtotime($sa['added_ts'])) ?></td>
                                            <td>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="its_id" value="<?= $sa['its_id'] ?>">
                                                    <button type="submit" name="delete" class="btn btn-sm btn-outline-danger remove-btn">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
<script src="<?= MODULE_PATH ?>assets/js/manage_sub_admin.js"></script>
