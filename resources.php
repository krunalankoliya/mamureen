<?php
require_once(__DIR__ . '/session.php');

$current_page = 'resources';
require_once(__DIR__ . '/inc/header.php');
$its_id = (int)$_SESSION[USER_ITS];

$query = "SELECT
    my.id,
    my.upload_date,
    my.title,
    my.file_name,
    my.dot_color,
    dl.its_id,
    dl.id AS download_log_id,
    dl.download_id,
    dl.download_date,
    CASE
        WHEN dl.id IS NOT NULL THEN 1
        ELSE 0
    END AS is_downloaded
FROM my_downloads my
LEFT JOIN download_log dl
    ON my.id = dl.download_id
    AND dl.its_id = $its_id GROUP BY `my`.`id` ORDER BY `my`.`id` DESC";
$result = mysqli_query($mysqli, $query);
$data = $result->fetch_all(MYSQLI_ASSOC);

$linkQuery = "SELECT * FROM `my_links` ORDER BY `upload_date` DESC";
$linkResult = mysqli_query($mysqli, $linkQuery);
$linkData = $linkResult->fetch_all(MYSQLI_ASSOC);

// Tab labels per dot_color
$tabLabels = [
    'text-success'   => 'Social Media',
    'text-primary'   => 'Blue',
    'text-danger'    => 'Shaadi Tafheem',
    'text-warning'   => 'General',
    'text-info'      => 'Zakereen',
    'text-dark'      => 'Dark',
    'text-secondary' => 'Grey',
];

// Collect distinct colors that actually exist in the data
$usedColors = array_unique(array_column($data, 'dot_color'));

// File type badge from extension
$extBadge = [
    'pdf'  => 'PDF',
    'doc'  => 'DOC', 'docx' => 'DOC',
    'xls'  => 'XLS', 'xlsx' => 'XLS',
    'ppt'  => 'PPT', 'pptx' => 'PPT',
    'zip'  => 'ZIP', 'rar'  => 'RAR',
    'mp4'  => 'MP4', 'avi'  => 'AVI', 'mov' => 'MOV',
    'mp3'  => 'MP3', 'wav'  => 'WAV',
    'jpg'  => 'IMG', 'jpeg' => 'IMG', 'png' => 'IMG', 'gif' => 'IMG',
];
?>

