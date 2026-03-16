<?php
    require_once __DIR__ . '/../session.php';

    mysqli_report(MYSQLI_REPORT_OFF);

    if (! $is_admin) {
    header('Location: ' . MODULE_PATH . 'admin/bqi_dashboard.php');
    exit();
    }

    // ── Load all questions keyed by q_key ─────────────────────────────────────
    $questionsMap = [];
    $qResult      = mysqli_query($mysqli, "SELECT * FROM `closure_report_questions` ORDER BY `section_id`, `id` ASC");
    if ($qResult) {
    while ($q = $qResult->fetch_assoc()) {
        $questionsMap[$q['q_key']] = $q;
    }
    }

    // Hardcoded section structure matching the form flow exactly
    $formSections = [
    [
        'title' => 'معلومات أساسية',
        'keys'  => ['q1', 'q2'],
    ],
    [
        'title' => 'برنامج القراْن و العلم ني خدمة نو تجربھ',
        'keys'  => ['q3', 'q4', 'q5', 'q6'],
    ],
    [
        'title' => 'ذاكرين فرزندو متعلق',
        'keys'  => ['q7', 'q8', 'q9', 'q10'],
    ],
    [
        'title' => 'Social Media Awareness متعلق',
        'keys'  => ['q11', 'q12', 'q13', 'q14', 'q15'],
    ],
    [
        'title' => 'شادي تفظظيم متعلق',
        'keys'  => ['q16', 'q17', 'q18', 'q19'],
    ],
    [
        'title' => 'مكتب المتابعة (BQI back office) متعلق',
        'keys'  => ['q20', 'q21', 'q22', 'q23'],
    ],
    [
        'title' => 'شكر عرض',
        'keys'  => ['q24'],
    ],
    ];

    // Helper: render one full report card for a submission row
    function renderReport($row, $questionsMap, $formSections, $userName)
    {
    $mamur_its   = (int) $row['user_its'];
    $mauze       = htmlspecialchars($row['mauze'] ? $row['mauze'] : '');
    $name        = htmlspecialchars($userName ? $userName : '—');
    $arrival     = ! empty($row['arrival_date']) ? date('d M Y', strtotime($row['arrival_date'])) : '—';
    $departure   = ! empty($row['departure_date']) ? date('d M Y', strtotime($row['departure_date'])) : '—';
    $days        = isset($row['days_of_service']) ? $row['days_of_service'] : '—';
    $photoUrl    = user_photo_url($mamur_its);
    $submittedOn = ! empty($row['submitted_ts']) ? date('d M Y, H:i', strtotime($row['submitted_ts'])) : '';
    ?>
    <!-- User Header -->
    <div class="mamur-card d-flex align-items-center gap-4 flex-wrap">
        <img src="<?php echo $photoUrl; ?>" class="mamur-photo" alt="">
        <div class="flex-grow-1">
            <div class="mamur-name"><?php echo $name; ?></div>
            <div class="meta-row mt-1">
                ITS:&nbsp;<strong><?php echo $mamur_its; ?></strong>
                &nbsp;|&nbsp;
                Mauze:&nbsp;<strong><?php echo $mauze; ?></strong>
            </div>
            <div class="mt-2 d-flex flex-wrap gap-2">
                <span class="badge bg-info text-dark">
                    <i class="bi bi-calendar-check"></i>&nbsp;Arrival: <?php echo $arrival; ?>
                </span>
                <span class="badge bg-info text-dark">
                    <i class="bi bi-calendar-x"></i>&nbsp;Departure: <?php echo $departure; ?>
                </span>
                <span class="badge bg-primary">
                    <i class="bi bi-clock"></i>&nbsp;<?php echo $days; ?> Days of Khidmat
                </span>
                <?php if ($submittedOn): ?>
                    <span class="badge bg-secondary">Submitted: <?php echo $submittedOn; ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Q&A by Section -->
    <?php if (empty($formSections)): ?>
        <div class="alert alert-warning">No questions configured.</div>
    <?php else: ?>
        <?php foreach ($formSections as $section):
                    // Check if this section has any answered questions before rendering
                    $hasAnswers = false;
                    foreach ($section['keys'] as $qKey) {
                        $ans = isset($row[$qKey]) ? $row[$qKey] : null;
                        if ($ans !== null && $ans !== '') {$hasAnswers = true;
                            break;}
                    }
                    if (! $hasAnswers) {
                        continue;
                    }

            ?>
            <div class="card section-card mb-3">
                <div class="card-body">
                    <h6 class="card-title" dir="auto"><?php echo htmlspecialchars($section['title']); ?></h6>
                    <?php foreach ($section['keys'] as $qKey):
                                    $q   = isset($questionsMap[$qKey]) ? $questionsMap[$qKey] : null;
                                    $ans = isset($row[$qKey]) ? $row[$qKey] : null;
                                    if (! $q || $ans === null || $ans === '') {
                                        continue;
                                    }

                        ?>
                        <div class="mb-3">
                            <p class="fw-semibold mb-1 text-dark" dir="auto">
                                <?php echo htmlspecialchars($q['question_text']); ?>
                            </p>
                            <?php if ($q['question_type'] === 'rating'): ?>
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <div class="progress" style="width:200px;height:18px;">
                                        <div class="progress-bar bg-warning"
                                             style="width:<?php echo (int) $ans * 10; ?>%"></div>
                                    </div>
                                    <span class="badge bg-warning text-dark fs-6"><?php echo (int) $ans; ?>/10</span>
                                </div>
                            <?php else: ?>
                                <p class="mb-0 text-secondary" dir="auto"><?php echo nl2br(htmlspecialchars($ans)); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php
        }

        // ── Single Report View (?its=ITS_ID) ──────────────────────────────────────
        if (isset($_GET['its'])) {
            $mamur_its = (int) $_GET['its'];
            if (! $mamur_its) {header('Location: report_closure_reports.php');exit();}

            $rowResult = mysqli_query($mysqli,
                "SELECT cr.*, u.fullname
         FROM `closure_report` cr
         LEFT JOIN `users_mamureen` u ON cr.user_its = u.its_id
         WHERE cr.user_its = '" . $mamur_its . "'
         LIMIT 1");
            $row      = ($rowResult && $rowResult->num_rows > 0) ? $rowResult->fetch_assoc() : [];
            $userName = isset($row['fullname']) ? $row['fullname'] : '';
        ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Closure Report &mdash; <?php echo htmlspecialchars($userName ? $userName : $mamur_its); ?></title>
        <link rel="stylesheet" href="<?php echo MODULE_PATH; ?>assets/vendor/bootstrap/css/bootstrap.min.css">
        <style>
            @font-face { font-family:'kanz'; src:url('https://www.talabulilm.com/fonts/Kanz-al-Marjaan.ttf'); }
            body { font-family:'kanz',serif; background: #f4f6fb; }
            .print-bar { font-family:'kanz',serif; background: linear-gradient(135deg,#1a73e8,#0d47a1); color:#fff; padding:12px 24px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:100; }
            .mamur-card { background:#fff; border-left:5px solid #1a73e8; border-radius:12px; box-shadow:0 2px 12px rgba(26,115,232,.10); padding:22px 26px; margin-bottom:24px; }
            .mamur-photo { width:90px; height:90px; object-fit:cover; border-radius:50%; border:3px solid #1a73e8; }
            .mamur-name { font-size:1.4rem; font-weight:700; color:#1a1a2e; }
            .meta-row { color:#555; font-size:.92rem; }
            .section-card { border:none; border-radius:10px; box-shadow:0 1px 5px rgba(0,0,0,.07); }
            .section-card .card-title { color:#1a73e8; text-align:right; font-weight:600; border-bottom:1px solid #e8f0fe; padding-bottom:6px; font-size:1.15rem; }
            .fw-semibold { font-weight:600; }
            p, span, td, th, h1, h2, h3, h4, h5, h6, button, input, textarea, label, .badge { font-family:'kanz',serif !important; }
            @media print {
                body { background:#fff !important; }
                .print-bar { display:none !important; }
                .mamur-card { box-shadow:none !important; border:1px solid #bbb; break-inside:avoid; }
                .section-card { box-shadow:none !important; border:1px solid #ddd !important; break-inside:avoid; page-break-inside:avoid; }
            }
        </style>
    </head>
    <body>
        <div class="print-bar">
            <div><strong>Closure Report</strong> &mdash; <?php echo htmlspecialchars($userName ? $userName : $mamur_its); ?></div>
            <div>
                <button id="printBtn" class="btn btn-light btn-sm me-1">&#128438; Print</button>
                <button id="closeBtn" class="btn btn-outline-light btn-sm">&#10005;</button>
            </div>
        </div>
        <div class="container py-4" style="max-width:900px;">
            <?php if (empty($row)): ?>
                <div class="alert alert-warning">No submission found for this user.</div>
            <?php else: ?>
                <?php renderReport($row, $questionsMap, $formSections, $userName); ?>
            <?php endif; ?>
        </div>
        <script src="<?php echo MODULE_PATH; ?>assets/js/admin/closure_print_view.js"></script>
    </body>
    </html>
    <?php
        exit();
        }

        // ── Print All Reports (?print_all=1) ──────────────────────────────────────
        if (isset($_GET['print_all'])) {
            $allResult = mysqli_query($mysqli,
                "SELECT cr.*, u.fullname
         FROM `closure_report` cr
         LEFT JOIN `users_mamureen` u ON cr.user_its = u.its_id
         ORDER BY u.miqaat_mauze, u.fullname");
            $allRows = ($allResult) ? $allResult->fetch_all(MYSQLI_ASSOC) : [];
        ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>All Closure Reports &mdash; <?php echo date('d M Y'); ?></title>
        <link rel="stylesheet" href="<?php echo MODULE_PATH; ?>assets/vendor/bootstrap/css/bootstrap.min.css">
        <style>
            @font-face { font-family:'kanz'; src:url('https://www.talabulilm.com/fonts/Kanz-al-Marjaan.ttf'); }
            body { font-family:'kanz',serif; background:#f4f6fb; }
            .print-bar { font-family:'kanz',serif; background:linear-gradient(135deg,#1a73e8,#0d47a1); color:#fff; padding:12px 24px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:100; }
            .mamur-card { background:#fff; border-left:5px solid #1a73e8; border-radius:10px; box-shadow:0 2px 10px rgba(26,115,232,.10); padding:20px 24px; margin-bottom:18px; }
            .mamur-photo { width:82px; height:82px; object-fit:cover; border-radius:50%; border:3px solid #1a73e8; }
            .mamur-name { font-size:1.25rem; font-weight:700; color:#1a1a2e; }
            .meta-row { color:#555; font-size:.9rem; }
            .section-card { border:none; border-radius:8px; box-shadow:0 1px 4px rgba(0,0,0,.07); }
            .section-card .card-title { color:#1a73e8; text-align: right; font-weight:600; border-bottom:1px solid #e8f0fe; padding-bottom:6px; font-size:1.15rem; }
            .fw-semibold { font-weight:600; }
            .report-block { padding:20px 0 10px; border-bottom:2px dashed #c5d5f5; margin-bottom:28px; }
            .report-block:last-child { border-bottom:none; }
            p, span, td, th, h1, h2, h3, h4, h5, h6, button, input, textarea, label, .badge { font-family:'kanz',serif !important; }
            @media print {
                body { background:#fff !important; }
                .print-bar { display:none !important; }
                .report-block { page-break-after:always; break-after:page; }
                .report-block:last-child { page-break-after:auto; break-after:auto; }
                .mamur-card { box-shadow:none !important; border:1px solid #bbb; break-inside:avoid; }
                .section-card { box-shadow:none !important; border:1px solid #ddd !important; break-inside:avoid; page-break-inside:avoid; }
            }
        </style>
    </head>
    <body>
        <div class="print-bar">
            <div><strong>All Closure Reports</strong> &mdash; <?php echo count($allRows); ?> Mamureen</div>
            <button id="printBtn" class="btn btn-light btn-sm">&#128438; Print All</button>
        </div>
        <div class="container-fluid p-4" style="max-width:1000px; margin:auto;">
            <?php if (empty($allRows)): ?>
                <div class="alert alert-warning mt-4">No closure reports submitted yet.</div>
            <?php else: ?>
                <?php foreach ($allRows as $row):
                            $userName = isset($row['fullname']) ? $row['fullname'] : '';
                    ?>
                    <div class="report-block">
                        <?php renderReport($row, $questionsMap, $formSections, $userName); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <script src="<?php echo MODULE_PATH; ?>assets/js/admin/closure_print_view.js"></script>
    </body>
    </html>
    <?php
        exit();
        }

        // ── Main Admin List ────────────────────────────────────────────────────────
        $current_page = 'report_closure_reports';
        require_once __DIR__ . '/../inc/header.php';

        // All mamureen
        $usersResult = mysqli_query($mysqli,
            "SELECT its_id, fullname, miqaat_mauze, zone FROM `users_mamureen` ORDER BY miqaat_mauze, fullname");
        $allMamureen = ($usersResult) ? $usersResult->fetch_all(MYSQLI_ASSOC) : [];

        // All submissions in a map keyed by user_its
        $subResult    = mysqli_query($mysqli, "SELECT user_its FROM `closure_report`");
        $submittedIts = [];
        if ($subResult) {
            while ($sub = $subResult->fetch_assoc()) {
                $submittedIts[$sub['user_its']] = true;
            }
        }

        $total_all       = count($allMamureen);
        $total_submitted = count($submittedIts);
        $total_not       = $total_all - $total_submitted;
    ?>
<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <?php require_once __DIR__ . '/../inc/messages.php'; ?>

            <!-- Stats -->
            <div class="col-12 mb-3">
                <div class="row g-3">
                    <div class="col-md-4 col-sm-6">
                        <div class="card border-0 shadow-sm text-center h-100">
                            <div class="card-body py-3">
                                <div class="display-6 fw-bold text-primary"><?php echo $total_all; ?></div>
                                <div class="small text-muted mt-1">Total Mamureen</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="card border-0 shadow-sm text-center h-100">
                            <div class="card-body py-3">
                                <div class="display-6 fw-bold text-success"><?php echo $total_submitted; ?></div>
                                <div class="small text-muted mt-1">Submitted</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="card border-0 shadow-sm text-center h-100">
                            <div class="card-body py-3">
                                <div class="display-6 fw-bold text-danger"><?php echo $total_not; ?></div>
                                <div class="small text-muted mt-1">Not Submitted</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">
                            Closure Reports
                            <span class="badge bg-secondary ms-2"><?php echo $total_all; ?></span>
                        </h5>
                        <a href="?print_all=1" target="_blank" class="btn btn-sm btn-primary">
                            <i class="bi bi-printer me-1"></i>Print All Reports
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle" id="datatable_cr">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>ITS</th>
                                    <th>Mauze</th>
                                    <th>Zone</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allMamureen as $idx => $m):
                                        $submitted = isset($submittedIts[$m['its_id']]);
                                ?>
                                <tr>
                                    <td><?php echo $idx + 1; ?></td>
                                    <td>
                                        <img src="<?php echo user_photo_url($m['its_id']); ?>"
                                             class="rounded-circle"
                                             style="width:46px;height:46px;object-fit:cover;" alt="">
                                    </td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($m['fullname']); ?></td>
                                    <td class="text-muted small"><?php echo (int) $m['its_id']; ?></td>
                                    <td><?php echo htmlspecialchars($m['miqaat_mauze'] ? $m['miqaat_mauze'] : ''); ?></td>
                                    <td class="text-muted small"><?php echo htmlspecialchars($m['zone'] ? $m['zone'] : ''); ?></td>
                                    <td>
                                        <?php if ($submitted): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Submitted
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Submitted</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($submitted): ?>
                                            <a href="?its=<?php echo (int) $m['its_id']; ?>" target="_blank"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">&#8212;</span>
                                        <?php endif; ?>
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

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
<script src="<?php echo MODULE_PATH; ?>assets/js/admin/report_closure_reports.js?v=1.2"></script>
