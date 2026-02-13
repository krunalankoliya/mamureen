<?php
// Early exit pattern removed as it's immediately followed by required files
require_once(__DIR__ . '/session.php');
$current_page = 'journal';
require_once(__DIR__ . '/inc/header.php');

// Constants
define('LOG_FILE_PATH', __DIR__ . '/journal.log');

// Initialize variables
$message = null;
$formData = [
    'a1' => '', 'a2' => '', 'a3' => '', 'a4' => '', 
    'a5' => '', 'a6' => '', 'a7' => ''
];

/**
 * Logs database queries to a file
 *
 * @param string $query The SQL query
 * @param string|null $message Additional message
 * @return void
 */
function logQuery($query, $message = null) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "$timestamp - Query: $query" . PHP_EOL;
    
    if ($message) {
        $logMessage .= "Message: $message" . PHP_EOL;
    }

    file_put_contents(LOG_FILE_PATH, $logMessage, FILE_APPEND);
}

/**
 * Sanitizes and validates form input
 *
 * @param array $postData The POST data to sanitize
 * @return array Sanitized form data
 */
function sanitizeFormInput($postData) {
    global $mysqli;
    $sanitized = [];
    
    $fieldNames = ['a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7'];
    
    foreach ($fieldNames as $field) {
        $sanitized[$field] = isset($postData[$field]) ? 
            $mysqli->real_escape_string($postData[$field]) : '';
    }
    
    return $sanitized;
}

/**
 * Fetches journal data for a user
 *
 * @param string $userId The user ID
 * @return array|false The journal data or false if not found
 */
function fetchJournalData($userId) {
    global $mysqli;
    
    $query = "SELECT * FROM `journal` WHERE `its_id` = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Saves journal data for a user
 *
 * @param string $userId The user ID
 * @param array $data The journal data to save
 * @return array Result message
 */
function saveJournalData($userId, $data) {
    global $mysqli;
    
    $currentTimestamp = date('Y-m-d H:i:s');
    
    $query = "INSERT INTO `journal` 
              (`its_id`, `a1`, `a2`, `a3`, `a4`, `a5`, `a6`, `a7`) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
              ON DUPLICATE KEY UPDATE 
              `a1`=VALUES(`a1`), `a2`=VALUES(`a2`), `a3`=VALUES(`a3`), 
              `a4`=VALUES(`a4`), `a5`=VALUES(`a5`), `a6`=VALUES(`a6`), 
              `a7`=VALUES(`a7`), `update_ts` = ?";
    
    try {
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('sssssssss', 
            $userId, 
            $data['a1'], $data['a2'], $data['a3'], $data['a4'], 
            $data['a5'], $data['a6'], $data['a7'], 
            $currentTimestamp
        );
        
        if ($stmt->execute()) {
            return ['text' => 'Journal Added Successfully', 'tag' => 'success'];
        } else {
            throw new Exception($mysqli->error);
        }
    } catch (Exception $e) {
        logQuery($query, $e->getMessage());
        return ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
}

// Process form submission
if (isset($_POST['submit'])) {
    $sanitizedData = sanitizeFormInput($_POST);
    $message = saveJournalData($user_its, $sanitizedData);
    $formData = $sanitizedData; // Preserve user input on error
}

// Fetch existing data if not coming from a form submission
if (!isset($_POST['submit'])) {
    $userData = fetchJournalData($user_its);
    if ($userData) {
        $formData = [
            'a1' => $userData['a1'],
            'a2' => $userData['a2'],
            'a3' => $userData['a3'],
            'a4' => $userData['a4'],
            'a5' => $userData['a5'],
            'a6' => $userData['a6'],
            'a7' => $userData['a7']
        ];
    }
}
?>

<style>
    @font-face {
        font-family: "kanz";
        src: url('<?= MODULE_PATH ?>assets/fonts/Kanz-al-Marjaan.ttf');
    }

    .card, .submit_form .btn {
        font-size: 24px;
    }
    
    .name-div {
        margin: auto 0;
    }
    
    .pl-2 {
        padding-left: 1rem;
    }
    
    span.required_star {
        color: red;
    }
    
    .question-container {
        margin-bottom: 2rem;
    }
</style>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Journal</h1>
    </div><!-- End Page Title -->
    
    <section class="section dashboard">
        <div class="row">
            <?php require_once(__DIR__ . '/inc/messages.php'); ?>
            
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title">Add Journal</h1>
                    
                    <div class="row">
                        <div class="col">
                            <form method="post" dir="rtl" style="font-family: kanz" class="submit_form" action=''>
                                <?php
                                $questions = [
                                    'منسس موضع الخدمة نا مؤمنين سي يه سككلا ني تصورات انسس تجارب سني نسس مولانا المنعام سيدنا عالي قدر مفضل سيف الدين ط ع نا مقام ني معرفة ما سوطط مزيد بلندي حاصل تهئي؟',
                                    'اْ خدمة انجام ديوا نا سبب - ميطط ماري ذات نسس مولانا المنعام سيدنا عالي قدر مفضل سيف الدين ط ع ني خوشي موافق لسان الدعوة ما} وات كروا ني عادة ما كتنو بلند كيدو؟',
                                    'اثناء الخدمة ميطط يھ رأي انسس مشورة سي مؤمنين ني كئي طرح مدد كيدي؟',
                                    'اْ خدمة نا دوران مؤمنين مخلصين سي مشورة لئي نسس عمل كروا ما سوطط فوائد حاصل تهيا؟',
                                    'اْخدمة نا طُفيل ما ميطط كئي جديد مظظارات نسس اكتساب كيدي؟',
                                    'مارا نزديك ___ مظظارات ؛، اهنسس ميطط يھ اْخدمة ما كئي طرح استعمال كيدي',
                                    'مولانا المنعام سيدنا عالي قدر مفضل سيف الدين ط ع ني حول انسس قوة سي خدمة نا ميدان ما جھ challenges اْيا - اهنسس كئي طرح حَل كيدا؟'
                                ];
                                
                                foreach ($questions as $index => $question) {
                                    $fieldName = 'a' . ($index + 1);
                                    echo '<div class="question-container">';
                                    echo '<label class="form-check-label">' . $question . '</label>';
                                    echo '<textarea class="form-control" name="' . $fieldName . '" style="height: 80px">' . htmlspecialchars($formData[$fieldName]) . '</textarea>';
                                    echo '</div>';
                                }
                                ?>
                                
                                <!-- Form buttons -->
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="text-center">
                                            <div class="d-grid gap-2">
                                                <input type="reset" value="Reset Form" name="reset" class="btn btn-outline-secondary" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-center">
                                            <div class="d-grid gap-2">
                                                <input type="submit" value="Submit" name="submit" class="btn btn-primary" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->

<?php
require_once(__DIR__ . '/inc/footer.php');
?>