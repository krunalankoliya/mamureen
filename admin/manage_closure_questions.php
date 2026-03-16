<?php
    require_once __DIR__ . '/../session.php';
    $current_page = 'manage_closure_questions';
    require_once __DIR__ . '/../inc/header.php';

    // Admin-only guard
    if (! $is_admin) {
    echo '<main id="main" class="main"><div class="alert alert-danger m-4">Access denied.</div></main>';
    require_once __DIR__ . '/../inc/footer.php';
    exit();
    }

    $question_type_options = ['text', 'rating'];

    // ── Handle Edit ───────────────────────────────────────────────────────────
    if (isset($_POST['update_question'])) {
    $edit_id       = (int) $_POST['edit_id'];
    $section_id    = (int) ($_POST['section_id'] ?? 1);
    $question_text = trim(mysqli_real_escape_string($mysqli, $_POST['question_text']));
    $question_type = in_array($_POST['question_type'] ?? '', $question_type_options)
        ? $_POST['question_type'] : 'text';
    $placeholder = trim(mysqli_real_escape_string($mysqli, $_POST['placeholder'] ?? ''));

    if (empty($question_text) || $section_id < 1) {
        $message = ['text' => 'Section and Question Text are required.', 'tag' => 'danger'];
    } else {
        $ph_val = $placeholder !== '' ? "'$placeholder'" : 'NULL';
        $query  = "UPDATE `closure_report_questions`
                       SET `section_id`    = '$section_id',
                           `question_text` = '$question_text',
                           `question_type` = '$question_type',
                           `placeholder`  = $ph_val
                       WHERE `id` = '$edit_id'";
        try {
            mysqli_query($mysqli, $query);
            $message = ['text' => 'Question updated successfully.', 'tag' => 'success'];
        } catch (Exception $e) {
            $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
        }
    }
    }

    // ── Handle Delete ─────────────────────────────────────────────────────────
    if (isset($_POST['delete_question'])) {
    $delete_id = (int) $_POST['delete_id'];
    $query     = "DELETE FROM `closure_report_questions` WHERE `id` = '$delete_id'";
    try {
        mysqli_query($mysqli, $query);
        $message = ['text' => 'Question deleted successfully.', 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
    }

    // ── Fetch all questions ───────────────────────────────────────────────────
    $questions_result = mysqli_query($mysqli, "SELECT * FROM `closure_report_questions` ORDER BY `section_id`, `id` ASC");
    $questions        = $questions_result->fetch_all(MYSQLI_ASSOC);

    // Group by section for display
    $sections = [];
    foreach ($questions as $q) {
    $sections[$q['section_id']][] = $q;
    }
    ksort($sections);
?>

<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <?php require_once __DIR__ . '/../inc/messages.php'; ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Manage Closure Report Questions</h5>

                    <!-- ── Questions Table ───────────────────────────────────── -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle" id="datatable_closure_q">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Key</th>
                                    <th>Section</th>
                                    <th>Type</th>
                                    <th style="min-width:300px;">Question Text</th>
                                    <th>Placeholder</th>
                                    <th>Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($questions as $idx => $q): ?>
                                    <tr>
                                        <td><?php echo $idx + 1 ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($q['q_key']) ?></span></td>
                                        <td class="text-center"><?php echo (int) $q['section_id'] ?></td>
                                        <td>
                                            <?php if ($q['question_type'] === 'rating'): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-star-fill me-1"></i>Rating
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-info text-dark">
                                                    <i class="bi bi-text-left me-1"></i>Text
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td dir="auto"><?php echo htmlspecialchars($q['question_text']) ?></td>
                                        <td dir="auto" class="text-muted small">
                                            <?php echo htmlspecialchars($q['placeholder'] ?? '') ?>
                                        </td>
                                        <td class="small text-muted text-nowrap">
                                            <?php echo date('d-M-Y', strtotime($q['update_ts'])) ?>
                                        </td>
                                        <td class="text-nowrap">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary edit-btn"
                                                    data-id="<?php echo $q['id'] ?>"
                                                    data-qkey="<?php echo htmlspecialchars($q['q_key']) ?>"
                                                    data-section="<?php echo (int) $q['section_id'] ?>"
                                                    data-type="<?php echo htmlspecialchars($q['question_type']) ?>"
                                                    data-text="<?php echo htmlspecialchars($q['question_text']) ?>"
                                                    data-placeholder="<?php echo htmlspecialchars($q['placeholder'] ?? '') ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form method="post" class="d-inline delete-form">
                                                <input type="hidden" name="delete_id" value="<?php echo $q['id'] ?>">
                                                <button type="submit" name="delete_question"
                                                        class="btn btn-sm btn-outline-danger delete-btn"
                                                        title="Delete">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ── Section Summary ───────────────────────────────────── -->
                    <div class="mt-4">
                        <h6 class="text-muted">Questions by Section</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($sections as $sec_id => $sec_questions): ?>
                                <span class="badge bg-primary fs-6">
                                    Section <?php echo $sec_id ?>:
                                    <?php echo count($sec_questions) ?> question<?php echo count($sec_questions) !== 1 ? 's' : '' ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</main>

<!-- ── Edit Modal ─────────────────────────────────────────────────────────── -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" id="editQuestionForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">
                        Edit Question <span class="badge bg-secondary ms-2" id="modal_qkey_display"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Section ID</label>
                            <input type="number" name="section_id" id="edit_section" class="form-control" min="1" max="10" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Question Type</label>
                            <select name="question_type" id="edit_question_type" class="form-select">
                                <option value="text">Text</option>
                                <option value="rating">Rating</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Question Text</label>
                            <textarea name="question_text" id="edit_question_text" class="form-control"
                                      rows="3" dir="auto" required></textarea>
                        </div>
                        <div class="col-md-12" id="edit_placeholder_row">
                            <label class="form-label">
                                Placeholder <small class="text-muted">(optional, for text questions only)</small>
                            </label>
                            <input type="text" name="placeholder" id="edit_placeholder" class="form-control" dir="auto">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_question" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
</body>
<script src="<?php echo MODULE_PATH ?>assets/js/admin/manage_closure_questions.js?v=1.0"></script>
</html>
