<?php
    $current_page = 'report_maaraz';
    require_once __DIR__ . '/../session.php';
    require_once __DIR__ . '/../inc/header.php';

    $res = mysqli_query($mysqli, "
    SELECT m.*, um.fullname AS submitted_by, um.miqaat_mauze
    FROM maaraz m
    LEFT JOIN users_mamureen um ON um.its_id = m.added_its
    ORDER BY m.added_ts DESC");
    $records = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

    $impact_map = [
    'strongly_positive' => 'Strongly Positive',
    'positive'          => 'Positive',
    'neutral'           => 'Neutral',
    'negative'          => 'Negative',
    'strongly_negative' => 'Strongly Negative',
    ];
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Ma'raz Report</h1>
            <p class="text-muted small mb-0">Global log of all Social Media Ma'raz submissions across all jamaats.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo MODULE_PATH ?>admin/bqi_dashboard_export.php?type=maaraz" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Data
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Master Records</h5>
                    <span class="badge bg-light text-primary border rounded-pill px-3"><?php echo count($records) ?> Reports</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Submitted By</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Location</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Ideal Scale</th>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $r):
                                    $rid   = (int) $r['id'];
                                    $fres  = mysqli_query($mysqli, "SELECT * FROM bqi_file_attachments WHERE module='maaraz' AND record_id=$rid");
                                    $files = $fres ? $fres->fetch_all(MYSQLI_ASSOC) : [];
                            ?>
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="small fw-bold text-dark"><?php echo h($r['submitted_by'] ?? 'N/A') ?></div>
                                    <div class="text-muted" style="font-size: 0.65rem;"><?php echo date('d M, Y', strtotime($r['added_ts'])) ?></div>
                                </td>
                                <td class="py-3">
                                    <div class="small fw-semibold text-dark"><?php echo h($r['miqaat_mauze'] ?? $r['jamaat'] ?? '') ?></div>
                                </td>
                                <td class="py-3">
                                    <div class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3"><?php echo (int) $r['ideal_model_scale'] ?>%</div>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#viewModal"
                                        data-from="<?php echo h($r['attendance_from'] ?? '') ?>"
                                        data-to="<?php echo h($r['attendance_to'] ?? '') ?>"
                                        data-duration="<?php echo (int) $r['duration_per_day'] ?>"
                                        data-scale="<?php echo (int) $r['ideal_model_scale'] ?>"
                                        data-prep="<?php echo h($r['prep_hours'] ?? '') ?>"
                                        data-avg="<?php echo (int) $r['avg_visitor_minutes'] ?>"
                                        data-tafheem="<?php echo htmlspecialchars($r['tafheem_text'] ?? '', ENT_QUOTES) ?>"
                                        data-engagement="<?php echo htmlspecialchars($r['engagement_strategy'] ?? '', ENT_QUOTES) ?>"
                                        data-takeaways="<?php echo htmlspecialchars($r['key_takeaways'] ?? '', ENT_QUOTES) ?>"
                                        data-impact="<?php echo h($impact_map[$r['feedback_impact']] ?? 'N/A') ?>"
                                        data-additional="<?php echo htmlspecialchars($r['additional_info'] ?? '', ENT_QUOTES) ?>"
                                        data-submittedby="<?php echo htmlspecialchars($r['submitted_by'] ?? 'N/A', ENT_QUOTES) ?>"
                                        data-location="<?php echo htmlspecialchars($r['miqaat_mauze'] ?? $r['jamaat'] ?? '', ENT_QUOTES) ?>"
                                        data-date="<?php echo date('d M, Y', strtotime($r['added_ts'])) ?>"
                                        data-files='<?php echo htmlspecialchars(json_encode(array_map(fn($f) => ['path' => $f['file_path'] ?? '', 'type' => $f['file_type'] ?? '', 'name' => $f['file_name'] ?? ''], $files)) ?: '[]', ENT_QUOTES) ?>'>
                                        <i class="bi bi-eye"></i> View
                                    </button>
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

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true"
     data-base-path="<?php echo MODULE_PATH ?>">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="viewModalLabel">Ma'raz Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="p-3 rounded-3 bg-light h-100">
                            <div class="small text-muted mb-1">Jamaat / Location</div>
                            <div class="fw-semibold" id="m-location"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-3 bg-light h-100">
                            <div class="small text-muted mb-1">Submitted By</div>
                            <div class="fw-semibold" id="m-submittedby"></div>
                            <div class="text-muted small" id="m-date"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-3 bg-light h-100">
                            <div class="small text-muted mb-1">Impact</div>
                            <div class="fw-semibold" id="m-impact"></div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 text-center">
                            <div class="small text-muted mb-1">Dates</div>
                            <div class="small fw-semibold" id="m-dates"></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 text-center">
                            <div class="small text-muted mb-1">Duration/Day</div>
                            <div class="fw-bold text-primary" id="m-duration"></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 text-center">
                            <div class="small text-muted mb-1">Ideal Scale</div>
                            <div class="fw-bold text-primary" id="m-scale"></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 text-center">
                            <div class="small text-muted mb-1">Prep / Avg Visit</div>
                            <div class="fw-bold text-primary" id="m-prep-avg"></div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="small fw-semibold text-uppercase text-muted mb-1">Tafheem Done</div>
                    <div class="bg-light rounded-3 p-3 small" id="m-tafheem" style="white-space:pre-wrap;"></div>
                </div>
                <div class="mb-3">
                    <div class="small fw-semibold text-uppercase text-muted mb-1">Engagement Strategy</div>
                    <div class="bg-light rounded-3 p-3 small" id="m-engagement" style="white-space:pre-wrap;"></div>
                </div>
                <div class="mb-3">
                    <div class="small fw-semibold text-uppercase text-muted mb-1">Key Takeaways</div>
                    <div class="bg-light rounded-3 p-3 small" id="m-takeaways" style="white-space:pre-wrap;"></div>
                </div>
                <div id="m-additional-wrap" class="mb-3">
                    <div class="small fw-semibold text-uppercase text-muted mb-1">Additional Info</div>
                    <div class="bg-light rounded-3 p-3 small" id="m-additional" style="white-space:pre-wrap;"></div>
                </div>
                <div id="m-files-wrap" class="mb-1">
                    <div class="small fw-semibold text-uppercase text-muted mb-2">Attachments</div>
                    <div id="m-files-list" class="d-flex flex-wrap gap-2"></div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
    require_once __DIR__ . '/../inc/footer.php';
    require_once __DIR__ . '/../inc/js-block.php';
?>
<script src="<?php echo MODULE_PATH ?>assets/js/report_maaraz.js?v=1.0"></script>
