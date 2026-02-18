<?php
$current_page = 'dashboard';
require_once(__DIR__ . '/inc/header.php');

function getCounts($query, $mysqli)
{
    $result = mysqli_query($mysqli, $query);
    $res = $result->fetch_assoc();
    return ($res && $res['count']) ? (int) $res['count'] : 0;
}

// Section 1: Zakereen
$parties_count = getCounts("SELECT COUNT(*) `count` FROM `bqi_zakereen_parties` WHERE `is_active` = 1", $mysqli);
$farzando_count = getCounts("SELECT COUNT(*) `count` FROM `bqi_zakereen_farzando`", $mysqli);
$training_farzando_count = getCounts("SELECT COUNT(*) `count` FROM `meeting_and_photo_reports` WHERE `jamaat` = '$mauze' AND `category` LIKE 'Zakereen Farzando Training%'", $mysqli);

// Section 2: Social Media
$social_media_count = getCounts("SELECT COUNT(*) `count` FROM `meeting_and_photo_reports` WHERE `jamaat` = '$mauze' AND `category` LIKE 'Social Media Awareness%'", $mysqli);
$one_on_one_count = getCounts("SELECT COUNT(*) `count` FROM `bqi_individual_tafheem` WHERE `added_its` = '$user_its'", $mysqli);

// Section 3: Shaadi Umoor
$shaadi_baramij_count = getCounts("SELECT COUNT(*) `count` FROM `meeting_and_photo_reports` WHERE `jamaat` = '$mauze' AND `category` LIKE 'Shaadi Tafheem%'", $mysqli);
$individual_tafheem_count = getCounts("SELECT COUNT(*) `count` FROM `bqi_individual_tafheem` WHERE `added_its` = '$user_its'", $mysqli);

// Section 4
$challenges_count = getCounts("SELECT COUNT(*) `count` FROM `bqi_challenges_solutions` WHERE `added_its` = '$user_its'", $mysqli);
$noteworthy_count = getCounts("SELECT COUNT(*) `count` FROM `bqi_noteworthy_experiences` WHERE `added_its` = '$user_its'", $mysqli);
$khidmat_count = getCounts("SELECT COUNT(*) `count` FROM `bqi_khidmat_preparations` WHERE `added_its` = '$user_its'", $mysqli);
?>