<style>
/* ── Filter tabs ── */
.dl-tabs {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    flex-wrap: wrap;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 1rem;
}
.dl-tab {
    background: none;
    border: none;
    padding: 0.3rem 1rem;
    border-radius: 20px;
    font-size: 0.88rem;
    font-weight: 500;
    color: #555;
    cursor: pointer;
    transition: background .15s, color .15s;
}
.dl-tab.active { background: #1e3a8a; color: #fff; }
.dl-tab:hover:not(.active) { background: #eef2ff; color: #1e3a8a; }

/* ── Download item card ── */
.dl-item {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    padding: 0.75rem 1rem;
    border: 1px solid #e5e9f0;
    border-radius: 10px;
    margin-bottom: 0.55rem;
    background: #fff;
    text-decoration: none;
    color: inherit;
    transition: box-shadow .15s, border-color .15s;
}
.dl-item:hover {
    box-shadow: 0 3px 14px rgba(0,0,0,0.09);
    text-decoration: none;
    color: inherit;
}
.dl-item.is-new  { border-color: #1e3a8a; border-width: 1.5px; }
.dl-item.is-done { opacity: 0.65; }

/* ── File type badge ── */
.dl-badge {
    min-width: 48px;
    height: 48px;
    background: #eef2ff;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 700;
    color: #1e3a8a;
    letter-spacing: 0.4px;
    flex-shrink: 0;
}
.dl-badge.done-badge { background: #f1f3f5; color: #888; }

/* ── Item text ── */
.dl-item-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1e3a8a;
    margin: 0 0 2px;
    line-height: 1.3;
}
.dl-item.is-done .dl-item-title { color: #555; font-weight: 400; }
.dl-item-date { font-size: 0.76rem; color: #9aa3b0; margin: 0; }

/* ── Links ── */
.link-item {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.65rem 0.9rem;
    border: 1px solid #e5e9f0;
    border-radius: 10px;
    margin-bottom: 0.5rem;
    background: #fff;
    text-decoration: none;
    color: #333;
    font-size: 0.88rem;
    transition: background .15s, border-color .15s;
}
.link-item:hover { background: #eef2ff; border-color: #a5b4fc; color: #1e3a8a; text-decoration: none; }
.link-item .arr { margin-left: auto; font-size: 0.72rem; color: #adb5bd; }
</style>

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Resources and Downloadables</h1>
    </div>

    <section class="section dashboard">
        <div class="row">

            <!-- Downloads -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">

                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                            <h5 class="mb-0 fw-bold" style="color:#1e3a8a">Downloads</h5>
                            <input type="text" id="dlSearch" class="form-control form-control-sm"
                                   style="max-width:200px;border-radius:20px"
                                   placeholder="Search resources...">
                        </div>

                        <!-- Filter tabs -->
                        <?php if (!empty($usedColors)): ?>
                        <div class="dl-tabs">
                            <button class="dl-tab active" data-filter="all">All</button>
                            <?php foreach ($usedColors as $color):
                                $label = $tabLabels[$color] ?? ucfirst(str_replace('text-', '', $color));
                            ?>
                            <button class="dl-tab" data-filter="<?= htmlspecialchars($color) ?>">
                                <?= $label ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- List -->
                        <div id="dlList">
                        <?php if (empty($data)): ?>
                            <p class="text-muted">No downloads available.</p>
                        <?php else: ?>
                            <?php foreach ($data as $resource):
                                $isNew        = (time() - strtotime($resource['upload_date'])) < (48 * 60 * 60);
                                $isDownloaded = $resource['is_downloaded'] == 1;
                                $ext          = strtolower(pathinfo($resource['file_name'], PATHINFO_EXTENSION));
                                $badge        = $extBadge[$ext] ?? (strtoupper($ext) ?: 'FILE');
                                $dotColor     = $resource['dot_color'] ?? '';

                                if ($isDownloaded) {
                                    $dateLabel = 'Downloaded ' . date('d/m/y', strtotime($resource['download_date']));
                                    $itemClass = 'is-done';
                                } elseif ($isNew) {
                                    $dateLabel = 'Updated: ' . date('d/m/y', strtotime($resource['upload_date'])) . ' &middot; New';
                                    $itemClass = 'is-new';
                                } else {
                                    $dateLabel = 'Updated: ' . date('d/m/y', strtotime($resource['upload_date']));
                                    $itemClass = 'is-new';
                                }
                            ?>
                            <a href="download_log.php?id=<?= $resource['id'] ?>"
                               class="dl-item <?= $itemClass ?>"
                               data-color="<?= htmlspecialchars($dotColor) ?>"
                               data-title="<?= strtolower(htmlspecialchars($resource['title'])) ?>">
                                <div class="dl-badge <?= $isDownloaded ? 'done-badge' : '' ?>">
                                    <?= $badge ?>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="dl-item-title"><?= htmlspecialchars($resource['title']) ?></p>
                                    <p class="dl-item-date"><?= $dateLabel ?></p>
                                </div>
                                <?php if ($isDownloaded): ?>
                                <i class="bi bi-check2-circle text-success ms-auto flex-shrink-0 fs-5"></i>
                                <?php else: ?>
                                <i class="bi bi-download text-primary ms-auto flex-shrink-0 fs-5"></i>
                                <?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </div>

                        <p id="dlNoResults" class="text-muted small d-none mt-2">No results found.</p>

                    </div>
                </div>
            </div>

            <!-- Links -->
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3 fw-bold" style="color:#1e3a8a">Links</h5>
                        <?php if (empty($linkData)): ?>
                            <p class="text-muted">No links available.</p>
                        <?php else: ?>
                            <?php foreach ($linkData as $link): ?>
                            <a target="_blank" href="<?= htmlspecialchars($link['link']) ?>" class="link-item">
                                <i class="bx bx-link-external text-primary fs-5"></i>
                                <span><?= htmlspecialchars($link['title']) ?></span>
                                <i class="bi bi-box-arrow-up-right arr"></i>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </section>

</main><!-- End #main -->

<script src="<?= MODULE_PATH ?>assets/js/resources.js"></script>

<?php require_once(__DIR__ . '/inc/footer.php'); ?>
