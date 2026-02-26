<?php
$current_page = 'zakereen_parties';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/inc/header.php';

$message = null;

// Handle Add Party using the new DB Class
if (isset($_POST['add_party'])) {
    $party_name = trim($_POST['party_name'] ?? '');
    if (!empty($party_name)) {
        try {
            $db->insert('bqi_zakereen_parties', [
                'party_name' => $party_name,
                'is_active' => 1,
                'added_its' => $user_its
            ]);
            $message = ['text' => 'Party registered successfully!', 'tag' => 'success'];
        } catch (Exception $e) {
            $message = ['text' => 'Error: Duplicate entry or database issue.', 'tag' => 'danger'];
        }
    }
}

// Fetch parties (Senior Refactor: No raw SQL in view if possible, but keeping it here for clarity)
$parties = $db->fetchAll("
    SELECT p.*, um.fullname AS submitted_by 
    FROM `bqi_zakereen_parties` p 
    LEFT JOIN `users_mamureen` um ON um.its_id = p.added_its 
    WHERE p.added_its IN (SELECT its_id FROM users_mamureen WHERE miqaat_mauze = ?)
    ORDER BY p.added_ts DESC
", [$mauze]);
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Zakereen Parties</h1>
            <p class="text-muted small mb-0">Manage and register your local Zakereen parties.</p>
        </div>
        <div>
            <span class="badge bg-light text-primary border px-3 py-2 rounded-pill">
                Total Parties: <?= count($parties) ?>
            </span>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message['tag']; ?> border-0 shadow-sm rounded-4 py-3 px-4 mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-2"></i>
                <div><?= $message['text']; ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Action Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-3">Register Party</h5>
                <form method="post" class="needs-validation">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Party Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-flag"></i></span>
                            <input type="text" name="party_name" class="form-control border-start-0" placeholder="e.g. Al-Fatah Party" required>
                        </div>
                    </div>
                    <button type="submit" name="add_party" class="btn btn-primary w-100 py-2 rounded-pill shadow-sm">
                        Confirm Registration
                    </button>
                </form>

                <hr class="my-4 opacity-50">
                
                <h6 class="fw-bold small text-muted mb-2">Helpful Tip</h6>
                <p class="small text-muted mb-0">Ensure the party name is spelled correctly as it will appear on official certificates and reports.</p>
            </div>
        </div>

        <!-- List Column -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0">Active Records</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Name</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Registrar</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Status</th>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($parties as $p): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-dark"><?= h($p['party_name']) ?></div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">Registered: <?= date('d M, Y', strtotime($p['added_ts'])) ?></div>
                                    </td>
                                    <td class="py-3">
                                        <div class="small fw-semibold text-secondary"><?= h($p['submitted_by'] ?? 'N/A') ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem;"><?= $p['added_its'] ?></div>
                                    </td>
                                    <td class="py-3">
                                        <?php if ($p['is_active']): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm rounded-pill" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3">
                                                <li><a class="dropdown-item small" href="#"><i class="bi bi-pencil me-2"></i> Edit</a></li>
                                                <li><a class="dropdown-item small text-danger" href="#"><i class="bi bi-trash me-2"></i> Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (empty($parties)): ?>
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-inbox h1 d-block opacity-25"></i>
                        No parties registered for this Mauze yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php 
require_once __DIR__ . '/inc/footer.php'; 
require_once __DIR__ . '/inc/js-block.php'; 
?>
