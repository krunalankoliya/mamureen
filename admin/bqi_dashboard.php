<?php
require_once __DIR__ . '/../session.php';
$current_page = 'bqi_dashboard';
require_once __DIR__ . '/../inc/header.php';

function getCounts($query, $mysqli)
{
    $result = mysqli_query($mysqli, $query);
    $res    = $result->fetch_assoc();
    return ($res && $res['count']) ? (int) $res['count'] : 0;
}

// Section 1: Zakereen
$parties_count           = getCounts("SELECT COUNT(*) `count` FROM `bqi_zakereen_parties` WHERE `is_active` = 1", $mysqli);
$farzando_count          = getCounts("SELECT COUNT(*) `count` FROM `bqi_zakereen_farzando`", $mysqli);
$training_farzando_count = getCounts("SELECT COUNT(*) `count` FROM `bqi_training_sessions` WHERE `program_type` LIKE '%Zakereen Farzando Training%'", $mysqli);

// Section 2: Social Media
$social_media_count = getCounts("SELECT COUNT(*) `count` FROM `bqi_training_sessions` WHERE `program_type` LIKE '%Social Media Awareness%'", $mysqli);
$one_on_one_count   = getCounts("SELECT COUNT(*) `count` FROM `bqi_individual_tafheem` WHERE `type` LIKE '%Social Media Awareness%'", $mysqli);

// Section 3: Shaadi Umoor
$shaadi_baramij_count     = getCounts("SELECT COUNT(*) `count` FROM `bqi_training_sessions` WHERE `program_type` LIKE '%Shaadi Tafheem%'", $mysqli);
$individual_tafheem_count = getCounts("SELECT COUNT(*) `count` FROM `bqi_individual_tafheem` WHERE `type` LIKE '%Shaadi Tafheem%'", $mysqli);

// Section 4: Reports
$challenges_count = getCounts("SELECT COUNT(*) `count` FROM `bqi_challenges_solutions`", $mysqli);
$noteworthy_count = getCounts("SELECT COUNT(*) `count` FROM `bqi_noteworthy_experiences`", $mysqli);
$khidmat_count    = getCounts("SELECT COUNT(*) `count` FROM `bqi_khidmat_preparations`", $mysqli);

$export_url = MODULE_PATH . 'admin/bqi_dashboard_export.php?type=';
?>

<style>
.stat-card {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-radius: 0.5rem;
    height: 100%;
    text-decoration: none;
    color: inherit;
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    color: inherit;
    text-decoration: none;
}
.stat-card .stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.stat-card .stat-icon i {
    font-size: 22px;
    color: #fff;
}
.stat-card .stat-body {
    padding-left: 1rem;
}
.stat-card .stat-count {
    font-size: 24px;
    font-weight: 700;
    line-height: 1.1;
    margin-bottom: 2px;
}
.stat-card .stat-label {
    font-size: 0.78rem;
    color: #6c757d;
}
.stat-card .csv-badge {
    position: absolute;
    top: 8px;
    right: 10px;
    font-size: 0.68rem;
    padding: 2px 7px;
    border-radius: 20px;
    background: rgba(0,0,0,0.08);
    color: #555;
}
.stat-card:hover .csv-badge {
    background: rgba(0,0,0,0.15);
}
</style>

