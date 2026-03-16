<?php
    $current_page = 'closure_report';
    require_once __DIR__ . '/session.php';
    require_once __DIR__ . '/inc/header.php';

    $already_filled = false;
    $filled_on      = null;
    $mauze_esc      = $mysqli->real_escape_string($mauze);
    $check_q        = mysqli_query($mysqli, "SELECT added_ts FROM `closure_report` WHERE user_its = $user_its AND mauze = '$mauze_esc' LIMIT 1");
    if ($check_q && $check_q->num_rows) {
    $already_filled = true;
    $row            = $check_q->fetch_assoc();
    $filled_on      = $row['added_ts'];
    }

    // Fetch dynamic questions
    $questions_res = mysqli_query($mysqli, "SELECT * FROM `closure_report_questions` ORDER BY `section_id`, `id` ASC");
    $db_questions  = [];
    while ($q_row = mysqli_fetch_assoc($questions_res)) {
    $db_questions[$q_row['q_key']] = $q_row;
    }

    $message = null;
    if (isset($_POST['submit_closure'])) {
    $arrival_date    = isset($_POST['arrival_date']) ? $mysqli->real_escape_string($_POST['arrival_date']) : null;
    $departure_date  = isset($_POST['departure_date']) ? $mysqli->real_escape_string($_POST['departure_date']) : null;
    $days_of_service = isset($_POST['days_of_service']) ? (int) $_POST['days_of_service'] : 0;

    $q_vals = [];
    for ($i = 1; $i <= 24; $i++) {
        $key = "q$i";
        if (isset($db_questions[$key]) && $db_questions[$key]['question_type'] == 'rating') {
            $q_vals[$key] = isset($_POST[$key]) ? (int) $_POST[$key] : 5;
        } else {
            $q_vals[$key] = $mysqli->real_escape_string($_POST[$key] ?? '');
        }
    }

    $arrival_val   = $arrival_date ? "'$arrival_date'" : "NULL";
    $departure_val = $departure_date ? "'$departure_date'" : "NULL";

    $query = "INSERT INTO closure_report
                  (user_its, mauze, arrival_date, departure_date, days_of_service,
                   q1, q2, q3, q4, q5, q6, q7, q8, q9, q10, q11, q12, q13, q14, q15, q16, q17, q18, q19, q20, q21, q22, q23, q24)
                  VALUES
                  ($user_its, '$mauze_esc', $arrival_val, $departure_val, $days_of_service,
                   {$q_vals['q1']}, '{$q_vals['q2']}', '{$q_vals['q3']}', '{$q_vals['q4']}', '{$q_vals['q5']}',
                   '{$q_vals['q6']}', '{$q_vals['q7']}', '{$q_vals['q8']}', '{$q_vals['q9']}', '{$q_vals['q10']}',
                   '{$q_vals['q11']}', '{$q_vals['q12']}', '{$q_vals['q13']}', '{$q_vals['q14']}', '{$q_vals['q15']}',
                   '{$q_vals['q16']}', '{$q_vals['q17']}', '{$q_vals['q18']}', '{$q_vals['q19']}', {$q_vals['q20']},
                   {$q_vals['q21']}, {$q_vals['q22']}, '{$q_vals['q23']}', '{$q_vals['q24']}')";

    if (mysqli_query($mysqli, $query)) {
        $message        = ['type' => 'success', 'text' => 'تم تقديم تقرير الإغلاق بنجاح!'];
        $already_filled = true;
        $filled_on      = date('Y-m-d H:i:s');
    } else {
        $message = ['type' => 'danger', 'text' => 'خطأ: ' . $mysqli->error];
    }
    }

    // Helper to render question
    function renderQuestion($q_key, $db_questions)
    {
    if (! isset($db_questions[$q_key])) {
        return '';
    }

    $q     = $db_questions[$q_key];
    $html  = '<div class="q-block">';
    $html .= '<div class="q-title">' . $q['question_text'] . '</div>';

    if ($q['question_type'] == 'rating') {
        $html .= '<div class="slider-container">';
        $html .= '<div class="rating-value">5</div>';
        $html .= '<input type="range" name="' . $q_key . '" min="1" max="10" value="5">';
        $html .= '<div class="rating-labels"><span>1</span><span>10</span></div>';
        $html .= '</div>';
    } else {
        $placeholder  = ! empty($q['placeholder']) ? ' placeholder="' . htmlspecialchars($q['placeholder']) . '"' : '';
        $html        .= '<textarea name="' . $q_key . '" id="' . $q_key . '" required' . $placeholder . '></textarea>';
    }
    $html .= '</div>';
    return $html;
    }
