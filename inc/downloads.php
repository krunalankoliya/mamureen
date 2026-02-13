<?php
require_once('session.php');
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
    AND dl.its_id = $its_id GROUP BY `my`.`id` ORDER BY `my`.`id` DESC LIMIT 5";
// $query = "SELECT * FROM `my_downloads` ORDER BY `upload_date` DESC LIMIT 5";
$result = mysqli_query($mysqli, $query);
$row = $result->fetch_all(MYSQLI_ASSOC);
?>
<div class="card">
    <div class="card-body">
        <div class="card-header">Resources and Downloads</div>
        <div class="card-header"><i class="bx bx-globe text-primary"></i> <a href="<?=MODULE_PATH . 'resources.php'?>">All Resources and Downloads</a></div>
        <p>&nbsp;</p>
        <div class="activity">
             <?php
                            if (empty($row)) {
                                echo '<p>No data available.</p>';
                            } else {
                                foreach ($row as $e_id => $resource) {
                                    if ($resource['is_downloaded'] == 1) {
                                        $display_date = date('d/m/y', strtotime($resource['download_date']));
                                        $download_text = "downloaded at " . $display_date;
                                        $title_class = ""; // Normal text for downloaded files
                                    } else {
                                        $display_date = date('d/m/y', strtotime($resource['upload_date']));
                                        $download_text = "";
                                        $title_class = "fw-bold"; // Bold text for not downloaded files
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
</div><!-- End Recent Activity -->