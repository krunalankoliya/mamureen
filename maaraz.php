<?php
    $current_page = 'maaraz';
    require_once __DIR__ . '/session.php';
    require_once __DIR__ . '/inc/header.php';

    $already_filled = false;
    $filled_on      = null;
    $mauze_esc      = $mysqli->real_escape_string($mauze);
    $check_q        = mysqli_query($mysqli, "SELECT added_ts FROM `maaraz` WHERE user_its = $user_its AND jamaat = '$mauze_esc' LIMIT 1");
    if ($check_q && $check_q->num_rows) {
    $already_filled = true;
    $row            = $check_q->fetch_assoc();
    $filled_on      = $row['added_ts'];
    }

    // Handle Submit

    // Handle Submit
    $message = null;
    if (isset($_POST['submit_maaraz'])) {
    $ideal_model_scale = (int) $_POST['ideal_model_scale'];
    $prep_hours        = (float) $_POST['prep_hours'];
    $attendance_from   = $mysqli->real_escape_string($_POST['attendance_from']);
    $attendance_to     = $mysqli->real_escape_string($_POST['attendance_to']);

    // ensure from date is <= to date
    if ($attendance_from && $attendance_to && $attendance_from > $attendance_to) {
        die("Invalid dates: 'From' date cannot be later than 'To' date.");
    }
    $duration_per_day    = (int) $_POST['duration_per_day'];
    $avg_visitor_minutes = (int) $_POST['avg_visitor_minutes'];
    $tafheem_text        = $mysqli->real_escape_string($_POST['tafheem_text']);
    $engagement_strategy = $mysqli->real_escape_string($_POST['engagement_strategy']);
    $key_takeaways       = $mysqli->real_escape_string($_POST['key_takeaways']);
    $feedback_impact     = $mysqli->real_escape_string($_POST['feedback_impact']);
    $additional_info     = $mysqli->real_escape_string($_POST['additional_info']);
    $instruction         = isset($_POST['instruction']) ? 1 : 0;
    $mauze_esc           = $mysqli->real_escape_string($mauze);

    $query = "INSERT INTO maaraz
                (ideal_model_scale, prep_hours, attendance_from, attendance_to,
                 duration_per_day, avg_visitor_minutes, tafheem_text,
                 engagement_strategy, key_takeaways, feedback_impact,
                 additional_info, instruction, user_its, jamaat, added_its)
              VALUES
                ($ideal_model_scale, $prep_hours, '$attendance_from', '$attendance_to',
                 $duration_per_day, $avg_visitor_minutes, '$tafheem_text',
                 '$engagement_strategy', '$key_takeaways', '$feedback_impact',
                 '$additional_info', $instruction, $user_its, '$mauze_esc', $user_its)";

    $result = mysqli_query($mysqli, $query);

    if ($result) {
        $record_id = $mysqli->insert_id;
        // Photo attachments
        if (! empty($_FILES['attachments']['name'][0])) {
            foreach ($_FILES['attachments']['name'] as $i => $name) {
                if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext   = $mysqli->real_escape_string(strtolower(pathinfo($name, PATHINFO_EXTENSION)));
                    $fsize = (int) $_FILES['attachments']['size'][$i];

                    // skip files bigger than 5MB
                    if ($fsize > 5 * 1024 * 1024) {
                        continue;
                    }

                    // allow only specific extensions
                    $allowedExt = [
                        'jpg', 'jpeg', 'png',
                        'mp4', 'mov', 'avi',
                        'mp3', 'wav', 'ogg',
                        'pdf', 'ppt', 'pptx',
                    ];
                    if (! in_array($ext, $allowedExt, true)) {
                        continue;
                    }

                    $newName = $user_its . '_maaraz_' . date('YmdHis') . '_' . $i . '.' . $ext;
                    $fname   = $mysqli->real_escape_string($name);
                    if (move_uploaded_file($_FILES['attachments']['tmp_name'][$i], __DIR__ . '/user_uploads/' . $newName)) {
                        mysqli_query($mysqli, "INSERT INTO bqi_file_attachments
                            (module, record_id, file_name, file_path, file_type, file_size, added_its)
                            VALUES ('maaraz', $record_id, '$fname', '$newName', '$ext', $fsize, $user_its)");
                    }
                }
            }
        }
        $message = ['type' => 'success', 'text' => "&#10004; Ma'raz report submitted successfully! (ID: $record_id)"];
    } else {
        $message = ['type' => 'danger', 'text' => '&#10008; Error: ' . $mysqli->error];
    }
    }

    // Fetch records
    $records   = [];
    $mauze_esc = $mysqli->real_escape_string($mauze);
    $res       = mysqli_query($mysqli, "SELECT m.*, um.fullname AS submitted_by
             FROM maaraz m
             LEFT JOIN users_mamureen um ON um.its_id = m.added_its
             WHERE m.jamaat = '$mauze_esc'
             ORDER BY m.added_ts DESC");
    if ($res) {
    $records = $res->fetch_all(MYSQLI_ASSOC);
    }

?>

<link rel="stylesheet" href="<?php echo MODULE_PATH ?>assets/css/maaraz_new.css?v=1.1">

<main id="main" class="main main-content">

    <div style="max-width: 800px; margin: 0 auto;">

        <h1 style="font-size: 1.5rem; font-weight: 800; color: #1a1a2e; margin-bottom: 4px;">Social Media Ma'raz</h1>
        <p style="font-size: .85rem; color: #6c757d; margin-bottom: 28px;">
            Aapna Mauze ma Ma'raz aqd thai ne tamaam thai gayu hoi — toj aa report fill karwu
        </p>

        <?php if ($message): ?>
        <div style="padding: 12px 18px; border-radius: 12px; font-size: .9rem; font-weight: 500; margin-bottom: 20px; background: <?php echo $message['type'] === 'success' ? '#d1f2eb' : '#fde8e8'; ?>; color: <?php echo $message['type'] === 'success' ? '#1a5c47' : '#7b1c1c'; ?>; border: 1px solid <?php echo $message['type'] === 'success' ? '#a3e4d7' : '#f5c6c6'; ?>;">
            <?php echo $message['text'] ?>
        </div>
        <?php endif; ?>

        <?php if ($already_filled): ?>
            <div class="alert alert-info">
                You have already submitted the Ma'raz report for this mauze on <?php echo date('d-M-Y H:i', strtotime($filled_on)); ?>. Thank you!
            </div>
        <?php else: ?>
        <!-- FORM CARD -->
        <div style="background: #fff; border-radius: 20px; box-shadow: 0 4px 24px rgba(13,110,253,.08), 0 1px 4px rgba(0,0,0,.06); padding: 28px 32px; margin-bottom: 24px;">

            <div style="font-size: 1.05rem; font-weight: 700; color: #1a1a2e; margin-bottom: 4px;">Ma'raz Report</div>
            <p style="font-size: .78rem; color: #6c757d; margin-bottom: 24px;">
                Fields marked <span style="color:#dc3545;font-weight:700">*</span> are required.
            </p>

        <div class="draft-banner" id="draftBanner">
            &#9729; <strong>Draft restored.</strong>&nbsp;Your previously entered data has been loaded.
            <button class="btn-clear-draft" id="clearDraftBtn">Clear Draft</button>
        </div>

        <form id="uploadForm" method="POST" enctype="multipart/form-data"
              data-user-its="<?php echo $user_its ?>">

            <!-- Q1 ── Ideal Model Scale -->
            <div class="q-block">
                <div class="q-header">
                    <span class="q-num">1</span>
                    <div>
                        <div class="q-title">
                            Ma'raz waaste je tawjehaat ane ideal models mokalwa ma aya &mdash;
                            ye raah par kitni had lag Ma'raz organise karwu imkaan thayu?
                            <span class="req">*</span>
                        </div>
                        <div class="q-sub">Scale of 0 to 100 &mdash; Intervals after every 10</div>
                    </div>
                </div>

                <div class="slider-row">
                    <span class="tick-lbl">0%</span>
                    <input type="range" id="scaleSlider" name="ideal_model_scale"
                           min="0" max="100" step="10" value="0" required>
                    <span class="tick-lbl">100%</span>
                </div>
                <div class="fill-track"><div class="fill-bar" id="fillBar"></div></div>
                <div class="ticks">
                    <span>0</span><span>10</span><span>20</span><span>30</span><span>40</span>
                    <span>50</span><span>60</span><span>70</span><span>80</span><span>90</span><span>100</span>
                </div>
                <div class="result-badge">
                    <span class="big" id="scaleVal">0</span>
                    <span class="pct-sym">%</span>
                    <div class="divider"></div>
                    <div class="lbl"><strong>yej raah par</strong><br>Ma'raz aqd thayu</div>
                </div>
            </div>

                <!-- Q2 ── Prep Hours -->
                <div class="q-block">
                    <div class="q-header">
                        <span class="q-num">2</span>
                        <div class="q-title">
                            Ma'raz ni tayyari waste jumlatan kitno waqt sarf thayo?
                            <span class="req">*</span>
                        </div>
                    </div>
                    <div style="max-width:220px">
                        <div class="input-group">
                            <input type="number" id="prep_hours" name="prep_hours" placeholder="0" min="0" step="0.5" required>
                            <span class="suffix">Hrs</span>
                        </div>
                    </div>
                </div>

                <!-- Q3 ── Attendance Dates & Duration -->
                <div class="q-block">
                    <div class="q-header">
                        <span class="q-num">3</span>
                        <div>
                            <div class="q-title">
                                Mumineen &mdash; Ma'raz ma kiwar si kiwar lag hazir thata?
                                <span class="req">*</span>
                            </div>
                            <div class="q-sub">Ma'raz kai tarikh shuru &amp; kai tarikh tamaam? Awqaat keta hata?</div>
                        </div>
                    </div>
                    <div class="row-3">
                        <div>
                            <div class="field-label">From</div>
                            <input type="date" id="attendance_from" name="attendance_from" required>
                        </div>
                        <div>
                            <div class="field-label">To</div>
                            <input type="date" id="attendance_to" name="attendance_to" required>
                        </div>
                        <div>
                            <div class="field-label">Duration per day</div>
                            <div class="input-group">
                                <input type="number" id="duration_per_day" name="duration_per_day" placeholder="0" min="1" required>
                                <span class="suffix">mins</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Q4 ── Avg Visitor Time -->
                <div class="q-block">
                    <div class="q-header">
                        <span class="q-num">4</span>
                        <div>
                            <div class="q-title">
                                Ma'raz ma awnaar shuru si akhir lag kitno waqt sarf karta?
                                <span class="req">*</span>
                            </div>
                            <div class="q-sub">on average</div>
                        </div>
                    </div>
                    <div style="max-width:220px">
                        <div class="input-group">
                            <input type="number" id="avg_visitor_minutes" name="avg_visitor_minutes" placeholder="0" min="1" required>
                            <span class="suffix">Minutes</span>
                        </div>
                    </div>
                </div>

            <!-- Q5 ── Tafheem -->
            <div class="q-block">
                <div class="q-header">
                    <span class="q-num">5</span>
                    <div class="q-title">
                        Ma'raz ma tafheem koiye kidi?
                        <span class="req">*</span>
                    </div>
                </div>
                <textarea id="tafheem_text" name="tafheem_text"
                          placeholder="Describe the tafheem (explanation / briefing) done during the Ma'raz..." required></textarea>
            </div>

            <!-- Q6 ── Engagement Strategy -->
            <div class="q-block">
                <div class="q-header">
                    <span class="q-num">6</span>
                    <div class="q-title">
                        Awnaar engaged rahe ane faido hasil kare ye waste su strategy ya tools istemaal karwa ma aya?
                        <span class="req">*</span>
                    </div>
                </div>
                <textarea id="engagement_strategy" name="engagement_strategy"
                          placeholder="List the strategies or tools used to keep visitors engaged..." required></textarea>
            </div>

            <!-- Q7 ── Key Takeaways -->
            <div class="q-block">
                <div class="q-header">
                    <span class="q-num">7</span>
                    <div class="q-title">
                        Ma'raz ma awa si Mumin ne kaya ehem takeaways mila?
                        <span class="req">*</span>
                    </div>
                </div>
                <textarea id="key_takeaways" name="key_takeaways"
                          placeholder="What were the most important takeaways visitors gained?" required></textarea>
            </div>

            <!-- Q8 ── Feedback Impact -->
            <div class="q-block">
                <div class="q-header">
                    <span class="q-num">8</span>
                    <div class="q-title">
                        Mumineen nu feedback na asaas par &mdash; aa Ma'raz ye kai tarah impact kidu?
                        <span class="req">*</span>
                    </div>
                </div>
                <div class="impact-grid">
                    <?php
                        $impacts = [
                            'strongly_positive' => ['Strongly Positive Impact', 'badge-green'],
                            'positive'          => ['Positive Impact', 'badge-blue'],
                            'neutral'           => ['Neutral / Minimal Impact', 'badge-gray'],
                            'negative'          => ['Negative Impact', 'badge-yellow'],
                            'strongly_negative' => ['Strongly Negative Impact', 'badge-red'],
                        ];
                    foreach ($impacts as $val => $opt): ?>
                    <label class="impact-card" for="impact_<?php echo $val ?>">
                        <input type="radio" id="impact_<?php echo $val ?>"
                               name="feedback_impact" value="<?php echo $val ?>" required>
                        <span><?php echo $opt[0] ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Q9 ── File Upload -->
            <div class="q-block">
                <div class="q-header">
                    <span class="q-num">9</span>
                    <div>
                        <div class="q-title">Ma'raz na related files yaha upload karo</div>
                        <div class="q-sub">Allowed: Images (JPG, PNG), Videos (MP4, MOV, AVI), Audio (MP3, WAV, OGG), PDF, PPT &mdash; max 5 MB per file</div>
                    </div>
                </div>
                <input type="file" name="attachments[]" id="attachments"
                      accept="image/jpeg,image/png,video/mp4,video/quicktime,video/x-msvideo,audio/mpeg,audio/wav,audio/ogg,application/pdf,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation" multiple>
                <p class="file-note">
                    &#8505; Ma'raz waste charts, promotional material ya bisri taiyaario &mdash;
                    ehne <strong>"Khidmat Preparations"</strong> ma upload karwu.
                </p>
            </div>

            <!-- Q10 ── Additional Info -->
            <div class="q-block">
                <div class="q-header">
                    <span class="q-num">10</span>
                    <div class="q-title">Ma'raz mutalliq mazeed koi information share karwi hoi to yaha likhwu:</div>
                </div>
                <textarea id="additional_info" name="additional_info"
                          placeholder="Any additional details about the Ma'raz..."></textarea>
            </div>

            <!-- Progress bar -->
            <div class="progress-wrap" id="progressWrap">
                <div class="progress-track">
                    <div class="progress-fill" id="progressBar">0%</div>
                </div>
                <div class="progress-msg" id="progressMsg"></div>
            </div>

            <!-- Review Confirmation Checkbox -->
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px 16px;margin-top:14px;display:flex;align-items:flex-start;gap:12px">
                <input type="checkbox" id="instruction" name="instruction" style="margin-top:3px;cursor:pointer;" required>
                <label for="instruction" style="font-size:.82rem;color:#92400e;cursor:pointer;line-height:1.5;margin:0">
                    <strong>Please review your entire Ma'raz report before submission.</strong> Once submitted, you will not be able to edit it.
                </label>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn-submit" id="submitBtn">
                    Submit Ma'raz Report
                </button>
                <button type="reset" class="btn-reset" id="resetBtn">Reset</button>
            </div>

        </form>
        </div>

        <?php endif; ?>
        <!-- RECORDS -->
        <?php if (! empty($records)): ?>
        <div style="background: #fff; border-radius: 20px; box-shadow: 0 4px 24px rgba(13,110,253,.08), 0 1px 4px rgba(0,0,0,.06); padding: 28px 32px; overflow: hidden;">
            <div style="font-size: 1.05rem; font-weight: 700; color: #1a1a2e; margin-bottom: 16px;">Submitted Reports (<?php echo count($records) ?>)</div>
            <div style="overflow-x:auto">
                <table style="width: 100%; border-collapse: collapse; font-size: .83rem;">
                    <thead>
                        <tr>
                            <th style="background: #f8f9fa; text-align: left; padding: 10px 14px; font-weight: 600; color: #6c757d; font-size: .72rem; text-transform: uppercase; letter-spacing: .04rem; border-bottom: 1.5px solid #e9ecef;">Dates</th>
                            <th style="background: #f8f9fa; text-align: left; padding: 10px 14px; font-weight: 600; color: #6c757d; font-size: .72rem; text-transform: uppercase; letter-spacing: .04rem; border-bottom: 1.5px solid #e9ecef;">Scale</th>
                            <th style="background: #f8f9fa; text-align: left; padding: 10px 14px; font-weight: 600; color: #6c757d; font-size: .72rem; text-transform: uppercase; letter-spacing: .04rem; border-bottom: 1.5px solid #e9ecef;">Prep Hrs</th>
                            <th style="background: #f8f9fa; text-align: left; padding: 10px 14px; font-weight: 600; color: #6c757d; font-size: .72rem; text-transform: uppercase; letter-spacing: .04rem; border-bottom: 1.5px solid #e9ecef;">Avg Visit</th>
                            <th style="background: #f8f9fa; text-align: left; padding: 10px 14px; font-weight: 600; color: #6c757d; font-size: .72rem; text-transform: uppercase; letter-spacing: .04rem; border-bottom: 1.5px solid #e9ecef;">Impact</th>
                            <th style="background: #f8f9fa; text-align: left; padding: 10px 14px; font-weight: 600; color: #6c757d; font-size: .72rem; text-transform: uppercase; letter-spacing: .04rem; border-bottom: 1.5px solid #e9ecef;">Submitted By</th>
                            <th style="background: #f8f9fa; text-align: left; padding: 10px 14px; font-weight: 600; color: #6c757d; font-size: .72rem; text-transform: uppercase; letter-spacing: .04rem; border-bottom: 1.5px solid #e9ecef;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $impact_map = [
                            'strongly_positive' => ['Strongly +ve', 'badge-green'],
                            'positive'          => ['Positive', 'badge-blue'],
                            'neutral'           => ['Neutral', 'badge-gray'],
                            'negative'          => ['Negative', 'badge-yellow'],
                            'strongly_negative' => ['Strongly -ve', 'badge-red'],
                        ];
                        foreach ($records as $r):
                            $b = $impact_map[$r['feedback_impact']] ?? ['N/A', 'badge-gray'];
                    ?>
                    <tr>
                        <td style="padding: 10px 14px; border-bottom: 1px solid #f0f0f0; color: #1a1a2e; vertical-align: middle;">
                            <strong><?php echo date('d M', strtotime($r['attendance_from'])) ?> – <?php echo date('d M Y', strtotime($r['attendance_to'])) ?></strong>
                            <div style="font-size:.72rem;color:#6c757d"><?php echo $r['duration_per_day'] ?> min/day</div>
                        </td>
                        <td style="padding: 10px 14px; border-bottom: 1px solid #f0f0f0; color: #1a1a2e; vertical-align: middle;"><span style="display: inline-block; padding: 3px 10px; border-radius: 50px; font-size: .72rem; font-weight: 600; background: #dbeafe; color: #1d4ed8;"><?php echo $r['ideal_model_scale'] ?>%</span></td>
                        <td style="padding: 10px 14px; border-bottom: 1px solid #f0f0f0; color: #1a1a2e; vertical-align: middle;"><?php echo $r['prep_hours'] ?> hrs</td>
                        <td style="padding: 10px 14px; border-bottom: 1px solid #f0f0f0; color: #1a1a2e; vertical-align: middle;"><?php echo $r['avg_visitor_minutes'] ?> min</td>
                        <td style="padding: 10px 14px; border-bottom: 1px solid #f0f0f0; color: #1a1a2e; vertical-align: middle;">
                            <span style="display: inline-block; padding: 3px 10px; border-radius: 50px; font-size: .72rem; font-weight: 600; background: <?php echo $b[1] === 'badge-green' ? '#d1fae5' : ($b[1] === 'badge-blue' ? '#dbeafe' : ($b[1] === 'badge-yellow' ? '#fef9c3' : ($b[1] === 'badge-red' ? '#fee2e2' : '#f3f4f6'))); ?>; color: <?php echo $b[1] === 'badge-green' ? '#065f46' : ($b[1] === 'badge-blue' ? '#1d4ed8' : ($b[1] === 'badge-yellow' ? '#854d0e' : ($b[1] === 'badge-red' ? '#991b1b' : '#374151'))); ?>;">
                                <?php echo $b[0] ?>
                            </span>
                        </td>
                        <td style="padding: 10px 14px; border-bottom: 1px solid #f0f0f0; color: #1a1a2e; vertical-align: middle;"><?php echo htmlspecialchars($r['submitted_by'] ?? 'N/A') ?></td>
                        <td style="padding: 10px 14px; border-bottom: 1px solid #f0f0f0; color: #1a1a2e; vertical-align: middle;"><?php echo date('d M, Y', strtotime($r['added_ts'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div>

</main>

<?php
    require_once __DIR__ . '/inc/footer.php';
    require_once __DIR__ . '/inc/js-block.php';
?>
<script src="<?php echo MODULE_PATH ?>assets/js/maaraz_new.js?v=2"></script>