?>

<script>document.body.dataset.userIts = "<?php echo $user_its; ?>";</script>

<style>
    @font-face {
        font-family: 'kanz';
        src: url('https://www.talabulilm.com/fonts/Kanz-al-Marjaan.ttf');
    }
    .arabic-font {
        font-family: 'kanz', serif;
        direction: rtl;
        text-align: right;
    }
    .main-content {
        background-color: #f4f7f6;
        padding: 40px 0;
    }
    .report-card {
        background: #ffffff;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        padding: 40px;
        margin-bottom: 30px;
        position: relative;
    }
    .section-title {
         font-family: 'kanz', serif;
        color: #1a1a2e;
        border-bottom: 2px solid #012970;
        padding-bottom: 10px;
        margin-bottom: 25px;
        font-weight: 700;
    }
    .q-block {
        margin-bottom: 30px;
    }
    .q-title {
        font-size: 1.4rem;
        font-weight: 600;
        margin-bottom: 12px;
        color: #333;
    }
    .slider-container {
        padding: 20px 0;
        position: relative;
    }
    input[type="range"] {
        width: 100%;
        margin: 15px 0;
        -webkit-appearance: none;
        height: 8px;
        border-radius: 5px;
        background: #e9ecef;
        outline: none;
    }
    input[type="range"]::-webkit-slider-runnable-track {
        height: 8px;
        border-radius: 5px;
        background: linear-gradient(to left, #012970 var(--value, 0%), #e9ecef var(--value, 0%));
    }
    input[type="range"]::-moz-range-track {
        height: 8px;
        border-radius: 5px;
        background: linear-gradient(to left, #012970 var(--value, 0%), #e9ecef var(--value, 0%));
    }
    input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #012970;
        cursor: pointer;
        border: 3px solid #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        margin-top: -8px;
    }
    .rating-labels {
        display: flex;
        justify-content: space-between;
        color: #666;
        font-size: 1.1rem;
    }
    textarea,
    input,
    select,
    button {
        font-family: 'kanz', serif !important;
    }
    .dark-blue{
        color: #012970;
        font-size: 1.4rem;
    }
    textarea {
        width: 100%;
        min-height: 120px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 1.2rem;
    }
    .profile-info {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 30px;
        border: 1px solid #e9ecef;
    }
    .profile-img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .btn-nav-row {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        gap: 15px;
    }
    .btn-submit, .btn-next, .btn-prev {
        padding: 12px 35px;
        border: none;
        border-radius: 30px;
        font-weight: 700;
        font-size: 1.1rem;
        cursor: pointer;
        transition: all 0.3s;
    }
    .btn-submit, .btn-next {
        background: #012970;
        color: white;
    }
    .btn-prev {
        background: #6c757d;
        color: white;
    }
    .btn-submit:hover, .btn-next:hover {
        background: #0b5ed7;
        transform: translateY(-2px);
    }
    .rating-value {
        text-align: center;
        font-size: 1.8rem;
        font-weight: 800;
        color: #012970;
        margin-bottom: 5px;
    }
    .form-section {
        display: none;
    }
    /* Progress Stepper */
    .progress-stepper {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 30px;
    }
    .progress-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #dee2e6;
        transition: all 0.3s;
    }
    .progress-dot.active {
        background: #012970;
        transform: scale(1.3);
    }
    .progress-dot.completed {
        background: #198754;
    }
    /* Draft Banner */
    .draft-banner {
        background: #e0f2fe;
        border: 1px solid #bae6fd;
        border-radius: 10px;
        padding: 12px 20px;
        font-size: 0.9rem;
        color: #0369a1;
        display: none;
        margin-bottom: 25px;
        align-items: center;
        gap: 15px;
    }
    .btn-clear-draft {
        background: #0369a1;
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        cursor: pointer;
    }
