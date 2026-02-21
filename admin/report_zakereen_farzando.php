<?php
require_once __DIR__ . '/../session.php';
$current_page = 'report_zakereen_farzando';
require_once __DIR__ . '/../inc/header.php';

$result  = mysqli_query($mysqli, "SELECT f.*, p.party_name, u.fullname AS added_by
    FROM `bqi_zakereen_farzando` f
    LEFT JOIN `bqi_zakereen_parties` p ON f.party_id = p.id
    LEFT JOIN `users_admin` u ON f.added_its = u.its_id
    ORDER BY f.id DESC");
$records = $result->fetch_all(MYSQLI_ASSOC);
?>
<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        Zakereen Farzando Report
                        <span class="badge bg-secondary ms-2"><?= count($records) ?> records</span>
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-striped" id="datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ITS ID</th>
                                    <th>Full Name</th>
                                    <th>Party</th>
                                    <th>Jamaat</th>
                                    <th>Gender</th>
                                    <th>Mobile</th>
                                    <th>Source</th>
                                    <th>Added By</th>
                                    <th>Added On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $i => $r): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= $r['its_id'] ?></td>
                                        <td><?= htmlspecialchars($r['full_name']) ?></td>
                                        <td><?= htmlspecialchars($r['party_name'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($r['jamaat'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($r['gender'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($r['mobile'] ?? '') ?></td>
                                        <td><?= $r['is_bulk_upload'] ? '<span class="badge bg-info">CSV</span>' : '<span class="badge bg-primary">Manual</span>' ?></td>
                                        <td><?= htmlspecialchars($r['added_by'] ?? $r['added_its']) ?></td>
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
