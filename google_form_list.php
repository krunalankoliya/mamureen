<?php
$current_page = 'google_form_list';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/inc/header.php';

// Fetch available forms
$forms = $db->fetchAll("SELECT * FROM `google_forms` ORDER BY id DESC");

$selectedForm = null;
if (isset($_GET['id'])) {
    $selectedForm = $db->fetch("SELECT * FROM `google_forms` WHERE id = ?", [(int)$_GET['id']]);
}
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Daily Reporting</h1>
            <p class="text-muted small mb-0">Fill out and submit your periodic activity reports via Google Forms.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Forms List -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white border-bottom-0 py-3 px-4">
                    <h6 class="fw-bold mb-0">Available Forms</h6>
                </div>
                <div class="list-group list-group-flush">
                    <?php if (empty($forms)): ?>
                        <div class="p-4 text-center text-muted small">No forms configured yet.</div>
                    <?php else: ?>
                        <?php foreach ($forms as $f): 
                            $isActive = (isset($_GET['id']) && $_GET['id'] == $f['id']);
                        ?>
                            <a href="?id=<?= $f['id'] ?>" class="list-group-item list-group-item-action border-0 px-4 py-3 <?= $isActive ? 'active' : '' ?>">
                                <div class="fw-bold small mb-1"><?= h($f['form_title']) ?></div>
                                <div class="<?= $isActive ? 'text-white-50' : 'text-muted' ?>" style="font-size: 0.65rem;">Added: <?= date('d M, Y', strtotime($f['upload_date'])) ?></div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Form Viewer -->
        <div class="col-lg-8">
            <?php if ($selectedForm): 
                $link = $selectedForm['form_link'];
                $link = str_replace(['ITS_ID', 'MAUZE', 'NAME', 'JAMIAT'], [$user_its, $mauze, $fullname, $jamiat], $link);
                
                // Embedded conversion
                if (strpos($link, 'viewform') !== false && strpos($link, 'embedded=true') === false) {
                    $link = str_replace('viewform', 'viewform?embedded=true', $link);
                }
            ?>
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-primary text-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0"><?= h($selectedForm['form_title']) ?></h6>
                        <a href="<?= h($link) ?>" target="_blank" class="btn btn-sm btn-light rounded-pill px-3 fw-bold" style="font-size: 0.7rem;">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Open in New Tab
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="ratio ratio-16x9" style="min-height: 700px;">
                            <iframe src="<?= h($link) ?>" frameborder="0" marginheight="0" marginwidth="0">Loading...</iframe>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm p-5 text-center bg-light bg-opacity-50">
                    <div class="mb-3">
                        <i class="bi bi-file-earmark-text text-muted opacity-25" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="fw-bold text-muted">Select a form to begin</h5>
                    <p class="small text-muted mb-0">Choose a form from the left sidebar to view and fill it out.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php 
require_once __DIR__ . '/inc/footer.php'; 
require_once __DIR__ . '/inc/js-block.php'; 
?>
