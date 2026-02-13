<?php
    require_once __DIR__ . '/../session.php';
    $current_page = 'manage_admin';
    require_once __DIR__ . '/../inc/header.php';

    function executeQuery($mysqli, $query)
    {
    try {
        $result = mysqli_query($mysqli, $query);
        return $result;
    } catch (Exception $e) {
        return ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
    }

    // Handle delete operation
    if (isset($_POST['delete'])) {
    $its_id = (int) $_POST['its_id'];
    $query  = "DELETE FROM `users_admin` WHERE `its_id` = '$its_id'";
    try {
        $result          = mysqli_query($mysqli, $query);
        $message['text'] = "Admin Deleted Successfully";
        $message['tag']  = 'success';
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
    }

    // Handle submit operation
    if (isset($_POST['submit'])) {
    $user_its  = $_COOKIE['user_its'];
    $ver       = $_COOKIE['ver'];
    $is_active = 1;
    $its_id    = (int) $_POST['its_id'];

    $api_url = "https://www.talabulilm.com/api2022/core/user/getUserDetailsByItsID/$its_id";
    $auth    = base64_encode("$user_its:$ver");
    $headers = [
        "Authorization: Basic $auth",
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RESOLVE, ['www.talabulilm.com:443:66.85.132.227']);
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    curl_close($ch);
    $data = json_decode($response, true);

    if ($data) {
        $nationality          = $data->nationality;
        $miqaat_india_foreign = ($nationality === 'Indian') ? 'I' : 'F';

        $dob = $data['dob'];

        // Calculate age from date of birth
        $today     = new DateTime();
        $birthdate = new DateTime($dob);
        $age       = $today->diff($birthdate)->y;

        $insertQuery = "INSERT INTO `users_admin` (`its_id`, `fullname`, `fullname_ar`, `email_id`, `mobile`, `miqaat_mauze`, `kg_age`, `miqaat_india_foreign`)
        VALUES ('$its_id', '$data[full_name_en]', '$data[full_name_ar]', '$data[email]', '$data[mobile]', '$data[jamaat]', '$age', '$miqaat_india_foreign')";

        try {
            $result          = mysqli_query($mysqli, $insertQuery);
            $message['text'] = "Admin Added Successfully";
            $message['tag']  = 'success';
        } catch (Exception $e) {
            $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
        }
    } else {
        $message['text'] = "Invalid ITS ID : $its_id";
        $message['tag']  = 'danger';
    }
    }

    // Fetch admin list
    $query      = "SELECT * FROM `users_admin`";
    $result     = mysqli_query($mysqli, $query);
    $admin_list = $result->fetch_all(MYSQLI_ASSOC);
?>

<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <?php require_once __DIR__ . '/../inc/messages.php'; ?>
            <div class="card">
                <div class="card-body" style="overflow-y: auto;">
                    <h5 class="card-title">Manage Admin</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Add new Admin</h5>
                                    <form method="post">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <input type="number" name="its_id" class="form-control" placeholder="ITS ID" required>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="d-grid gap-2">
                                                    <button class="btn btn-primary" name="submit" type="submit">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <h5 class="card-title">List of Admin</h5>
                            <table class="table table-striped" id="datatable">
                                <thead>
                                    <tr>
                                        <th scope="col">ITS</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Mobile</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admin_list as $admin): ?>
                                        <tr>
                                            <form method="post">
                                                <td><?php echo $admin['its_id'] ?></td>
                                                <td><?php echo $admin['fullname'] ?></td>
                                                <td><?php echo $admin['email_id'] ?></td>
                                                <td><?php echo $admin['mobile'] ?></td>
                                                <td>
                                                    <input type="hidden" name="its_id" value="<?php echo $admin['its_id'] ?>" />
                                                    <button type="submit" name="delete" class="btn btn-outline-danger remove-btn"><i class="bi bi-trash-fill"></i></button>
                                                </td>
                                            </form>
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

<?php
    require_once __DIR__ . '/../inc/footer.php';
    require_once __DIR__ . '/../inc/js-block.php';
?>
</body>
<script>
    $(document).ready(function() {
        // Handler for 'Remove' button click
        $('.remove-btn').click(function(event) {
            if (!confirm("Are you sure you want to remove this record?")) {
                event.preventDefault(); // Prevent form submission if user cancels
            }
        });
    });
</script>

</html>