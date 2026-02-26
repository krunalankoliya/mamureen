<?php
    $current_page = 'manage_mamureen';
    require_once __DIR__ . '/../session.php';
    require_once __DIR__ . '/../inc/header.php';

    $mamureen = $db->fetchAll("SELECT * FROM users_mamureen ORDER BY fullname ASC");
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Mamureen Directory</h1>
            <p class="text-muted small mb-0">Global user management and directory synchronization.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo MODULE_PATH ?>assets/sample.csv" class="btn btn-light btn-sm rounded-pill px-3 border shadow-sm">
                <i class="bi bi-file-earmark-arrow-down me-1"></i> Sample CSV
            </a>
            <button id="addUserBtn" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                <i class="bi bi-cloud-upload me-1"></i> Import Users
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Upload Context (Hidden by default) -->
        <div class="col-12" id="uploadFormContainer" style="display: none;">
            <div class="card border-0 shadow-sm p-4 bg-primary bg-opacity-10 border-primary border-opacity-25">
                <h6 class="fw-bold mb-3">Bulk CSV Import</h6>
                <form id="addUserForm" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Select CSV File</label>
                        <input type="file" name="csv_file" id="csv_file" class="form-control border-0 shadow-sm" accept=".csv" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" id="uploadBtn" class="btn btn-primary w-100 rounded-pill">Start Processing</button>
                    </div>
                    <div class="col-md-3">
                        <button type="button" onclick="$('#uploadFormContainer').slideUp()" class="btn btn-light w-100 rounded-pill border">Cancel</button>
                    </div>
                </form>

                <div id="uploadProgress" class="mt-4 d-none">
                    <div class="progress rounded-pill mb-2" style="height: 12px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;"></div>
                    </div>
                    <div id="progressMsg" class="text-center small fw-bold text-primary"></div>
                </div>
            </div>
        </div>

        <!-- Global Directory -->
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Registered Mamureen</h5>
                    <div class="badge bg-light text-dark border rounded-pill px-3"><?php echo count($mamureen) ?> Total Records</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable" id="datatable_manage_mamureen">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Member</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Regional Info</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Contact</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Metadata</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mamureen as $row): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?php echo user_photo_url($row['its_id']) ?>" class="rounded-circle border shadow-sm" width="44" height="44">
                                            <div>
                                                <div class="fw-bold text-dark small"><?php echo h($row['fullname']) ?></div>
                                                <div class="text-muted" style="font-size: 0.7rem;">ITS: <?php echo $row['its_id'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="small fw-semibold text-primary"><?php echo h($row['miqaat_mauze']) ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem;"><?php echo h($row['zone']) ?> • <?php echo h($row['miqaat_jamiat']) ?></div>
                                    </td>
                                    <td class="py-3">
                                        <div class="small text-dark"><?php echo h($row['email_id']) ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem;"><?php echo h($row['mobile']) ?></div>
                                    </td>
                                    <td class="py-3">
                                        <div class="badge bg-light text-secondary rounded-pill px-2" style="font-size: 0.65rem;"><?php echo h($row['mauze_type_name']) ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem; mt-1">Age: <?php echo $row['kg_age'] ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
    require_once __DIR__ . '/../inc/footer.php';
    require_once __DIR__ . '/../inc/js-block.php';
?>
<script>
$(document).ready(function(){
    $('#addUserBtn').click(function(){
        $('#uploadFormContainer').slideToggle();
    });
});
</script>
<script src="../assets/js/manage_mamureen.js"></script>