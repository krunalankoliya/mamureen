<?php
$current_page = 'zakereen_farzando';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/inc/header.php';

$message = null;
$verified_user = null;

/**
 * Logic: Verify ITS ID
 */
if (isset($_POST['verify_its'])) {
    $verify_its_id = (int) ($_POST['verify_its_id'] ?? 0);
    if ($verify_its_id > 10000000) {
        $verified_user = ApiService::getUserDetails($verify_its_id);
        if (!$verified_user) {
            $message = ['text' => "ITS ID $verify_its_id not found in central database.", 'tag' => 'danger'];
        }
    } else {
        $message = ['text' => 'Please enter a valid 8-digit ITS ID.', 'tag' => 'warning'];
    }
}

/**
 * Logic: Save Farzand
 */
if (isset($_POST['add_farzand'])) {
    $party_id = (int) $_POST['party_id'];
    $its_id = (int) $_POST['its_id'];
    
    // Check duplicate in same party
    $exists = $db->fetch("SELECT id FROM bqi_zakereen_farzando WHERE its_id = ? AND party_id = ?", [$its_id, $party_id]);
    
    if ($exists) {
        $message = ['text' => "This person is already registered in the selected party.", 'tag' => 'warning'];
    } else {
        $db->insert('bqi_zakereen_farzando', [
            'party_id' => $party_id,
            'its_id' => $its_id,
            'full_name' => $_POST['full_name'],
            'gender' => $_POST['gender'],
            'dob' => $_POST['dob'],
            'jamaat' => $_POST['jamaat_val'],
            'jamiat' => $_POST['jamiat_val'],
            'mobile' => $_POST['mobile'],
            'email' => $_POST['email'],
            'is_bulk_upload' => 0,
            'added_its' => $user_its
        ]);
        $message = ['text' => "Farzand registered successfully.", 'tag' => 'success'];
    }
}

/**
 * Logic: Bulk Upload
 */
if (isset($_POST['bulk_upload']) && !empty($_FILES['csv_file']['tmp_name'])) {
    $party_id = (int) $_POST['party_id'];
    $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
    $success = 0; $skipped = 0;
    
    while (($line = fgetcsv($file)) !== false) {
        $csv_its = (int) trim($line[0]);
        if ($csv_its <= 0) continue;

        $exists = $db->fetch("SELECT id FROM bqi_zakereen_farzando WHERE its_id = ? AND party_id = ?", [$csv_its, $party_id]);
        if ($exists) { $skipped++; continue; }

        $data = ApiService::getUserDetails($csv_its);
        if ($data) {
            $db->insert('bqi_zakereen_farzando', [
                'party_id' => $party_id,
                'its_id' => $csv_its,
                'full_name' => $data['full_name_en'],
                'gender' => $data['gender'] ?? '',
                'dob' => $data['dob'] ?? '',
                'jamaat' => $data['jamaat'] ?? '',
                'jamiat' => $data['jamiat'] ?? '',
                'mobile' => $data['mobile'] ?? '',
                'email' => $data['email'] ?? '',
                'is_bulk_upload' => 1,
                'added_its' => $user_its
            ]);
            $success++;
        } else { $skipped++; }
    }
    fclose($file);
    $message = ['text' => "Bulk Upload Complete. Success: $success, Skipped/Failed: $skipped", 'tag' => 'info'];
}

/**
 * Fetch Data
 */
$parties = $db->fetchAll("SELECT id, party_name FROM bqi_zakereen_parties WHERE is_active = 1 AND added_its IN (SELECT its_id FROM users_mamureen WHERE miqaat_mauze = ?) ORDER BY party_name ASC", [$mauze]);

