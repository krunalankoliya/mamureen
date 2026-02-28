<?php
    $current_page = 'maaraz';
    require_once __DIR__ . '/session.php';
    require_once __DIR__ . '/inc/header.php';
    // exit('sd');

    $message = null;

    // Logic: Submit Ma'raz Report
    if (isset($_POST['submit_maaraz'])) {

        $ideal_model_scale   = (int)    $_POST['ideal_model_scale'];
        $prep_hours          = (float)  $_POST['prep_hours'];
        $attendance_from     = $mysqli->real_escape_string($_POST['attendance_from']);
        $attendance_to       = $mysqli->real_escape_string($_POST['attendance_to']);
        $duration_per_day    = (int)    $_POST['duration_per_day'];
        $avg_visitor_minutes = (int)    $_POST['avg_visitor_minutes'];
        $tafheem_text        = $mysqli->real_escape_string($_POST['tafheem_text']);
        $engagement_strategy = $mysqli->real_escape_string($_POST['engagement_strategy']);
        $key_takeaways       = $mysqli->real_escape_string($_POST['key_takeaways']);
        $feedback_impact     = $mysqli->real_escape_string($_POST['feedback_impact']);
        $additional_info     = $mysqli->real_escape_string($_POST['additional_info']);
        $mauze_esc           = $mysqli->real_escape_string($mauze);

        $query = "INSERT INTO maaraz
                    (ideal_model_scale, prep_hours, attendance_from, attendance_to,
                     duration_per_day, avg_visitor_minutes, tafheem_text,
                     engagement_strategy, key_takeaways, feedback_impact,
                     additional_info, user_its, jamaat, added_its)
                  VALUES
                    ($ideal_model_scale, $prep_hours, '$attendance_from', '$attendance_to',
                     $duration_per_day, $avg_visitor_minutes, '$tafheem_text',
                     '$engagement_strategy', '$key_takeaways', '$feedback_impact',
                     '$additional_info', $user_its, '$mauze_esc', $user_its)";

        $result = mysqli_query($mysqli, $query);

        if ($result) {
            $record_id = $mysqli->insert_id;

            // Process photo attachments
            if (isset($_FILES['attachments'])) {
                foreach ($_FILES['attachments']['name'] as $i => $name) {
                    if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
                        $ext       = $mysqli->real_escape_string(strtolower(pathinfo($name, PATHINFO_EXTENSION)));
                        $newName   = $user_its . '_maaraz_' . date('YmdHis') . '_' . $i . '.' . $ext;
                        $file_name = $mysqli->real_escape_string($name);
                        $file_size = (int) $_FILES['attachments']['size'][$i];

                        if (move_uploaded_file($_FILES['attachments']['tmp_name'][$i], __DIR__ . '/user_uploads/' . $newName)) {
                            $fq = "INSERT INTO bqi_file_attachments
                                       (module, record_id, file_name, file_path, file_type, file_size, added_its)
                                   VALUES
                                       ('maaraz', $record_id, '$file_name', '$newName', '$ext', $file_size, $user_its)";
                            mysqli_query($mysqli, $fq);
                        }
                    }
                }
            }

            $message = ['text' => "Ma'raz report submitted successfully.", 'tag' => 'success'];
        } else {
            $message = ['text' => 'Error: ' . $mysqli->error, 'tag' => 'danger'];
        }
    }

    // Fetch previous reports for this jamaat
    $records  = [];
    $mauze_esc = $mysqli->real_escape_string($mauze);
    $query    = "SELECT m.*, um.fullname AS submitted_by
                 FROM maaraz m
                 LEFT JOIN users_mamureen um ON um.its_id = m.added_its
                 WHERE m.jamaat = '$mauze_esc'
                 ORDER BY m.added_ts DESC";
    $result   = mysqli_query($mysqli, $query);
    if ($result) {
        $records = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $message = ['text' => 'Table not found. Please import maaraz.sql first. (' . $mysqli->error . ')', 'tag' => 'warning'];
    }
?>

<link rel="stylesheet" href="<?php echo MODULE_PATH ?>assets/css/maaraz.css?v=1">