</style>

<main id="main" class="main arabic-font">
    <div class="container" style="max-width: 900px;">

        <div class="report-card">
            <h1 class="text-center mb-4 arabic-font" style="font-size: 2.2rem; font-weight: 800; color: #1a1a2e;">برنامج القراْن والعلم ــ Closure Report</h1>
            <!-- <p class="text-center text-muted mb-4" style="font-size: 1.1rem;">خدمة ككزار ني تفاصيل</p> -->

            <div class="progress-stepper">
                <div class="progress-dot"></div>
                <div class="progress-dot"></div>
                <div class="progress-dot"></div>
                <div class="progress-dot"></div>
                <div class="progress-dot"></div>
                <div class="progress-dot"></div>
                <div class="progress-dot"></div>
            </div>

            <div class="draft-banner" id="draftBanner">
                <span>&#9729; <strong>Draft found!</strong> Your unsaved progress has been restored.</span>
                <button type="button" class="btn-clear-draft" id="clearDraftBtn">Clear Draft</button>
            </div>

            <?php if ($message): ?>
                <!-- <div class="alert alert-<?php //echo $message['type']; ?> text-center">
                    <?php                          //echo $message['text']; ?>
                </div> -->
            <?php endif; ?>

            <?php if ($already_filled): ?>
                <div class="alert alert-info text-center py-4">
                    <h4 class="mb-2">Closure report submitted successfully</h4>

                </div>
            <?php else: ?>

            <form method="POST" action="">
                <!-- SECTION 1: BASIC INFO & INITIAL EXPERIENCE -->
                <div class="form-section">
                    <div class="profile-info">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center mb-3 mb-md-0">
                                <img src="<?php echo user_photo_url($user_its) ?>" class="profile-img" alt="Profile">
                            </div>
                            <div class="col-md-9" style="direction: ltr;">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td><?php echo htmlspecialchars($fullname) ?></td><td><strong>:الاسم</strong></td></tr>
                                    <tr><td><?php echo htmlspecialchars($user_its) ?></td><td><strong>:ITS ID</strong></td></tr>
                                    <tr><td><?php echo htmlspecialchars($mauze) ?></td><td><strong>:موضع</strong></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" style="direction: ltr; text-align: left;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="na_dates" name="na_dates">
                            <label class="form-check-label fw-bold dark-blue" for="na_dates">
                                Select N/A if your BQI Mauze is the same place as your current
                            </label>
                        </div>
                    </div>

                    <div class="row mb-4" style="direction: ltr; text-align: left;">
                        <div class="col-md-4 mb-3">
                            <label class="q-title">Date of Arrival</label>
                            <input type="date" name="arrival_date" id="arrival_date" class="form-control"
                                   min="2026-01-01" max="2026-03-31" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="q-title">Date of Departure</label>
                            <input type="date" name="departure_date" id="departure_date" class="form-control"
                                   min="2026-01-01" max="2026-03-31" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="q-title">ايام الخدمة</label>
                            <input type="number" name="days_of_service" id="days_of_service" class="form-control" readonly required>
                        </div>
                    </div>

                    <?php
                        echo renderQuestion('q1', $db_questions);
                        echo renderQuestion('q2', $db_questions);
                    ?>

                    <div class="btn-nav-row">
                        <span></span>
                        <button type="button" class="btn-next">Next &#10095;</button>
                    </div>
                </div>

                <!-- SECTION 2: SERVICE OUTCOMES & CHALLENGES -->
                <div class="form-section">
                    <h3 class="section-title">برنامج القراْن و العلم ني خدمة نو تجربھ</h3>
                    <?php
                        echo renderQuestion('q3', $db_questions);
                        echo renderQuestion('q4', $db_questions);
                        echo renderQuestion('q5', $db_questions);
                        echo renderQuestion('q6', $db_questions);
                    ?>

                    <div class="btn-nav-row">
                        <button type="button" class="btn-prev">&#10094; Previous</button>
                        <button type="button" class="btn-next">Next &#10095;</button>
                    </div>
                </div>

                <!-- SECTION 3: ZAKEREEN FARZANDO -->
                <div class="form-section">
                    <h3 class="section-title">ذاكرين فرزندو متعلق</h3>
                    <?php
                        echo renderQuestion('q7', $db_questions);
                        echo renderQuestion('q8', $db_questions);
                        echo renderQuestion('q9', $db_questions);
                        echo renderQuestion('q10', $db_questions);
                    ?>

                    <div class="btn-nav-row">
                        <button type="button" class="btn-prev">&#10094; Previous</button>
                        <button type="button" class="btn-next">Next &#10095;</button>
                    </div>
                </div>

                <!-- SECTION 4: SOCIAL MEDIA AWARENESS -->
                <div class="form-section">
                    <h3 class="section-title">Social Media Awareness متعلق</h3>
                    <?php
                        echo renderQuestion('q11', $db_questions);
                        echo renderQuestion('q12', $db_questions);
                        echo renderQuestion('q13', $db_questions);
                        echo renderQuestion('q14', $db_questions);
                        echo renderQuestion('q15', $db_questions);
                    ?>

                    <div class="btn-nav-row">
                        <button type="button" class="btn-prev">&#10094; Previous</button>
                        <button type="button" class="btn-next">Next &#10095;</button>
                    </div>
                </div>

                <!-- SECTION 5: MARRIAGE -->
                <div class="form-section">
                    <h3 class="section-title">شادي تفظظيم متعلق</h3>
                    <?php
                        echo renderQuestion('q16', $db_questions);
                        echo renderQuestion('q17', $db_questions);
                        echo renderQuestion('q18', $db_questions);
                        echo renderQuestion('q19', $db_questions);
                    ?>

                    <div class="btn-nav-row">
                        <button type="button" class="btn-prev">&#10094; Previous</button>
                        <button type="button" class="btn-next">Next &#10095;</button>
                    </div>
                </div>

                <!-- SECTION 6: BACK OFFICE & CLOSING IMPRESSIONS -->
                <div class="form-section">
                    <h3 class="section-title">مكتب المتابعة (BQI back office) متعلق</h3>
                    <?php
                        echo renderQuestion('q20', $db_questions);
                        echo renderQuestion('q21', $db_questions);
                        echo renderQuestion('q22', $db_questions);
                        echo renderQuestion('q23', $db_questions);
                    ?>

                    <div class="btn-nav-row">
                        <button type="button" class="btn-prev">&#10094; Previous</button>
                        <button type="button" class="btn-next">Next &#10095;</button>
                    </div>
                </div>

                <!-- SECTION 7: SHUKR -->
                <div class="form-section">
                    <h3 class="section-title">شكر عرض</h3>
                    <?php
                        echo renderQuestion('q24', $db_questions);
                    ?>

                    <div class="btn-nav-row">
                        <button type="button" class="btn-prev">&#10094; Previous</button>
                        <button type="submit" name="submit_closure" class="btn-submit">Submit Closure Report</button>
                    </div>
                </div>

            </form>

            <?php endif; ?>
        </div>
    </div>
</main>

<?php
    require_once __DIR__ . '/inc/footer.php';
    require_once __DIR__ . '/inc/js-block.php';
?>
<script src="assets/js/closure_report.js?v=1.1"></script>