$farzando = $db->fetchAll("
    SELECT f.*, p.party_name, um.fullname AS submitted_by 
    FROM bqi_zakereen_farzando f
    LEFT JOIN bqi_zakereen_parties p ON f.party_id = p.id
    LEFT JOIN users_mamureen um ON um.its_id = f.added_its
    WHERE f.added_its IN (SELECT its_id FROM users_mamureen WHERE miqaat_mauze = ?)
    ORDER BY f.added_ts DESC", [$mauze]);

?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Zakereen Farzando</h1>
            <p class="text-muted small mb-0">Manage individual and bulk registration for Zakereen participants.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= MODULE_PATH ?>samples/sample_farzando_bulk_upload.csv" class="btn btn-light btn-sm rounded-pill px-3 border">
                <i class="bi bi-file-earmark-arrow-down me-1"></i> Sample CSV
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message['tag']; ?> border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-info-circle-fill me-2"></i> <?= $message['text']; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Individual Registration Card -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <h5 class="fw-bold mb-0">Manual Registration</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    
                    <?php if (!$verified_user): ?>
                        <form method="post" class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Enter ITS ID</label>
                                <input type="number" name="verify_its_id" class="form-control form-control-lg" placeholder="8 Digit ITS ID" required>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" name="verify_its" class="btn btn-primary btn-lg w-100 rounded-pill">
                                    Verify Member
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="p-3 rounded-4 bg-light border mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?= user_photo_url($verified_user['its_id']) ?>" class="rounded-circle shadow-sm" width="64" height="64" style="object-fit: cover;">
                                <div>
                                    <h6 class="fw-bold mb-0"><?= h($verified_user['full_name_en']) ?></h6>
                                    <p class="text-muted small mb-0"><?= h($verified_user['its_id']) ?> • <?= h($verified_user['jamaat']) ?></p>
                                </div>
                                <a href="zakereen_farzando.php" class="ms-auto btn btn-sm btn-light rounded-pill px-3">Change</a>
                            </div>
                        </div>

                        <form method="post" class="row g-3">
                            <input type="hidden" name="its_id" value="<?= $verified_user['its_id'] ?>">
                            <input type="hidden" name="full_name" value="<?= $verified_user['full_name_en'] ?>">
                            <input type="hidden" name="gender" value="<?= $verified_user['gender'] ?>">
                            <input type="hidden" name="dob" value="<?= $verified_user['dob'] ?>">
                            <input type="hidden" name="jamaat_val" value="<?= $verified_user['jamaat'] ?>">
                            <input type="hidden" name="jamiat_val" value="<?= $verified_user['jamiat'] ?>">
                            <input type="hidden" name="mobile" value="<?= $verified_user['mobile'] ?>">
                            <input type="hidden" name="email" value="<?= $verified_user['email'] ?>">

                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Select Active Party</label>
                                <select name="party_id" class="form-select form-select-lg" required>
                                    <option value="">Choose a party...</option>
                                    <?php foreach ($parties as $p): ?>
                                        <option value="<?= $p['id'] ?>"><?= h($p['party_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 mt-4 text-end">
                                <button type="submit" name="add_farzand" class="btn btn-primary px-5 rounded-pill btn-lg">Complete Registration</button>
                            </div>
                        </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- Bulk Upload Card -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <h5 class="fw-bold mb-0">Bulk Import</h5>
                </div>
                <div class="card-body px-4">
                    <p class="text-muted small">Upload a CSV file containing a list of ITS IDs to register them all at once to a specific party.</p>
                    <form method="post" enctype="multipart/form-data" class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold">Target Party</label>
                            <select name="party_id" class="form-select" required>
                                <option value="">Select party for import...</option>
                                <?php foreach ($parties as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= h($p['party_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">CSV Data File</label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" name="bulk_upload" class="btn btn-success w-100 rounded-pill">
                                <i class="bi bi-cloud-arrow-up me-2"></i> Process Bulk Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Registered Participants</h5>
                    <div class="small text-muted"><?= count($farzando) ?> Members Found</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase">Member</th>
                                <th class="py-3 small text-muted text-uppercase">Assigned Party</th>
                                <th class="py-3 small text-muted text-uppercase">Jamaat</th>
                                <th class="py-3 small text-muted text-uppercase">Method</th>
                                <th class="px-4 py-3 small text-muted text-uppercase text-end">Added By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($farzando as $f): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= user_photo_url($f['its_id']) ?>" class="rounded-circle border" width="40" height="40">
                                            <div>
                                                <div class="fw-bold text-dark"><?= h($f['full_name']) ?></div>
                                                <div class="text-muted small" style="font-size: 0.75rem;">ITS: <?= $f['its_id'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="fw-semibold text-primary"><?= h($f['party_name'] ?? 'N/A') ?></span>
                                    </td>
                                    <td class="py-3 small text-secondary">
                                        <?= h($f['jamaat']) ?>
                                    </td>
                                    <td class="py-3">
                                        <?php if ($f['is_bulk_upload']): ?>
                                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2">Bulk</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2">Manual</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="small fw-bold text-dark"><?= h($f['submitted_by'] ?? 'System') ?></div>
                                        <div class="text-muted small" style="font-size: 0.7rem;"><?= date('d M, Y', strtotime($f['added_ts'])) ?></div>
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
require_once __DIR__ . '/inc/footer.php'; 
require_once __DIR__ . '/inc/js-block.php'; 
?>
