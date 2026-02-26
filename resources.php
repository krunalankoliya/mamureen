<?php
$current_page = 'resources';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/inc/header.php';

// Fetch Downloads with check for user logs
$downloads = $db->fetchAll("
    SELECT my.*, 
    (SELECT dl.download_date FROM download_log dl WHERE dl.download_id = my.id AND dl.its_id = ? LIMIT 1) as download_date
    FROM my_downloads my 
    ORDER BY my.id DESC", [$user_its]);

$links = $db->fetchAll("SELECT * FROM `my_links` ORDER BY `upload_date` DESC");
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Knowledge Center</h1>
            <p class="text-muted small mb-0">Access official documents, guidelines, and important resource links.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Downloads Column -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Available Downloads</h5>
                    <i class="bi bi-cloud-download text-primary h5 mb-0"></i>
                </div>
                <div class="card-body px-0">
                    <div class="list-group list-group-flush">
                        <?php if (empty($downloads)): ?>
                            <div class="p-5 text-center text-muted">No downloads available at this time.</div>
                        <?php else: ?>
                            <?php foreach ($downloads as $res): 
                                $is_new = (time() - strtotime($res['upload_date'])) < (72 * 3600);
                                $is_downloaded = !empty($res['download_date']);
                            ?>
                                <div class="list-group-item px-4 py-3 border-0 border-bottom">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-light p-2 rounded-3 text-primary">
                                            <i class="bi bi-file-earmark-text h4 mb-0"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2">
                                                <h6 class="fw-bold mb-0 text-dark"><?= h($res['title']) ?></h6>
                                                <?php if($is_new): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill" style="font-size: 0.6rem;">NEW</span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted">
                                                Added: <?= date('d M, Y', strtotime($res['upload_date'])) ?>
                                                <?php if($is_downloaded): ?>
                                                    • <span class="text-success"><i class="bi bi-check-all"></i> Downloaded on <?= date('d/m/y', strtotime($res['download_date'])) ?></span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <a href="download_log.php?id=<?= $res['id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Links Column -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">External Links</h5>
                    <i class="bi bi-link-45deg text-primary h5 mb-0"></i>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="d-flex flex-column gap-3">
                        <?php if (empty($links)): ?>
                            <p class="text-muted small text-center">No links registered.</p>
                        <?php else: ?>
                            <?php foreach ($links as $link): ?>
                                <a href="<?= h($link['link']) ?>" target="_blank" class="d-flex align-items-center gap-3 p-3 rounded-4 bg-light text-decoration-none border hover-shadow transition-all">
                                    <div class="bg-white p-2 rounded shadow-sm text-primary">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark small"><?= h($link['title']) ?></div>
                                        <div class="text-muted truncate" style="font-size: 0.65rem; max-width: 200px;"><?= h($link['link']) ?></div>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted small"></i>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card border-0 bg-primary text-white p-4 shadow-sm" style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-white bg-opacity-20 p-2 rounded-3">
                        <i class="bi bi-info-circle h4 mb-0"></i>
                    </div>
                    <h6 class="fw-bold mb-0">Usage Guidance</h6>
                </div>
                <p class="small opacity-75 mb-0">All resources are intended for official use. Please ensure you have the latest version of guidelines before submitting reports.</p>
            </div>
        </div>
    </div>
</main>

<?php 
require_once __DIR__ . '/inc/footer.php'; 
require_once __DIR__ . '/inc/js-block.php'; 
?>