<main id="main" class="main bqi-1447">
    <div class="pagetitle">
        <h1>Dashboard</h1>
    </div>

    <section class="section dashboard">
        <div class="row">

            <!-- Left side columns -->
            <div class="col-lg-8">

                <!-- SECTION 1: ZAKEREEN -->
                <div class="card mb-3">
                    <div class="card-body pb-0">
                        <h5 class="card-title pb-0 mb-2"><i class="bi bi-people-fill text-primary"></i> Zakereen</h5>
                        <div class="row align-items-stretch">
                            <div class="col-md-4 mb-3">
                                <a href="<?= MODULE_PATH ?>zakereen_parties.php" class="text-decoration-none d-block h-100">
                                    <div class="d-flex align-items-center p-3 rounded h-100" style="background: linear-gradient(135deg, #e8f0fe 0%, #d2e3fc 100%); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #4154f1; flex-shrink: 0;">
                                            <i class="bi bi-flag-fill text-white" style="font-size: 20px;"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6 class="mb-0 text-dark" style="font-size: 22px; font-weight: 700;"><?= $parties_count ?></h6>
                                            <small class="text-muted">Zakereen Parties</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="<?= MODULE_PATH ?>zakereen_farzando.php" class="text-decoration-none d-block h-100">
                                    <div class="d-flex align-items-center p-3 rounded h-100" style="background: linear-gradient(135deg, #e8f0fe 0%, #d2e3fc 100%); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #4154f1; flex-shrink: 0;">
                                            <i class="bi bi-person-lines-fill text-white" style="font-size: 20px;"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6 class="mb-0 text-dark" style="font-size: 22px; font-weight: 700;"><?= $farzando_count ?></h6>
                                            <small class="text-muted">Zakereen Farzando</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="<?= MODULE_PATH ?>training_sessions.php" class="text-decoration-none d-block h-100">
                                    <div class="d-flex align-items-center p-3 rounded h-100" style="background: linear-gradient(135deg, #e8f0fe 0%, #d2e3fc 100%); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #4154f1; flex-shrink: 0;">
                                            <i class="bi bi-mortarboard-fill text-white" style="font-size: 20px;"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6 class="mb-0 text-dark" style="font-size: 22px; font-weight: 700;"><?= $training_farzando_count ?></h6>
                                            <small class="text-muted">Training Sessions</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: SOCIAL MEDIA -->
                <div class="card mb-3">
                    <div class="card-body pb-0">
                        <h5 class="card-title pb-0 mb-2"><i class="bi bi-phone-fill text-success"></i> Social Media</h5>
                        <div class="row align-items-stretch">
                            <div class="col-md-4 mb-3">
                                <a href="<?= MODULE_PATH ?>training_sessions.php" class="text-decoration-none d-block h-100">
                                    <div class="d-flex align-items-center p-3 rounded h-100" style="background: linear-gradient(135deg, #e6f4ea 0%, #ceead6 100%); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #2eca6a; flex-shrink: 0;">
                                            <i class="bi bi-broadcast text-white" style="font-size: 20px;"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6 class="mb-0 text-dark" style="font-size: 22px; font-weight: 700;"><?= $social_media_count ?></h6>
                                            <small class="text-muted">Social Media Baramij</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="<?= MODULE_PATH ?>individual_tafheem.php" class="text-decoration-none d-block h-100">
                                    <div class="d-flex align-items-center p-3 rounded h-100" style="background: linear-gradient(135deg, #e6f4ea 0%, #ceead6 100%); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #2eca6a; flex-shrink: 0;">
                                            <i class="bi bi-chat-dots-fill text-white" style="font-size: 20px;"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6 class="mb-0 text-dark" style="font-size: 22px; font-weight: 700;"><?= $one_on_one_count ?></h6>
                                            <small class="text-muted">One on One Counseling</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="<?= MODULE_PATH ?>maaraz.php" class="text-decoration-none d-block h-100">
                                    <div class="d-flex align-items-center p-3 rounded h-100" style="background: linear-gradient(135deg, #e6f4ea 0%, #ceead6 100%); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #2eca6a; flex-shrink: 0;">
                                            <i class="bi bi-hourglass-split text-white" style="font-size: 20px;"></i>
                                        </div>
                                        <div class="ps-3">
                                            <span class="badge bg-warning text-dark" style="font-size: 13px;">Pending</span>
                                            <br><small class="text-muted">Ma'araz</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: SHAADI UMOOR -->
                <div class="card mb-3">
                    <div class="card-body pb-0">
                        <h5 class="card-title pb-0 mb-2"><i class="bi bi-heart-fill text-danger"></i> Shaadi Umoor</h5>
                        <div class="row align-items-stretch">
                            <div class="col-md-6 mb-3">
                                <a href="<?= MODULE_PATH ?>training_sessions.php" class="text-decoration-none d-block h-100">
                                    <div class="d-flex align-items-center p-3 rounded h-100" style="background: linear-gradient(135deg, #fce8e6 0%, #f8d0cc 100%); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #ff6384; flex-shrink: 0;">
                                            <i class="bi bi-calendar-event-fill text-white" style="font-size: 20px;"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6 class="mb-0 text-dark" style="font-size: 22px; font-weight: 700;"><?= $shaadi_baramij_count ?></h6>
                                            <small class="text-muted">Shaadi Layak Farzando Baramij</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="<?= MODULE_PATH ?>individual_tafheem.php" class="text-decoration-none d-block h-100">
                                    <div class="d-flex align-items-center p-3 rounded h-100" style="background: linear-gradient(135deg, #fce8e6 0%, #f8d0cc 100%); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #ff6384; flex-shrink: 0;">
                                            <i class="bi bi-person-check-fill text-white" style="font-size: 20px;"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6 class="mb-0 text-dark" style="font-size: 22px; font-weight: 700;"><?= $individual_tafheem_count ?></h6>
                                            <small class="text-muted">Individual Tafheem</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: REPORTS -->
                <div class="card mb-3">
                    <div class="card-body pb-0">
                        <h5 class="card-title pb-0 mb-2"><i class="bi bi-clipboard-data-fill text-warning"></i> Reports</h5>
                        <div class="row align-items-stretch">
                            <div class="col-md-4 mb-3">
                                <a href="<?= MODULE_PATH ?>challenges_solutions.php" class="text-decoration-none d-block h-100">
                                    <div class="d-flex align-items-center p-3 rounded h-100" style="background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #ff9f43; flex-shrink: 0;">
                                            <i class="bi bi-exclamation-triangle-fill text-white" style="font-size: 20px;"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6 class="mb-0 text-dark" style="font-size: 22px; font-weight: 700;"><?= $challenges_count ?></h6>
                                            <small class="text-muted">Challenges</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="<?= MODULE_PATH ?>noteworthy_experiences.php" class="text-decoration-none d-block h-100">
                                    <div class="d-flex align-items-center p-3 rounded h-100" style="background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #ff9f43; flex-shrink: 0;">
                                            <i class="bi bi-star-fill text-white" style="font-size: 20px;"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6 class="mb-0 text-dark" style="font-size: 22px; font-weight: 700;"><?= $noteworthy_count ?></h6>
                                            <small class="text-muted">Noteworthy</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="<?= MODULE_PATH ?>khidmat_preparations.php" class="text-decoration-none d-block h-100">
                                    <div class="d-flex align-items-center p-3 rounded h-100" style="background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #ff9f43; flex-shrink: 0;">
                                            <i class="bi bi-tools text-white" style="font-size: 20px;"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6 class="mb-0 text-dark" style="font-size: 22px; font-weight: 700;"><?= $khidmat_count ?></h6>
                                            <small class="text-muted">Khidmat Preps</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- End Left side columns -->

            <!-- Right side columns -->
            <div class="col-lg-4">

                <?php require_once(__DIR__ . '/inc/downloads.php'); ?>

                <?php
                $mauze = $rowdata['miqaat_mauze'];
                $query = "SELECT `u`.`full_name_en`,`j`.`primary_email`,`j`.`aamil_masool_its_id` FROM `its_jamaat_master` `j`
                      LEFT JOIN user `u` ON `j`.`aamil_masool_its_id` = `u`.`its_id`
                      WHERE `j`.`jamaat` = '$mauze'";
                $result = mysqli_query($coremysqli, $query);
                $row = $result->fetch_all(MYSQLI_ASSOC);
                ?>
                <!-- Aamil Saheb Info card -->
                <div class="card">
                    <div class="card-header">Aamil Saheb Info</div>
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= $row[0]['full_name_en'] ?></h5>
                        <?php
                        $imagePath = "https://www.talabulilm.com/mumin_images/" . $row[0]['aamil_masool_its_id'] . ".png";
                        if (@getimagesize($imagePath)) {
                            echo '<img src="' . $imagePath . '" style="width:80px" />';
                        } else {
                            echo '<img src="https://www.talabulilm.com/placeholder_image/placeholder.png" style="width:80px" />';
                        }
                        ?>
                    </div>
                    <div class="card-footer">
                        <p>Email: <a href="mailto:<?= $row[0]['primary_email'] ?>"><?= $row[0]['primary_email'] ?></a></p>
                    </div>
                </div><!-- End Aamil Saheb Info card -->

            </div><!-- End Right side columns -->

        </div>
    </section>
</main>

<?php require_once(__DIR__ . '/inc/footer.php'); ?>
<?php require_once(__DIR__ . '/inc/js-block.php'); ?>
