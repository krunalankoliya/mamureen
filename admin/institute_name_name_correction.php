<?php
require_once(__DIR__ . '/../session.php');

$current_page = 'institute_name_name_correction';
require_once(__DIR__ . '/../inc/header.php');

if (isset($_POST['update'])) {
    $updates = $_POST['updates'];

    $updatedInstitute = [];

    foreach ($updates as $institute => $update) {
        $araiz_institute = isset($update['araiz_institute']) ? $mysqli->real_escape_string($update['araiz_institute']) : '';

        if (!empty($araiz_institute) || $araiz_institute !== '') {
            $updateQuery = "UPDATE `migrated_students` SET `institute_name` = '$araiz_institute' WHERE `institute_name` = '$institute'";

            try {
                $result = mysqli_query($mysqli, $updateQuery);
                $affectedRows = mysqli_affected_rows($mysqli);

                if ($affectedRows > 0) {
                    $updatedInstitute[] = $institute;
                }
            } catch (Exception $e) {
                // Handle any errors if needed
            }
        }
    }

    if (!empty($updatedInstitute)) {
        $message['text'] = "Institute Name Updated Successfully: " . implode(', ', $updatedInstitute);
        $message['tag'] = 'success';
    } else {
        $message['text'] = "No Institute Name were updated";
        $message['tag'] = 'info';
    }
}

$instituteQuery = "SELECT DISTINCT `m`.`institute_name`, `m`.`institute_city`
    FROM `migrated_students` `m`
    WHERE
        `m`.`institute_name` NOT IN (
            SELECT `name`
            FROM `talabulilm_araiz`.`institute_master` `i`
            WHERE `i`.`city_id` = ( SELECT `id` FROM `talabulilm_araiz`.`city_master` `cm` WHERE `cm`.`city_name` = `m`.`institute_city` )
        )
        AND m.institute_name != '' AND `m`.`institute_city` != '' LIMIT 5";

$instituteResult = mysqli_query($mysqli, $instituteQuery);
$instituteData = mysqli_fetch_all($instituteResult, MYSQLI_ASSOC);

$pendingInstituteQuery = "SELECT  COUNT(DISTINCT(`institute_name`)) `count`
    FROM `migrated_students` `m`
    WHERE
        `m`.`institute_name` NOT IN (
            SELECT `name`
            FROM `talabulilm_araiz`.`institute_master` `i`
            WHERE `i`.`city_id` = ( SELECT `id` FROM `talabulilm_araiz`.`city_master` `cm` WHERE `cm`.`city_name` = `m`.`institute_city` )
        )
        AND m.institute_name != '' AND `m`.`institute_city` != ''";
$pendingInstituteResult = mysqli_query($mysqli, $pendingInstituteQuery);
$pendingInstituteData = $pendingInstituteResult->fetch_assoc();

?>
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Total Pending Institute : <?= $pendingInstituteData['count'] ?></h1>
    </div><!-- End Page Title -->

    <section class="section dashboard">
        <div class="row">
            <?php require_once(__DIR__ . '/../inc/messages.php'); ?>
            <div class="card">
                <div class="card-body">
                    <form method="post" action="">
                        <?php
                        foreach ($instituteData as $institute) {
                            $current_institute = $institute['institute_name'];
                        ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <p><?= $current_institute ?></p>
                                    <input type="hidden" name="updates[<?= $current_institute ?>][institute_name]" value="<?= $current_institute ?>">
                                </div>
                                <div class="col-md-9">
                                    <select name="updates[<?= $current_institute ?>][araiz_institute]" style="width:100%">
                                        <option value='' selected>Select Institute</option>
                                        <?php
                                        $araizInstituteQuery = "SELECT `name`
                                            FROM `talabulilm_araiz`.`institute_master` `i`
                                            LEFT JOIN `talabulilm_araiz`.`city_master` `c` ON `i`.`city_id` = `c`.`id`
                                            WHERE `c`.`city_name` = '$institute[institute_city]'";

                                        $araizInstituteResult = mysqli_query($mysqli, $araizInstituteQuery);
                                        $araizInstituteData = mysqli_fetch_all($araizInstituteResult, MYSQLI_ASSOC);
                                        foreach ($araizInstituteData as $araizInstitute) {
                                        ?>
                                            <option value="<?= $araizInstitute['name'] ?>"><?= $araizInstitute['name'] ?></option>
                                        <?php
                                        }

                                        ?>
                                    </select>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                        <input type="submit" name="update" value="Update" class="btn btn-primary ">
                    </form>
                </div>
            </div>
        </div>
    </section>

</main><!-- End #main -->

<?php
require_once(__DIR__ . '/../inc/footer.php');
?>