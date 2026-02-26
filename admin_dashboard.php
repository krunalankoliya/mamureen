<?php
$current_page = 'admin_dashboard';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/inc/header.php';

// Helper for counts using PDO
function fetchCount($pdo, $query, $params = []) {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $res = $stmt->fetch();
    return $res ? (int) $res['count'] : 0;
}

// Stats Calculation
$total_house_visit_count = fetchCount($pdo, "SELECT COUNT(*) as count FROM migrated_students WHERE not_migrated = '0' AND institute_city IN (SELECT institute_city FROM map_city WHERE zone != 'ignore')");
$house_visit_count = fetchCount($pdo, "SELECT COUNT(*) as count FROM (SELECT student_its FROM hv_sub LEFT JOIN migrated_students ON hv_sub.student_its = migrated_students.its_id WHERE not_migrated = '0' GROUP BY student_its) q");
$house_visit_percent = ($total_house_visit_count > 0) ? round(($house_visit_count / $total_house_visit_count) * 100, 1) : 0;

$migratedInCount = fetchCount($pdo, "SELECT COUNT(*) as count FROM hv_master");
$migratedOutCount = fetchCount($pdo, "SELECT COUNT(*) as count FROM migrated_out_hv");
$identifiedCount = fetchCount($pdo, "SELECT COUNT(*) as count FROM identified_students");
$muraqibCount = fetchCount($pdo, "SELECT COUNT(*) as count FROM supervisor_master");

// Aamil Saheb / Core Info
$stmt = $pdo->prepare("SELECT SUM(total_amount) AS amount, currency FROM minhat_talimiyah GROUP BY currency");
$stmt->execute();
$minhatData = $stmt->fetchAll();

$today = date("Y-m-d");
$yesterday = date("Y-m-d", strtotime("-1 day"));
$todayParams = ["$today%", "$yesterday%"];

$migratedInToday = fetchCount($pdo, "SELECT COUNT(*) as count FROM hv_master WHERE added_ts LIKE ? OR added_ts LIKE ?", $todayParams);
$migratedOutToday = fetchCount($pdo, "SELECT COUNT(*) as count FROM migrated_out_hv WHERE added_ts LIKE ? OR added_ts LIKE ?", $todayParams);
$identifiedToday = fetchCount($pdo, "SELECT COUNT(*) as count FROM identified_students WHERE added_ts LIKE ? OR added_ts LIKE ?", $todayParams);

?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Master Admin Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Admin Dashboard</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            
            <!-- Quick Stats Row -->
            <div class="col-12">
                <div class="card p-3 mb-4">
                    <h5 class="card-title"><i class="bi bi-speedometer me-2"></i> Real-time Overview</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="stat-card bg-gradient-blue">
                                <div class="stat-icon icon-blue"><i class="bi bi-person-down"></i></div>
                                <div class="stat-info">
                                    <h6><?= $migratedInCount ?></h6>
                                    <span>Migrated In</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-gradient-green">
                                <div class="stat-icon icon-green"><i class="bi bi-person-up"></i></div>
                                <div class="stat-info">
                                    <h6><?= $migratedOutCount ?></h6>
                                    <span>Migrated Out</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-gradient-orange">
                                <div class="stat-icon icon-orange"><i class="bi bi-search"></i></div>
                                <div class="stat-info">
                                    <h6><?= $identifiedCount ?></h6>
                                    <span>Identified</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-gradient-purple">
                                <div class="stat-icon icon-purple"><i class="bi bi-shield-check"></i></div>
                                <div class="stat-info">
                                    <h6><?= $muraqibCount ?></h6>
                                    <span>Muraqebeen</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Progress Row -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">House Visit Progress <span>| Migrated In</span></h5>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $house_visit_percent ?>%" aria-valuenow="<?= $house_visit_percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="ms-3 fw-bold text-primary"><?= $house_visit_percent ?>%</div>
                        </div>
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <h4 class="mb-0 fw-bold"><?= $house_visit_count ?></h4>
                                <small class="text-muted text-uppercase">Completed</small>
                            </div>
                            <div class="col-6">
                                <h4 class="mb-0 fw-bold"><?= $total_house_visit_count ?></h4>
                                <small class="text-muted text-uppercase">Total Target</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Table -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recent Submissions <span>| Meetings & Photo Reports</span></h5>
                        <div class="table-responsive">
                            <table class="table table-hover datatable">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Barnamaj</th>
                                        <th>Attendees</th>
                                        <th>Date</th>
                                        <th>Files</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT * FROM seminar_reports ORDER BY id DESC LIMIT 50");
                                    $stmt->execute();
                                    foreach ($stmt->fetchAll() as $row):
                                    ?>
                                    <tr>
                                        <td>#<?= $row['id'] ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($row['barnamaj']) ?></td>
                                        <td><span class="badge bg-info-light text-info"><?= $row['haazereen_count'] ?></span></td>
                                        <td><?= date('d M Y', strtotime($row['date_of_barnamaj'])) ?></td>
                                        <td><i class="bi bi-paperclip me-1"></i><?= $row['uploaded_file_count'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Financials & Daily Stats -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Daily Pulse <span>| Last 48h</span></h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div><i class="bi bi-arrow-down-circle text-primary me-2"></i> New Migrations In</div>
                                <span class="badge bg-primary rounded-pill"><?= $migratedInToday ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div><i class="bi bi-arrow-up-circle text-success me-2"></i> New Migrations Out</div>
                                <span class="badge bg-success rounded-pill"><?= $migratedOutToday ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div><i class="bi bi-patch-plus text-warning me-2"></i> Newly Identified</div>
                                <span class="badge bg-warning text-dark rounded-pill"><?= $identifiedToday ?></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Minhat Collection <span>| Financials</span></h5>
                        <?php if ($minhatData): ?>
                            <?php foreach ($minhatData as $data): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="stat-icon icon-green shadow-sm" style="width: 40px; height: 40px; font-size: 1.2rem;">
                                        <i class="bi bi-currency-exchange"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0 fw-bold"><?= $data['currency'] ?> <?= number_format($data['amount'], 2) ?></h6>
                                        <small class="text-muted">Total Collected</small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">No financial data found.</p>
                        <?php endif; ?>
                        <div class="d-grid gap-2 mt-2">
                            <a href="admin/manage_minhat_talimiyah.php" class="btn btn-sm btn-outline-primary rounded-pill">View Detailed Report</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</main>

<?php 
require_once __DIR__ . '/inc/footer.php';
require_once __DIR__ . '/inc/js-block.php'; 
?>
