<?php
require_once __DIR__ . '/../session.php';
$current_page = 'report_zakereen_parties';
require_once __DIR__ . '/../inc/header.php';

$result  = mysqli_query($mysqli, "SELECT p.*, u.fullname AS added_by
    FROM `bqi_zakereen_parties` p
    LEFT JOIN `users_admin` u ON p.added_its = u.its_id
    ORDER BY p.id DESC");
$records = $result->fetch_all(MYSQLI_ASSOC);
?>
<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        Zakereen Parties Report
                        <span class="badge bg-secondary ms-2"><?= count($records) ?> records</span>
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-striped" id="datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Party Name</th>
                                    <th>Status</th>
                                    <th>Added By</th>
                                    <th>Added By (ITS)</th>
                                    <th>Added On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $i => $r): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= htmlspecialchars($r['party_name']) ?></td>
                                        <td><?= $r['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                                        <td><?= htmlspecialchars($r['added_by'] ?? '—') ?></td>
                                        <td><?= $r['added_its'] ?></td>
                                        <td><?= date('d-M-Y', strtotime($r['added_ts'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../inc/footer.php'; ?>
