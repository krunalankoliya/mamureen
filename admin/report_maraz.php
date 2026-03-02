<?php
    require_once __DIR__ . '/../session.php';
    $current_page = 'report_maraz';
    require_once __DIR__ . '/../inc/header.php';

    // Fetch all records
    $result = mysqli_query($mysqli, "SELECT m.*, um.fullname AS submitted_by
    FROM `maaraz` m
    LEFT JOIN `users_mamureen` um ON um.its_id = m.added_its
    ORDER BY m.id DESC");
    $records = $result->fetch_all(MYSQLI_ASSOC);

    // Pre-fetch all attachments
    $attachments = [];
    $fa_res      = mysqli_query($mysqli, "SELECT * FROM `bqi_file_attachments` WHERE module = 'maaraz' ORDER BY id");
    if ($fa_res) {
    while ($fa = $fa_res->fetch_assoc()) {
        $attachments[(int) $fa['record_id']][] = $fa;
    }
    }

    $impact_labels = [
    'strongly_positive' => 'Strongly Positive',
    'positive'          => 'Positive',
    'neutral'           => 'Neutral / Minimal',
    'negative'          => 'Negative',
    'strongly_negative' => 'Strongly Negative',
    ];
?>
<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        Ma'raz Report
                        <span class="badge bg-secondary ms-2"><?php echo count($records) ?> records</span>
                    </h5>
                    <div id="app-config" data-base-url="<?php echo htmlspecialchars(MODULE_PATH) ?>"></div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Jamaat</th>
                                    <th data-full="Q3 — Mumineen — Ma'raz ma kiwar si kiwar lag hazir thata? (Attendance dates &amp; duration per day)">Attendance Dates</th>
                                    <th data-full="Q1 — Ma'raz waaste je tawjehaat ane ideal models mokalwa ma aya — ye raah par kitni had lag Ma'raz organise karwu imkaan thayu?">Ideal Scale</th>
                                    <th data-full="Q2 — Ma'raz ni tayyari waste jumlatan kitno waqt sarf thayo?">Prep (hrs)</th>
                                    <th data-full="Q4 — Ma'raz ma awnaar shuru si akhir lag kitno waqt sarf karta? (on average)">Avg Visit (min)</th>
                                    <th data-full="Q8 — Mumineen nu feedback na asaas par — aa Ma'raz ye kai tarah impact kidu?">Impact</th>
                                    <th>Submitted By</th>
                                    <th>Files</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $i => $r):
                                        $impact_label = $impact_labels[$r['feedback_impact']] ?? ($r['feedback_impact'] ?? '—');

                                        // Files
                                        $files_arr = [];
                                        foreach ($attachments[(int) $r['id']] ?? [] as $fa) {
                                            $files_arr[] = ['path' => $fa['file_path'], 'type' => $fa['file_type'], 'name' => $fa['file_name']];
                                        }
                                        $file_count = count($files_arr);

                                        // Date range
                                        $date_from  = ! empty($r['attendance_from']) ? date('d-M-Y', strtotime($r['attendance_from'])) : '';
                                        $date_to    = ! empty($r['attendance_to']) ? date('d-M-Y', strtotime($r['attendance_to'])) : '';
                                        $date_range = $date_from . ($date_to ? ' – ' . $date_to : '');

                                        // Fields JSON — question labels from maaraz.php form
                                        $fields_arr = [
                                            ['label' => 'Jamaat',
                                                'value'  => $r['jamaat'] ?? ''],

                                            ['label' => "Q1 — Ma'raz waaste je tawjehaat ane ideal models mokalwa ma aya — ye raah par kitni had lag Ma'raz organise karwu imkaan thayu?",
                                                'value'  => $r['ideal_model_scale'] . '%'],

                                            ['label' => "Q2 — Ma'raz ni tayyari waste jumlatan kitno waqt sarf thayo?",
                                                'value'  => $r['prep_hours'] . ' hrs'],

                                            ['label' => "Q3 — Mumineen — Ma'raz ma kiwar si kiwar lag hazir thata?",
                                                'value'  => $date_from . ' – ' . $date_to . ' | ' . $r['duration_per_day'] . ' mins/day'],

                                            ['label' => "Q4 — Ma'raz ma awnaar shuru si akhir lag kitno waqt sarf karta? (on average)",
                                                'value'  => $r['avg_visitor_minutes'] . ' minutes'],

                                            ['label' => "Q5 — Ma'raz ma tafheem koiye kidi?",
                                                'value'  => $r['tafheem_text'] ?? ''],

                                            ['label' => "Q6 — Awnaar engaged rahe ane faido hasil kare ye waste su strategy ya tools istemaal karwa ma aya?",
                                                'value'  => $r['engagement_strategy'] ?? ''],

                                            ['label' => "Q7 — Ma'raz ma awnaar si Mumin ne kaya ehem takeaways mila?",
                                                'value'  => $r['key_takeaways'] ?? ''],

                                            ['label' => "Q8 — Mumineen nu feedback na asaas par — aa Ma'raz ye kai tarah impact kidu?",
                                                'value'  => $impact_label],

                                            ['label' => "Q10 — Ma'raz mutalliq mazeed koi information share karwi hoi to yaha likhwu:",
                                                'value'  => $r['additional_info'] ?? ''],

                                            ['label' => 'Submitted By',
                                                'value'  => ($r['submitted_by'] ?? '') . ' (' . $r['added_its'] . ')'],

                                            ['label' => 'Submitted On',
                                                'value'  => date('d-M-Y H:i', strtotime($r['added_ts']))],
                                        ];

                                        $fields_json = htmlspecialchars(json_encode($fields_arr, JSON_UNESCAPED_UNICODE), ENT_QUOTES);
                                        $files_json  = htmlspecialchars(json_encode($files_arr, JSON_UNESCAPED_UNICODE), ENT_QUOTES);
                                ?>
                                    <tr>
                                        <td><?php echo $i + 1 ?></td>
                                        <td><?php echo htmlspecialchars($r['jamaat'] ?? '') ?></td>
                                        <td><?php echo htmlspecialchars($date_range) ?></td>
                                        <td><?php echo $r['ideal_model_scale'] ?>%</td>
                                        <td><?php echo $r['prep_hours'] ?></td>
                                        <td><?php echo $r['avg_visitor_minutes'] ?></td>
                                        <td><?php echo htmlspecialchars($impact_label) ?></td>
                                        <td><?php echo htmlspecialchars($r['submitted_by'] ?? $r['added_its']) ?></td>
                                        <td>
                                            <?php if ($file_count > 0): ?>
                                                <span class="badge bg-info"><?php echo $file_count ?> file<?php echo $file_count > 1 ? 's' : '' ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary view-btn"
                                                data-title="<?php echo htmlspecialchars($r['jamaat'] ?? "Ma'raz") ?>"
                                                data-fields="<?php echo $fields_json ?>"
                                                data-files="<?php echo $files_json ?>"
                                                data-record-id="<?php echo (int)$r['id'] ?>"
                                                data-module="maaraz">
                                                <i class="bi bi-eye"></i>
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
    </section>
</main>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">Record Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-fields"></div>
                <div id="modal-files"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
<script src="<?php echo MODULE_PATH ?>assets/js/admin_reports.js"></script>
<script src="<?php echo MODULE_PATH ?>assets/js/report_maraz.js"></script>