<main id="main" class="main bqi-1447">
    <div class="pagetitle">
        <h1>BQI Admin Dashboard</h1>
        <p class="text-muted" style="font-size:0.9rem;">Global counts across all Mamureen. Click any card to download CSV.</p>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-lg-12">

                <!-- SECTION 1: ZAKEREEN -->
                <div class="card mb-3">
                    <div class="card-body pb-0">
                        <h5 class="card-title pb-0 mb-2"><i class="bi bi-people-fill text-primary"></i> Zakereen</h5>
                        <div class="row align-items-stretch">

                            <div class="col-md-4 mb-3">
                                <a href="<?= $export_url ?>zakereen_parties" class="d-block h-100">
                                    <div class="stat-card" style="background: linear-gradient(135deg, #e8f0fe 0%, #d2e3fc 100%);">
                                        <div class="stat-icon" style="background: #4154f1;"><i class="bi bi-flag-fill"></i></div>
                                        <div class="stat-body">
                                            <div class="stat-count text-dark"><?= $parties_count ?></div>
                                            <div class="stat-label">Zakereen Parties</div>
                                        </div>
                                        <span class="csv-badge"><i class="bi bi-download"></i> CSV</span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-4 mb-3">
                                <a href="<?= $export_url ?>zakereen_farzando" class="d-block h-100">
                                    <div class="stat-card" style="background: linear-gradient(135deg, #e8f0fe 0%, #d2e3fc 100%);">
                                        <div class="stat-icon" style="background: #4154f1;"><i class="bi bi-person-lines-fill"></i></div>
                                        <div class="stat-body">
                                            <div class="stat-count text-dark"><?= $farzando_count ?></div>
                                            <div class="stat-label">Zakereen Farzando</div>
                                        </div>
                                        <span class="csv-badge"><i class="bi bi-download"></i> CSV</span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-4 mb-3">
                                <a href="<?= $export_url ?>zakereen_training" class="d-block h-100">
                                    <div class="stat-card" style="background: linear-gradient(135deg, #e8f0fe 0%, #d2e3fc 100%);">
                                        <div class="stat-icon" style="background: #4154f1;"><i class="bi bi-mortarboard-fill"></i></div>
                                        <div class="stat-body">
                                            <div class="stat-count text-dark"><?= $training_farzando_count ?></div>
                                            <div class="stat-label">Zakereen Training Sessions</div>
                                        </div>
                                        <span class="csv-badge"><i class="bi bi-download"></i> CSV</span>
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

                            <div class="col-md-6 mb-3">
                                <a href="<?= $export_url ?>social_media_baramij" class="d-block h-100">
                                    <div class="stat-card" style="background: linear-gradient(135deg, #e6f4ea 0%, #ceead6 100%);">
                                        <div class="stat-icon" style="background: #2eca6a;"><i class="bi bi-broadcast"></i></div>
                                        <div class="stat-body">
                                            <div class="stat-count text-dark"><?= $social_media_count ?></div>
                                            <div class="stat-label">Social Media Baramij</div>
                                        </div>
                                        <span class="csv-badge"><i class="bi bi-download"></i> CSV</span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-6 mb-3">
                                <a href="<?= $export_url ?>social_media_counseling" class="d-block h-100">
                                    <div class="stat-card" style="background: linear-gradient(135deg, #e6f4ea 0%, #ceead6 100%);">
                                        <div class="stat-icon" style="background: #2eca6a;"><i class="bi bi-chat-dots-fill"></i></div>
                                        <div class="stat-body">
                                            <div class="stat-count text-dark"><?= $one_on_one_count ?></div>
                                            <div class="stat-label">One on One Counseling</div>
                                        </div>
                                        <span class="csv-badge"><i class="bi bi-download"></i> CSV</span>
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
                                <a href="<?= $export_url ?>shaadi_baramij" class="d-block h-100">
                                    <div class="stat-card" style="background: linear-gradient(135deg, #fce8e6 0%, #f8d0cc 100%);">
                                        <div class="stat-icon" style="background: #ff6384;"><i class="bi bi-calendar-event-fill"></i></div>
                                        <div class="stat-body">
                                            <div class="stat-count text-dark"><?= $shaadi_baramij_count ?></div>
                                            <div class="stat-label">Shaadi Layak Farzando Baramij</div>
                                        </div>
                                        <span class="csv-badge"><i class="bi bi-download"></i> CSV</span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-6 mb-3">
                                <a href="<?= $export_url ?>shaadi_tafheem" class="d-block h-100">
                                    <div class="stat-card" style="background: linear-gradient(135deg, #fce8e6 0%, #f8d0cc 100%);">
                                        <div class="stat-icon" style="background: #ff6384;"><i class="bi bi-person-check-fill"></i></div>
                                        <div class="stat-body">
                                            <div class="stat-count text-dark"><?= $individual_tafheem_count ?></div>
                                            <div class="stat-label">Individual Tafheem</div>
                                        </div>
                                        <span class="csv-badge"><i class="bi bi-download"></i> CSV</span>
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
                                <a href="<?= $export_url ?>challenges" class="d-block h-100">
                                    <div class="stat-card" style="background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);">
                                        <div class="stat-icon" style="background: #ff9f43;"><i class="bi bi-exclamation-triangle-fill"></i></div>
                                        <div class="stat-body">
                                            <div class="stat-count text-dark"><?= $challenges_count ?></div>
                                            <div class="stat-label">Challenges</div>
                                        </div>
                                        <span class="csv-badge"><i class="bi bi-download"></i> CSV</span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-4 mb-3">
                                <a href="<?= $export_url ?>noteworthy" class="d-block h-100">
                                    <div class="stat-card" style="background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);">
                                        <div class="stat-icon" style="background: #ff9f43;"><i class="bi bi-star-fill"></i></div>
                                        <div class="stat-body">
                                            <div class="stat-count text-dark"><?= $noteworthy_count ?></div>
                                            <div class="stat-label">Noteworthy Experiences</div>
                                        </div>
                                        <span class="csv-badge"><i class="bi bi-download"></i> CSV</span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-4 mb-3">
                                <a href="<?= $export_url ?>khidmat" class="d-block h-100">
                                    <div class="stat-card" style="background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);">
                                        <div class="stat-icon" style="background: #ff9f43;"><i class="bi bi-tools"></i></div>
                                        <div class="stat-body">
                                            <div class="stat-count text-dark"><?= $khidmat_count ?></div>
                                            <div class="stat-label">Khidmat Preparations</div>
                                        </div>
                                        <span class="csv-badge"><i class="bi bi-download"></i> CSV</span>
                                    </div>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
<?php require_once __DIR__ . '/../inc/js-block.php'; ?>