<main id="main" class="main main-content">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Social Media Ma'raz</h1>
            <p class="text-muted small mb-0">
                Aapna Mauze ma Ma'raz aqd thai ne tamaam thai gayu hoi &mdash; toj aa report fill karwu
            </p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message['tag'] ?> border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-info-circle-fill me-2"></i> <?php echo $message['text'] ?>
        </div>
    <?php endif; ?>

    <!-- ── FORM ──────────────────────────────────────────────────────── -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">

        <div class="d-flex align-items-center justify-content-between mb-1">
            <h5 class="fw-bold mb-0">Ma'raz Report</h5>
            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 small fw-semibold">
                <i class="bi bi-floppy-fill me-1"></i> Auto-saved
            </span>
        </div>
        <p class="text-muted small mb-4">Fields marked <span class="text-danger fw-bold">*</span> are required.</p>

        <div id="draftBanner" class="alert alert-info border-0 rounded-4 py-2 px-3 d-none mb-3" role="alert">
            <i class="bi bi-cloud-check me-2"></i>
            <strong>Draft restored.</strong> Your previously entered data has been loaded automatically.
            <button type="button" class="btn btn-sm btn-outline-secondary ms-3 rounded-pill" id="clearDraftBtn">Clear Draft</button>
        </div>

        <form id="uploadForm" method="post" enctype="multipart/form-data" data-user-its="<?php echo $user_its ?>">

            <!-- Q1: Ideal Model Scale -->
            <div class="maaraz-q">
                <div class="d-flex gap-3 align-items-start mb-3">
                    <span class="q-num">1</span>
                    <div>
                        <div class="q-label">
                            Ma'raz waaste je tawjehaat ane ideal models mokalwa ma aya &mdash; ye raah par kitni had lag Ma'raz organise karwu imkaan thayu?
                            <span class="text-danger ms-1">*</span>
                        </div>
                        <div class="q-sub mt-1">Scale of 0 to 100 &mdash; Intervals after every 10</div>
                    </div>
                </div>

                <!-- Range slider -->
                <div class="scale-slider-wrap">
                    <input type="range" id="scaleSlider" name="ideal_model_scale"
                           min="0" max="100" step="10" value="0" required>
                    <div class="scale-ticks">
                        <span>0</span><span>10</span><span>20</span><span>30</span><span>40</span>
                        <span>50</span><span>60</span><span>70</span><span>80</span><span>90</span><span>100</span>
                    </div>
                </div>

                <!-- Result badge -->
                <div class="scale-result">
                    <span class="pct-val" id="scaleVal">0</span>
                    <span class="pct-sym">%</span>
                    <div class="pct-divider"></div>
                    <div class="pct-label">
                        <strong>yej raah par</strong><br>Ma'raz aqd thayu
                    </div>
                </div>
            </div>

            <!-- Q2: Preparation Hours -->
            <div class="maaraz-q">
                <div class="d-flex gap-3 align-items-start mb-3">
                    <span class="q-num">2</span>
                    <div class="q-label">
                        Ma'raz ni tayyari waste jumlatan kitno waqt sarf thayo?
                        <span class="text-danger ms-1">*</span>
                    </div>
                </div>
                <div class="row g-2 align-items-center">
                    <div class="col-sm-5 col-md-4">
                        <div class="input-group">
                            <input type="number" name="prep_hours" id="prep_hours" class="form-control form-control-lg"
                                   placeholder="0" min="0" step="0.5" required>
                            <span class="input-group-text fw-semibold">Hours</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Q3: Attendance Dates & Duration -->
            <div class="maaraz-q">
                <div class="d-flex gap-3 align-items-start mb-3">
                    <span class="q-num">3</span>
                    <div>
                        <div class="q-label">
                            Mumineen &mdash; Ma'raz ma kiwar si kiwar lag hazir thata?
                            <span class="text-danger ms-1">*</span>
                        </div>
                        <div class="q-sub mt-1">Ma'raz kai tarikh shuru thayu ane kai tarikh tamaam thayu? Ane Ma'raz waaste su awqaat aapwama aaya hata?</div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-sm-4">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.05rem">From</label>
                        <input type="date" name="attendance_from" id="attendance_from" class="form-control" required>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.05rem">To</label>
                        <input type="date" name="attendance_to" id="attendance_to" class="form-control" required>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.05rem">Duration per day</label>
                        <div class="input-group">
                            <input type="number" name="duration_per_day" id="duration_per_day" class="form-control"
                                   placeholder="0" min="1" required>
                            <span class="input-group-text">mins</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Q4: Avg Visitor Time -->
            <div class="maaraz-q">
                <div class="d-flex gap-3 align-items-start mb-3">
                    <span class="q-num">4</span>
                    <div>
                        <div class="q-label">
                            Ma'raz ma awnaar shuru si akhir lag Ma'raz ma kitno waqt sarf karta?
                            <span class="text-danger ms-1">*</span>
                        </div>
                        <div class="q-sub mt-1">on average</div>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-sm-5 col-md-4">
                        <div class="input-group">
                            <input type="number" name="avg_visitor_minutes" id="avg_visitor_minutes" class="form-control form-control-lg"
                                   placeholder="0" min="1" required>
                            <span class="input-group-text fw-semibold">Minutes</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Q5: Tafheem -->
            <div class="maaraz-q">
                <div class="d-flex gap-3 align-items-start mb-3">
                    <span class="q-num">5</span>
                    <div class="q-label">
                        Ma'raz ma tafheem koiye kidi?
                        <span class="text-danger ms-1">*</span>
                    </div>
                </div>
                <textarea name="tafheem_text" id="tafheem_text" class="form-control" rows="3"
                          placeholder="Describe the tafheem (explanation / briefing) done during the Ma'raz..." required></textarea>
            </div>

            <!-- Q6: Engagement Strategy -->
            <div class="maaraz-q">
                <div class="d-flex gap-3 align-items-start mb-3">
                    <span class="q-num">6</span>
                    <div class="q-label">
                        Ma'raz ma awnaar akhir lag engaged rahe ane barabar faido hasil kare ye waste su strategy ya tools istemaal karwa ma aya?
                        <span class="text-danger ms-1">*</span>
                    </div>
                </div>
                <textarea name="engagement_strategy" id="engagement_strategy" class="form-control" rows="3"
                          placeholder="List the strategies or tools used to keep visitors engaged throughout the Ma'raz..." required></textarea>
            </div>

            <!-- Q7: Key Takeaways -->
            <div class="maaraz-q">
                <div class="d-flex gap-3 align-items-start mb-3">
                    <span class="q-num">7</span>
                    <div class="q-label">
                        Ma'raz ma awa si Mumin ne kaya ehem takeaways mila?
                        <span class="text-danger ms-1">*</span>
                    </div>
                </div>
                <textarea name="key_takeaways" id="key_takeaways" class="form-control" rows="3"
                          placeholder="What were the most important takeaways visitors gained from this Ma'raz?" required></textarea>
            </div>

            <!-- Q8: Feedback Impact -->
            <div class="maaraz-q">
                <div class="d-flex gap-3 align-items-start mb-3">
                    <span class="q-num">8</span>
                    <div>
                        <div class="q-label">
                            Ma'raz ma awnaar mumineen nu (formally ya informally) feedback liduj hase. Ye feedback na asaas par jawab do ke Mumineen ne aa Ma'raz ye kai tarah impact kidu?
                            <span class="text-danger ms-1">*</span>
                        </div>
                    </div>
                </div>
                <div class="row g-2">
                    <?php
                    $impacts = [
                        'strongly_positive' => ['label' => 'Strongly / Positive Impact', 'icon' => 'bi-emoji-laughing-fill', 'color' => 'success'],
                        'positive'          => ['label' => 'Positive Impact',             'icon' => 'bi-emoji-smile-fill',    'color' => 'primary'],
                        'neutral'           => ['label' => 'Neutral / Minimal Impact',    'icon' => 'bi-emoji-neutral-fill',  'color' => 'secondary'],
                        'negative'          => ['label' => 'Negative Impact',             'icon' => 'bi-emoji-frown-fill',    'color' => 'warning'],
                        'strongly_negative' => ['label' => 'Strongly / Negative Impact',  'icon' => 'bi-emoji-angry-fill',    'color' => 'danger'],
                    ];
                    foreach ($impacts as $val => $opt): ?>
                        <div class="col-sm-6 col-xl-4">
                            <div class="impact-card">
                                <div class="form-check d-flex align-items-center gap-2 mb-0">
                                    <input class="form-check-input mt-0 flex-shrink-0" type="radio"
                                           name="feedback_impact" id="impact_<?php echo $val ?>" value="<?php echo $val ?>" required>
                                    <label class="form-check-label d-flex align-items-center gap-2 fw-semibold" for="impact_<?php echo $val ?>">
                                        <i class="bi <?php echo $opt['icon'] ?> text-<?php echo $opt['color'] ?> fs-5"></i>
                                        <span><?php echo $opt['label'] ?></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Q9: Photo Upload -->
            <div class="maaraz-q">
                <div class="d-flex gap-3 align-items-start mb-3">
                    <span class="q-num">9</span>
                    <div>
                        <div class="q-label">Ma'raz ni tasaveer yaha upload karwu</div>
                        <div class="q-sub mt-1">JPEG / PNG / WebP &mdash; max 5 MB per file</div>
                    </div>
                </div>
                <input type="file" name="attachments[]" id="attachments" class="form-control"
                       accept="image/jpeg,image/png,image/webp" multiple>
                <div class="form-text mt-2">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>Note:</strong> Ma'raz waste charts, promotional material, video ya koi bisri taiyaario kidi hoi to
                    <strong>"Khidmat Preparations"</strong> na tab ma ehne upload karwu
                </div>
            </div>

            <!-- Q10: Additional Info -->
            <div class="maaraz-q">
                <div class="d-flex gap-3 align-items-start mb-3">
                    <span class="q-num">10</span>
                    <div class="q-label">
                        Ma'raz mutalliq mazeed koi information share karwi hoi to yaha likhwu:
                    </div>
                </div>
                <textarea name="additional_info" id="additional_info" class="form-control" rows="3"
                          placeholder="Any additional details about the Ma'raz..."></textarea>
            </div>

            <!-- Upload Progress Bar -->
            <div id="progressContainer" class="mt-3 d-none">
                <div class="progress upload-progress-bar mb-2">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary fw-bold"
                         role="progressbar" style="width:0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
                <div id="uploadMessage" class="small fw-semibold text-primary d-none"></div>
            </div>

            <!-- Review warning -->
            <div class="alert alert-warning border-0 rounded-4 mt-3 py-2 px-3 small">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Please review your entire Ma'raz report before submission.</strong>
                Once the report is submitted, you will not be able to edit it.
            </div>

            <div class="mt-3 d-flex gap-2 flex-wrap">
                <button type="submit" id="submitBtn" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">
                    <i class="bi bi-send-fill me-2"></i>Submit Ma'raz Report
                </button>
                <button type="reset" class="btn btn-outline-secondary rounded-pill px-4 py-2">Reset</button>
            </div>

        </form>
    </div>

    <!-- ── RECORDS TABLE ─────────────────────────────────────────────── -->
    <?php if (! empty($records)): ?>
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom-0">
            <h5 class="fw-bold mb-0">Submitted Reports</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="bg-light bg-opacity-50">
                    <tr>
                        <th class="px-4 py-3 small text-muted text-uppercase border-0">Ma'raz Dates</th>
                        <th class="py-3 small text-muted text-uppercase border-0">Scale</th>
                        <th class="py-3 small text-muted text-uppercase border-0">Prep Hrs</th>
                        <th class="py-3 small text-muted text-uppercase border-0">Avg Visit</th>
                        <th class="py-3 small text-muted text-uppercase border-0">Impact</th>
                        <th class="px-4 py-3 small text-muted text-uppercase border-0">Submitted By</th>
                        <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $impact_badges = [
                            'strongly_positive' => ['Strongly +ve', 'success'],
                            'positive'          => ['Positive', 'primary'],
                            'neutral'           => ['Neutral', 'secondary'],
                            'negative'          => ['Negative', 'warning'],
                            'strongly_negative' => ['Strongly -ve', 'danger'],
                        ];
                        foreach ($records as $r):
                            $badge = $impact_badges[$r['feedback_impact']] ?? ['N/A', 'light'];
                    ?>
                    <tr>
                        <td class="px-4 py-3">
                            <div class="small fw-semibold text-dark">
                                <?php echo date('d M', strtotime($r['attendance_from'])) ?> – <?php echo date('d M Y', strtotime($r['attendance_to'])) ?>
                            </div>
                            <div class="text-muted" style="font-size:0.7rem"><?php echo $r['duration_per_day'] ?> min/day</div>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2"><?php echo $r['ideal_model_scale'] ?>%</span>
                        </td>
                        <td class="py-3 small fw-semibold"><?php echo $r['prep_hours'] ?> hrs</td>
                        <td class="py-3 small"><?php echo $r['avg_visitor_minutes'] ?> min</td>
                        <td class="py-3">
                            <span class="badge bg-<?php echo $badge[1] ?> bg-opacity-10 text-<?php echo $badge[1] ?> rounded-pill px-2">
                                <?php echo $badge[0] ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 small"><?php echo h($r['submitted_by'] ?? 'N/A') ?></td>
                        <td class="px-4 py-3 text-end small fw-bold text-dark"><?php echo date('d M, Y', strtotime($r['added_ts'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</main>

<?php
    require_once __DIR__ . '/inc/footer.php';
    require_once __DIR__ . '/inc/js-block.php';
?>
<script src="<?php echo MODULE_PATH ?>assets/js/maaraz.js?v=6"></script>
