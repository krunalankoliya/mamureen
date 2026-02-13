<?php
require_once( __DIR__. '/../session.php');

$current_page = 'meetings_data';
require_once( __DIR__. '/../inc/header.php');

if (isset($_POST['submit'])) {
    $date = $_POST['date'];
}
// if(!isset($date)) $date = date('Y-m-d');
$category = isset($_POST['category']) ? $_POST['category'] : '';

$query = "SELECT `s`.*,`u`.`miqaat_mauze` FROM `meeting_and_photo_reports` `s`
LEFT JOIN `users_mamureen` `u` ON `s`.`user_its` = `u`.`its_id`
WHERE `s`.`category` = '$category'";
$result = mysqli_query($mysqli, $query);
$row = $result->fetch_all(MYSQLI_ASSOC);

?>

<main id="main" class="main">

    <section class="section dashboard">
        <div class="row">

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Meeting & Photo Report</h5>

                    <form method="post">
                        <div class="row">

                                <!--<label for="inputDate" class="col-md-3 col-form-label">Select Report Date : </label>-->
                                <!--<div class="col-md-6">-->
                                <!--    <input type="date" name="date" class="form-control" value="<?=$date?>">-->
                                <!--</div>-->
                                
                                <label for="inputCategory" class="col-md-3 col-form-label">Select Category:</label>
                                <div class="col-md-6">
                                    <select id="inputState" class="form-select" name="category" required>
                                        <option value="" disabled selected hidden>Select Category...</option>
                                        <option value="Khidmat Guzar LIsan ud Dawat na Sessions Mumineen darmiyan conduct karta hua" <?= ($category == "Khidmat Guzar LIsan ud Dawat na Sessions Mumineen darmiyan conduct karta hua") ? 'selected' : '' ?>>Khidmat Guzar LIsan ud Dawat na Sessions Mumineen darmiyan conduct karta hua</option>
                                        <option value="BQI waaste Banners Display/ Hall Tazyeen" <?= ($category == "BQI waaste Banners Display/ Hall Tazyeen") ? 'selected' : '' ?>>BQI waaste Banners Display/ Hall Tazyeen</option>
                                        <option value="Mumineen Nasihat Hifzan Tasmee karta hua" <?= ($category == "Mumineen Nasihat Hifzan Tasmee karta hua") ? 'selected' : '' ?>>Mumineen Nasihat Hifzan Tasmee karta hua</option>
                                        <option value="Mumineen Lisan ud Dawat ni Activities karta hua" <?= ($category == "Mumineen Lisan ud Dawat ni Activities karta hua") ? 'selected' : '' ?>>Mumineen Lisan ud Dawat ni Activities karta hua</option>
                                        <option value="Session ma hazereen sathe interaction" <?= ($category == "Session ma hazereen sathe interaction") ? 'selected' : '' ?>>Session ma hazereen sathe interaction</option>
                                        <option value="Haazereen no aerial view" <?= ($category == "Haazereen no aerial view") ? 'selected' : '' ?>>Haazereen no aerial view</option>
                                        <option value="Ilqa karnar khidmat guzar no closeup photo" <?= ($category == "Ilqa karnar khidmat guzar no closeup photo") ? 'selected' : '' ?>>Ilqa karnar khidmat guzar no closeup photo</option>
                                    </select>
                                </div>

                            <div class="col-md-3">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary" name="submit" type="submit">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <?php
                    foreach ($row as $key => $data) {
                        $mauze = isset($data['miqaat_mauze']) ? $data['miqaat_mauze'] : 'Not found';
                    ?>
                        <div class="row">
                            <h5 class="card-title">Mauze : <?= $mauze ?> - <?= $data['category'] ?></h5>
                            <div class="col">
                                <a href="<?= MODULE_PATH . 'user_uploads/' . $data['upload_photo_1'] ?>" class="fw-bold" download="<?= str_replace(' ', '_', $data['miqaat_mauze'] . '-photo-1') ?>"><img src="<?= MODULE_PATH . 'user_uploads/' . $data['upload_photo_1'] ?>" class="img-fluid"></a>
                            </div>
                            <?php if (!empty($data['upload_photo_2'])) : ?>
                                <div class="col">
                                    <a href="<?= MODULE_PATH . 'user_uploads/' . $data['upload_photo_2'] ?>" class="fw-bold" download="<?= str_replace(' ', '_', $data['miqaat_mauze'] . '-photo-2') ?>"><img src="<?= MODULE_PATH . 'user_uploads/' . $data['upload_photo_2'] ?>" class="img-fluid"></a>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($data['upload_photo_3'])) : ?>
                                <div class="col">
                                    <a href="<?= MODULE_PATH . 'user_uploads/' . $data['upload_photo_3'] ?>" class="fw-bold" download="<?= str_replace(' ', '_', $data['miqaat_mauze'] . '-photo-3') ?>"><img src="<?= MODULE_PATH . 'user_uploads/' . $data['upload_photo_3'] ?>" class="img-fluid"></a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>

        </div>
    </section>

</main><!-- End #main -->

<?php
require_once( __DIR__. '/../inc/footer.php');
?>