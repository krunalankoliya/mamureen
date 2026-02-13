<?php
require_once( __DIR__. '/../session.php');

// Admin check - uncomment this for production
// $admin_its = array('50476733', '40444857', '20372359', '30419712');
// if(!in_array($user_its, $admin_its)) {
//     header('location:index.php');
//     exit;
// }

$current_page = 'Form_Submissions';
require_once( __DIR__. '/../inc/header.php');

// Handle export request
if(isset($_GET['export']) && $_GET['export'] == 'csv' && isset($_GET['form_id'])) {
    $form_id = intval($_GET['form_id']);
    
    // Get form details
    $formQuery = "SELECT form_title FROM `google_forms` WHERE id = $form_id";
    $formResult = mysqli_query($mysqli, $formQuery);
    $formDetails = mysqli_fetch_assoc($formResult);
    $filename = "submissions_" . preg_replace('/[^a-z0-9]/', '_', strtolower($formDetails['form_title'])) . "_" . date('Y-m-d') . ".csv";
    
    // Get submissions for this form
    $query = "SELECT gs.*, gf.form_title FROM `google_form_submissions` gs 
              JOIN `google_forms` gf ON gs.form_id = gf.id
              WHERE gs.form_id = $form_id ORDER BY gs.submission_date DESC";
    $result = mysqli_query($mysqli, $query);
    
    if($result && mysqli_num_rows($result) > 0) {
        // Start output buffering
        ob_start();
        
        // Create CSV file
        $output = fopen('php://output', 'w');
        
        // Get the first row to determine headers
        $first_row = mysqli_fetch_assoc($result);
        $submission_data = json_decode($first_row['submission_data'], true);
        
        // Create headers array
        $headers = ['ID', 'User ITS', 'Submission Date'];
        foreach($submission_data as $key => $value) {
            $headers[] = $key;  
        }
        
        // Write headers
        fputcsv($output, $headers);
        
        // Reset pointer and start writing data
        mysqli_data_seek($result, 0);
        while($row = mysqli_fetch_assoc($result)) {
            $submission_data = json_decode($row['submission_data'], true);
            
            $data_row = [
                $row['id'],
                $row['user_its'],
                $row['submission_date']
            ];
            
            // Add submission data fields
            foreach($headers as $index => $header) {
                if($index > 2) { // Skip first 3 headers (ID, User ITS, Submission Date)
                    $data_row[] = $submission_data[$header] ?? '';
                }
            }
            
            fputcsv($output, $data_row);
        }
        
        // Get the contents of the output buffer
        $csv_content = ob_get_clean();
        
        // Set appropriate headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Output the CSV content
        echo $csv_content;
        exit;
    }
}

// Get all forms
$formsQuery = "SELECT * FROM `google_forms` ORDER BY form_title ASC";
$formsResult = mysqli_query($mysqli, $formsQuery);
$forms = $formsResult->fetch_all(MYSQLI_ASSOC);

// Handle form filter
$form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;

// Get submissions with pagination
$limit = 20;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$countQuery = "SELECT COUNT(*) as total FROM `google_form_submissions`";
if($form_id > 0) {
    $countQuery .= " WHERE form_id = $form_id";
}
$countResult = mysqli_query($mysqli, $countQuery);
$totalRows = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRows / $limit);

$query = "SELECT gs.*, gf.form_title FROM `google_form_submissions` gs 
          JOIN `google_forms` gf ON gs.form_id = gf.id";
if($form_id > 0) {
    $query .= " WHERE gs.form_id = $form_id";
}
$query .= " ORDER BY gs.submission_date DESC LIMIT $offset, $limit";

$result = mysqli_query($mysqli, $query);
$submissions = $result->fetch_all(MYSQLI_ASSOC);
?>

<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <?php require_once(__DIR__ . '/../inc/messages.php'); ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Submissions</h5>
                    
                    <!-- Filter By Form -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form method="get" class="d-flex">
                                <select name="form_id" class="form-select me-2">
                                    <option value="0">All Forms</option>
                                    <?php foreach($forms as $form): ?>
                                        <option value="<?= $form['id'] ?>" <?= ($form_id == $form['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($form['form_title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <?php if($form_id > 0): ?>
                                    <a href="view_submissions.php?export=csv&form_id=<?= $form_id ?>" class="btn btn-success ms-2">
                                        Export to CSV
                                    </a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    
                    <?php if(empty($submissions)): ?>
                        <div class="alert alert-info">
                            No submissions found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Form</th>
                                        <th>User ITS</th>
                                        <th>Submission Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($submissions as $submission): ?>
                                        <tr>
                                            <td><?= $submission['id'] ?></td>
                                            <td><?= htmlspecialchars($submission['form_title']) ?></td>
                                            <td><?= htmlspecialchars($submission['user_its']) ?></td>
                                            <td><?= date('M d, Y H:i', strtotime($submission['submission_date'])) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewModal<?= $submission['id'] ?>">
                                                    View Details
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- View Modal -->
                                        <div class="modal fade" id="viewModal<?= $submission['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Submission Details: <?= htmlspecialchars($submission['form_title']) ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Submission ID:</div>
                                                            <div class="col-md-8"><?= $submission['id'] ?></div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">User ITS:</div>
                                                            <div class="col-md-8"><?= htmlspecialchars($submission['user_its']) ?></div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Submission Date:</div>
                                                            <div class="col-md-8"><?= date('M d, Y H:i:s', strtotime($submission['submission_date'])) ?></div>
                                                        </div>
                                                        <hr>
                                                        <h6 class="mb-3">Form Data:</h6>
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Field</th>
                                                                        <th>Value</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php 
                                                                    $form_data = json_decode($submission['submission_data'], true);
                                                                    if($form_data && is_array($form_data)) {
                                                                        foreach($form_data as $key => $value) {
                                                                            echo '<tr>';
                                                                            echo '<td>' . htmlspecialchars($key) . '</td>';
                                                                            echo '<td>' . htmlspecialchars($value) . '</td>';
                                                                            echo '</tr>';
                                                                        }
                                                                    } else {
                                                                        echo '<tr><td colspan="2">No form data available</td></tr>';
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if($totalPages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php if($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page-1 ?><?= $form_id ? '&form_id='.$form_id : '' ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?><?= $form_id ? '&form_id='.$form_id : '' ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page+1 ?><?= $form_id ? '&form_id='.$form_id : '' ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
require_once( __DIR__. '/../inc/footer.php');
?>