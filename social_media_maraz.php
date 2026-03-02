<?php
    $current_page = 'social_media_maraz';
    require_once __DIR__ . '/session.php';
    require_once __DIR__ . '/inc/header.php';

    $message = null;

    $impact_options = [
    'strongly_positive' => 'Strongly / Positive Impact',
    'positive'          => 'Positive Impact',
    'neutral'           => 'Neutral / Minimal Impact',
    'negative'          => 'Negative Impact',
    'strongly_negative' => 'Strongly / Negative Impact',
    ];

    $impact_badges = [
    'strongly_positive' => 'success',
    'positive'          => 'primary',
    'neutral'           => 'secondary',
    'negative'          => 'warning',
    'strongly_negative' => 'danger',
    ];
    /**
     * Submit Report
     */
    if (isset($_POST['submit_maraz'])) {
    try {
        $record_id = $db->insert('bqi_social_media_maraz', [
            'compliance_pct'       => max(0, min(100, (int) ($_POST['compliance_pct'] ?? 0))),
            'prep_time_hours'      => max(0, (float) ($_POST['prep_time_hours'] ?? 0)),
            'maraz_from_date'      => ! empty($_POST['maraz_from_date']) ? $_POST['maraz_from_date'] : null,
            'maraz_to_date'        => ! empty($_POST['maraz_to_date']) ? $_POST['maraz_to_date'] : null,
            'duration_per_day_min' => ! empty($_POST['duration_per_day_min']) ? (int) $_POST['duration_per_day_min'] : null,
            'time_spent_avg_min'   => ! empty($_POST['time_spent_avg_min']) ? (int) $_POST['time_spent_avg_min'] : null,
            'tafheem_details'      => $_POST['tafheem_details'] ?? null,
            'engagement_strategy'  => $_POST['engagement_strategy'] ?? null,
            'key_takeaways'        => $_POST['key_takeaways'] ?? null,
            'impact_rating'        => $_POST['impact_rating'] ?? null,
            'additional_info'      => $_POST['additional_info'] ?? null,
            'jamaat'               => $mauze,
            'added_its'            => $user_its,
        ]);

        // Photo uploads
        if (! empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['name'] as $i => $name) {
                if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext     = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $newName = $user_its . '_maraz_' . date('YmdHis') . '_' . $i . '.' . $ext;
                    if (move_uploaded_file($_FILES['photos']['tmp_name'][$i], __DIR__ . '/user_uploads/' . $newName)) {
                        $db->insert('bqi_file_attachments', [
                            'module'    => 'social_media_maraz',
                            'record_id' => $record_id,
                            'file_name' => $name,
                            'file_path' => $newName,
                            'file_type' => $ext,
                            'file_size' => $_FILES['photos']['size'][$i],
                            'added_its' => $user_its,
                        ]);
                    }
                }
            }
        }

        $message = ['text' => "Ma'raz report submit successfully!", 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
    }

    // Fetch history
    $reports = [];
    try {
    $reports = $db->fetchAll("
        SELECT m.*, um.fullname AS submitted_by
        FROM bqi_social_media_maraz m
        LEFT JOIN users_mamureen um ON um.its_id = m.added_its
        WHERE m.added_its IN (SELECT its_id FROM users_mamureen WHERE miqaat_mauze = ?)
        ORDER BY m.added_ts DESC", [$mauze]);
    } catch (Exception $e) {
    $message = ['text' => 'DB Error: ' . $e->getMessage() . ' — Please import the SQL table first.', 'tag' => 'danger'];
    }
?>

<style>
.progress { height: 25px; }
.impact-option { cursor: pointer; transition: background 0.15s; }
.impact-option:hover { background: #f0f4ff !important; }
.impact-option input[type="radio"]:checked + label { color: #0d6efd; }
.draft-banner { border-left: 4px solid #ffc107; }
#complianceDisplay { min-width: 56px; text-align: center; font-size: 1rem; }
</style>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Social Media Ma'raz</h1>
            <p class="text-muted small mb-0">Aapna Mauze ma Ma'raz tamaam thai gayu hoi – toj aa report fill karwu.</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message['tag']; ?> border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-info-circle-fill me-2"></i> <?php echo h($message['text']); ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- ── Form Card ─────────────────────────────────────────────── -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-4">Submit Report</h5>

                <!-- Draft Banner -->
                <div id="draftBanner" class="alert alert-warning draft-banner border-0 rounded-3 p-2 small d-flex align-items-center justify-content-between mb-3 d-none">
                    <span><i class="bi bi-clock-history me-1"></i> Aapna draft restore thayo. Review karine submit karo.</span>
                    <button type="button" id="clearDraftBtn" class="btn btn-sm btn-outline-warning rounded-pill py-0 ms-2">Clear</button>
                </div>

                <form method="post" enctype="multipart/form-data" id="uploadForm" data-user-its="<?php echo (int)$user_its ?>">

                    <!-- 1. Compliance Slider -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Ma'raz organising ma tawjehaat kitni had follow thayi?</label>
                        <div class="d-flex align-items-center gap-3 mb-1">
                            <input type="range" class="form-range flex-grow-1" name="compliance_pct" id="compliance_pct" min="0" max="100" step="10" value="50">
                            <span class="badge bg-primary px-3 py-2" id="complianceDisplay">50%</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted" style="font-size: 0.7rem; padding: 0 2px;">
                            <span>0% — Bilkul nahi</span>
                            <span>50%</span>
                            <span>100% — Puri tarah</span>
                        </div>
                    </div>

                    <!-- 2. Prep Time -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Ma'raz ni tayyari waste jumlatan kitno waqt sarf thayo?</label>
                        <div class="input-group">
                            <input type="number" name="prep_time_hours" id="prep_time_hours" class="form-control" min="0" step="0.5" placeholder="0">
                            <span class="input-group-text">Hours</span>
                        </div>
                    </div>

                    <!-- 3 & 4. Date Range -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Ma'raz kai tarikh shuru ane tamaam thayu?</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="input-group">
                                    <span class="input-group-text small">From</span>
                                    <input type="date" name="maraz_from_date" id="maraz_from_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="input-group">
                                    <span class="input-group-text small">To</span>
                                    <input type="date" name="maraz_to_date" id="maraz_to_date" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Duration per day -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Ma'raz roz nu duration</label>
                        <div class="input-group">
                            <input type="number" name="duration_per_day_min" id="duration_per_day_min" class="form-control" min="0" placeholder="0">
                            <span class="input-group-text">Minutes / day</span>
                        </div>
                    </div>

                    <!-- 5. Time Spent Avg -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Ma'raz ma awnaar shuru si akhir lag average kitno waqt sarf thayo?</label>
                        <div class="input-group">
                            <input type="number" name="time_spent_avg_min" id="time_spent_avg_min" class="form-control" min="0" placeholder="0">
                            <span class="input-group-text">Minutes (avg)</span>
                        </div>
                    </div>

                    <!-- 6. Tafheem -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Ma'raz ma tafheem koiye kidi?</label>
                        <textarea name="tafheem_details" id="tafheem_details" class="form-control" rows="3" placeholder="Ma'raz ma tafheem ni details..."></textarea>
                    </div>

                    <!-- 7. Engagement Strategy -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Engaged rakhe waste su strategy ya tools istemaal karwa ma aya?</label>
                        <textarea name="engagement_strategy" id="engagement_strategy" class="form-control" rows="3" placeholder="Strategies and tools used..."></textarea>
                    </div>

                    <!-- 8. Key Takeaways -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Mumineen ne kaya ehem takeaways mila?</label>
                        <textarea name="key_takeaways" id="key_takeaways" class="form-control" rows="3" placeholder="Key takeaways from the Ma'raz..."></textarea>
                    </div>

                    <!-- 9. Impact Rating -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Mumineen feedback na asaas par – Ma'raz e kai tarah impact kidu?</label>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($impact_options as $val => $label): ?>
                            <div class="form-check impact-option px-3 py-2 rounded-3 border bg-white">
                                <input class="form-check-input" type="radio" name="impact_rating" id="impact_<?php echo $val ?>" value="<?php echo $val ?>">
                                <label class="form-check-label small fw-semibold w-100" for="impact_<?php echo $val ?>"><?php echo h($label) ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 10. Photos -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Ma'raz ni tasaveer upload karwu</label>
                        <input type="file" name="photos[]" class="form-control" multiple accept="image/*">
                        <div class="form-text text-muted">JPEG / PNG – max 5 MB per photo</div>
                    </div>

                    <!-- 11. Additional Info -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Ma'raz mutalliq mazeed koi information</label>
                        <textarea name="additional_info" id="additional_info" class="form-control" rows="2" placeholder="Any additional information..."></textarea>
                        <div class="form-text text-muted mt-1">
                            <i class="bi bi-info-circle me-1"></i>Charts, promotional material ya video waaste <strong>Khidmat Preparations</strong> tab ma upload karwu.
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div id="progressContainer" class="d-none mb-3">
                        <div class="progress">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        <div id="uploadMessage" class="text-center small mt-1 text-primary d-none"></div>
                    </div>

                    <!-- Warning -->
                    <div class="alert alert-warning border-0 rounded-3 p-2 small mb-3">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Submit karta pehla puro report review karo. Submit thaya baad edit nahi thashey.
                    </div>

                    <button type="submit" id="submitBtn" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">
                        <i class="bi bi-send-fill me-2"></i>Submit Ma'raz Report
                    </button>

                </form>
            </div>
        </div>

        <!-- ── History Card ─────────────────────────────────────────── -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0">Ma'raz History</h5>
                </div>

                <?php if (empty($reports)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-journal-x fs-1 opacity-25 d-block mb-2"></i>
                        <p class="small">Abhi koi report nahi hai.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="px-4 py-3 small text-muted text-uppercase border-0">Dates</th>
                                    <th class="py-3 small text-muted text-uppercase border-0">Compliance</th>
                                    <th class="py-3 small text-muted text-uppercase border-0">Impact</th>
                                    <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $r): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <?php if ($r['maraz_from_date']): ?>
                                            <div class="small fw-bold"><?php echo date('d M', strtotime($r['maraz_from_date'])) ?></div>
                                            <div class="text-muted" style="font-size: 0.7rem;">to <?php echo date('d M Y', strtotime($r['maraz_to_date'])) ?></div>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold"><?php echo (int)$r['compliance_pct'] ?>%</span>
                                    </td>
                                    <td class="py-3">
                                        <?php
                                            $ir    = $r['impact_rating'] ?? '';
                                            $badge = $impact_badges[$ir] ?? 'secondary';
                                            $label = $impact_options[$ir] ?? '—';
                                        ?>
                                        <span class="badge bg-<?php echo $badge ?> bg-opacity-15 text-<?php echo $badge ?>" style="font-size: 0.7rem;"><?php echo h($label) ?></span>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="small fw-bold"><?php echo date('d M, Y', strtotime($r['added_ts'])) ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem;">By: <?php echo h($r['submitted_by'] ?? 'System') ?></div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
<script src="assets/js/social_media_maraz.js"></script>
