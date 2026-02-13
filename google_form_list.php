<?php
require_once( __DIR__. '/session.php');
$current_page = 'google_form_list';
require_once( __DIR__. '/inc/header.php');

$mauze = $rowdata['miqaat_mauze'];
$name = $rowdata['fullname'];
$user_its = (int) $_SESSION[USER_ITS];

// Get all available forms
$query = "SELECT * FROM `google_forms` ORDER BY upload_date DESC";
$result = mysqli_query($mysqli, $query);
$forms = $result->fetch_all(MYSQLI_ASSOC);

// Handle form view request
$selectedForm = null;
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $formQuery = "SELECT * FROM `google_forms` WHERE id = $id";
    $formResult = mysqli_query($mysqli, $formQuery);
    if ($formResult && mysqli_num_rows($formResult) > 0) {
        $selectedForm = mysqli_fetch_assoc($formResult);
    }
}
?>

<main id="main" class="main">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Daily Feedback Forms</h5>
                        
                        <?php if (empty($forms)): ?>
                            <div class="alert alert-info">
                                No forms are available at this time.
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <!-- Form List -->
                                <div class="col-md-4">
                                    <div class="list-group">
                                        <?php foreach ($forms as $form): ?>
                                            <a href="google_form_list.php?id=<?= $form['id'] ?>" class="list-group-item list-group-item-action <?= (isset($_GET['id']) && $_GET['id'] == $form['id']) ? 'active' : '' ?>">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h5 class="mb-1"><?= htmlspecialchars($form['form_title']) ?></h5>
                                                </div>
                                                <small>Added: <?= date('M d, Y', strtotime($form['upload_date'])) ?></small>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <!-- Form Display Area -->
                                <div class="col-md-8">
                                    <?php if ($selectedForm): ?>
                                        <div class="card">
                                            <div class="card-header">
                                                <h5><?= htmlspecialchars($selectedForm['form_title']) ?></h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="embed-responsive">
                                                    <?php
                                                    // Get the form link
                                                    $formLink = $selectedForm['form_link'];
                                                    
                                                    // Replace ITS_ID with user_its and MAUZE with mauze
                                                    $formLink = str_replace('ITS_ID', $user_its, $formLink);
                                                    $formLink = str_replace('MAUZE', $mauze, $formLink);
                                                    $formLink = str_replace('NAME', $name, $formLink);
                                                    
                                                    // Check if it's already an embed link, if not convert it
                                                    if (strpos($formLink, 'viewform') !== false && strpos($formLink, 'embedded=true') === false) {
                                                        // Convert regular form link to embedded version
                                                        $formLink = str_replace('viewform', 'viewform?embedded=true', $formLink);
                                                    }
                                                    ?>
                                                    <iframe src="<?= htmlspecialchars($formLink) ?>" width="100%" height="700px" frameborder="0" marginheight="0" marginwidth="0">Loading…</iframe>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="card">
                                            <div class="card-body text-center p-5">
                                                <h4 class="text-muted">Select a form from the list to view</h4>
                                                <p class="text-muted">The form will appear here when selected</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->

<?php
require_once( __DIR__. '/inc/footer.php');
?>