<?php
require_once __DIR__ . '/../session.php';
$current_page = 'manage_mamureen';
require_once __DIR__ . '/../inc/header.php';

// Fetch current mamureen list
$result = mysqli_query($mysqli, "SELECT * FROM `users_mamureen`");
$data   = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <?php require_once __DIR__ . '/../inc/messages.php'; ?>
            <div class="card">
                <div class="card-body" style="overflow-y: auto;">
                    <h5 class="card-title">Manage Mamureen</h5>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-3">
                                <div class="card-body">

                                    <div class="d-flex gap-2 mb-3">
                                        <button id="addUserBtn" class="btn btn-primary">
                                            <i class="bi bi-upload me-1"></i>Upload CSV
                                        </button>
                                        <a href="https://www.talabulilm.com/mamureen/assets/sample.csv" class="btn btn-success" download>
                                            <i class="bi bi-download me-1"></i>Download Sample CSV
                                        </a>
                                    </div>

                                    <form id="addUserForm" style="display: none;">
                                        <div class="alert alert-info py-2">
                                            <strong>Note:</strong> Uploading will replace all existing records. ITS ID is required for every row.
                                            Rows are processed in batches of 10 — no timeout issues.
                                        </div>

                                        <div class="row g-2 align-items-end mb-3">
                                            <div class="col-auto">
                                                <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required>
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" id="uploadBtn" class="btn btn-primary">
                                                    <i class="bi bi-upload me-1"></i>Upload
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Progress bar -->
                                        <div id="uploadProgress" class="d-none">
                                            <div class="progress mb-2" style="height: 24px;">
                                                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                                     role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                            </div>
                                            <div id="progressMsg" class="text-center fw-semibold" style="font-size: 0.88rem;"></div>
                                        </div>

                                    </form>

                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <h5 class="card-title">List of Mamureen <span class="badge bg-secondary"><?= count($data) ?></span></h5>
                            <table class="table table-bordered cell-border" id="datatable_manage_mamureen">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ITS ID</th>
                                        <th>Photo</th>
                                        <th>Full Name</th>
                                        <th>AVS Idara</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Mauze Type</th>
                                        <th>Age</th>
                                        <th>Miqaat Mauze</th>
                                        <th>Jamiat</th>
                                        <th>Zone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data as $row) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['its_id']) ?></td>
                                            <td><img style="width:50px;height:50px;object-fit:cover;" src="https://www.talabulilm.com/mumin_images/<?= $row['its_id'] ?>.png" class="rounded-circle" alt=""></td>
                                            <td><?= htmlspecialchars($row['fullname']) ?></td>
                                            <td><?= htmlspecialchars($row['avs_idara']) ?></td>
                                            <td><?= htmlspecialchars($row['email_id']) ?></td>
                                            <td><?= htmlspecialchars($row['mobile']) ?></td>
                                            <td><?= htmlspecialchars($row['mauze_type_name']) ?></td>
                                            <td><?= htmlspecialchars($row['kg_age']) ?></td>
                                            <td><?= htmlspecialchars($row['miqaat_mauze']) ?></td>
                                            <td><?= htmlspecialchars($row['miqaat_jamiat']) ?></td>
                                            <td><?= htmlspecialchars($row['zone']) ?></td>
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

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
<style>
#progressBar { line-height: 24px; font-weight: bold; font-size: 0.82rem; }
</style>
<script src="../assets/js/manage_mamureen.js"></script>
