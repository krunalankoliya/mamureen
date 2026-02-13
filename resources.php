<?php
require_once(__DIR__ . '/session.php');

$current_page = 'resources';
require_once(__DIR__ . '/inc/header.php');
$its_id = (int)$_SESSION[USER_ITS];

$query = "SELECT 
    my.id,
    my.upload_date,
    my.title,
    my.file_name,
    my.dot_color,
    dl.its_id,
    dl.id AS download_log_id,
    dl.download_id,
    dl.download_date,
    CASE 
        WHEN dl.id IS NOT NULL THEN 1 
        ELSE 0 
    END AS is_downloaded
FROM my_downloads my
LEFT JOIN download_log dl 
    ON my.id = dl.download_id 
    AND dl.its_id = $its_id GROUP BY `my`.`id` ORDER BY `my`.`id` DESC";
$result = mysqli_query($mysqli, $query);
$data = $result->fetch_all(MYSQLI_ASSOC);

$linkQuery = "SELECT * FROM `my_links` ORDER BY `upload_date` DESC";
$linkResult = mysqli_query($mysqli, $linkQuery);
$linkData = $linkResult->fetch_all(MYSQLI_ASSOC);

?>

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Resources and Downloadables</h1>
    </div><!-- End Page Title -->

    <section class="section dashboard">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Downloads</h5>
                        <div class="activity">
                            <?php
                            if (empty($data)) {
                                echo '<p>No data available.</p>';
                            } else {
                                foreach ($data as $e_id => $resource) {
                                    if ($resource['is_downloaded'] == 1) {
                                        $display_date = date('d/m/y', strtotime($resource['download_date']));
                                        $download_text = "downloaded at " . $display_date;
                                         $title_class = ""; // Normal text for downloaded files
                                    } else {
                                        $display_date = date('d/m/y', strtotime($resource['upload_date']));
                                        $download_text = "";
                                         $title_class = "fw-bold"; 
                                    }
                        
                                    $current_date = time();
                                    $upload_date = ($current_date - strtotime($display_date)) < (48 * 60 * 60) ? '<span class="badge bg-success">New</span>' : $display_date;
                            ?>
                                    <div class="activity-item d-flex">
                                        <div class="activite-label"><?= $upload_date ?></div>
                                        <i class='bi bi-circle-fill activity-badge <?= $resource['dot_color'] ?> align-self-start'></i>
                                        <div class="activity-content">
                                            <a href="download_log.php?id=<?= $resource['id'] ?>" class="<?= $title_class ?>" download>
                                                <?= $resource['title'] ?> <?= $download_text ?>
                                            </a>
                                        </div>
                                    </div>
                            <?php
                                }
                            }
                            ?>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Links</h5>
                                <ul class="list-group">
                                    <?php
                                        if (empty($linkData)) {
                                            echo '<p>No data available.</p>';
                                        } else {
                                            foreach ($linkData as $e_id => $link) {

                                        ?>
                                            <li class="list-group-item"><i class="bx bx-link text-primary"></i> <a target="_blank" href="<?=$link['link']?>"><?=$link['title']?></a></li>

                                        <?php
                                            }
                                        }
                                        ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!--<div class="col-md-12">-->
                <!--    <div class="card">-->
                <!--        <div class="card-body">-->
                <!--            <h5 class="card-title">Workshop Video Links</h5>-->
                <!--            <ul class="list-group">-->
                <!--                <li class="list-group-item"><i class="bx bxs-video text-primary"></i> <a target="_blank" href="https://www.youtube.com/watch?v=wNpNV88SdOo">BQI Orientation Day 1 (Monday)</a></li>-->
                <!--                <li class="list-group-item"><i class="bx bxs-video text-primary"></i> <a target="_blank" href="https://www.youtube.com/watch?v=gOB5ENPIuxc">B.Q.I Khidmat Orientation 1445H - Session Two</a></li>-->

                <!--            </ul>-->
                <!--        </div>-->
                <!--    </div>-->
                <!--</div>-->
            </div>


        </div>
    </section>

</main><!-- End #main -->

<?php
require_once(__DIR__ . '/inc/footer.php');
?>