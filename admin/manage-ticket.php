<?php
$current_page = 'manage-ticket';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../inc/header.php';

$tickets = $db->fetchAll("SELECT * FROM `manage_tickets` ORDER BY id DESC");
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Global Support Overview</h1>
            <p class="text-muted small mb-0">Monitor and manage all support tickets submitted across the platform.</p>
        </div>
        <div class="badge bg-light text-primary border rounded-pill px-3 py-2">
            Total Tickets: <?= count($tickets) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0">Ticket Logs</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Identity</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Context</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Category</th>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $t): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= user_photo_url($t['its_id']) ?>" class="rounded-circle border" width="36" height="36">
                                            <div>
                                                <div class="fw-bold text-dark small">Target: <?= $t['its_id'] ?></div>
                                                <div class="text-muted" style="font-size: 0.65rem;">Agent: <?= $t['user_its'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2" style="font-size: 0.7rem;"><?= h($t['purpose']) ?></div>
                                    </td>
                                    <td class="py-3 small text-secondary">
                                        <?= h($t['purpose']) ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-muted small truncate" style="max-width: 400px;"><?= h($t['details']) ?></div>
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
