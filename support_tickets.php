<?php
    $current_page = 'support_tickets';
    require_once __DIR__ . '/session.php';
    require_once __DIR__ . '/inc/header.php';

    // ── Fetch logged-in user's email (checks all three user tables) ──
    if ($is_admin) {
        $email_table = 'users_admin';
    } elseif ($is_sub_admin) {
        $email_table = 'users_sub_admin';
    } else {
        $email_table = 'users_mamureen';
    }
    $user_email_res  = mysqli_query($mysqli, "SELECT `email_id` FROM `{$email_table}` WHERE `its_id` = '$user_its'");
    $user_email      = ($user_email_res && $user_email_res->num_rows) ? ($user_email_res->fetch_assoc()['email_id'] ?? '') : '';
    $safe_user_email = mysqli_real_escape_string($mysqli, $user_email);

    // ── Handle: Create Ticket ────────────────────────────────────
    if (isset($_POST['create_ticket'])) {
    $subject     = trim(mysqli_real_escape_string($mysqli, $_POST['subject']));
    $description = trim(mysqli_real_escape_string($mysqli, $_POST['description']));
    $priority    = in_array($_POST['priority'], ['low', 'medium', 'high']) ? $_POST['priority'] : 'medium';

    if (empty($subject) || empty($description)) {
        $message = ['text' => 'Subject and description are required.', 'tag' => 'danger'];
    } else {
        $insert = "INSERT INTO `support_tickets` (`ticket_number`, `subject`, `description`, `priority`, `added_its`, `added_by_name`, `email`, `miqaat_mauze`)
                   VALUES ('TMP', '$subject', '$description', '$priority', '$user_its', '$fullname', '$safe_user_email', '$mauze')";
        mysqli_query($mysqli, $insert);
        $new_id        = mysqli_insert_id($mysqli);
        $ticket_number = 'TKT-' . str_pad($new_id, 6, '0', STR_PAD_LEFT);
        mysqli_query($mysqli, "UPDATE `support_tickets` SET `ticket_number` = '$ticket_number' WHERE `id` = '$new_id'");
        $message = ['text' => "Ticket <strong>{$ticket_number}</strong> submitted successfully. We will get back to you shortly.", 'tag' => 'success'];
    }
    }

    // ── Handle: User Reply ───────────────────────────────────────
    if (isset($_POST['add_reply'])) {
    $ticket_id  = (int) $_POST['ticket_id'];
    $reply_text = trim(mysqli_real_escape_string($mysqli, $_POST['reply_text']));

    // Ensure ticket belongs to this user
    $chk = mysqli_query($mysqli, "SELECT id FROM `support_tickets` WHERE `id` = '$ticket_id' AND `added_its` = '$user_its'");
    if ($chk && $chk->num_rows > 0 && ! empty($reply_text)) {
        mysqli_query($mysqli, "INSERT INTO `support_ticket_replies` (`ticket_id`, `reply_text`, `replied_by_its`, `replied_by_name`, `is_admin_reply`)
                               VALUES ('$ticket_id', '$reply_text', '$user_its', '$fullname', 0)");
        mysqli_query($mysqli, "UPDATE `support_tickets` SET `updated_at` = NOW() WHERE `id` = '$ticket_id'");
        $message = ['text' => 'Reply added.', 'tag' => 'success'];
    }
    }

    // ── Fetch this user's tickets ────────────────────────────────
    $tickets = mysqli_query($mysqli, "SELECT * FROM `support_tickets` WHERE `added_its` = '$user_its' ORDER BY `created_at` DESC")
    ->fetch_all(MYSQLI_ASSOC);

    // ── Fetch ticket thread for modal (AJAX-style via GET) ───────
    $modal_ticket  = null;
    $modal_replies = [];
    if (isset($_GET['view']) && (int) $_GET['view'] > 0) {
    $view_id = (int) $_GET['view'];
    $res     = mysqli_query($mysqli, "SELECT * FROM `support_tickets` WHERE `id` = '$view_id' AND `added_its` = '$user_its'");
    if ($res && $res->num_rows) {
        $modal_ticket  = $res->fetch_assoc();
        $modal_replies = mysqli_query($mysqli, "SELECT * FROM `support_ticket_replies` WHERE `ticket_id` = '$view_id' ORDER BY `created_at` ASC")
            ->fetch_all(MYSQLI_ASSOC);
    }
    }
?>

<style>
.ticket-thread { max-height: 420px; overflow-y: auto; }
.bubble       { border-radius: 12px; padding: 10px 14px; margin-bottom: 10px; max-width: 85%; }
.bubble-user  { background: #e8f0fe; margin-left: auto; text-align: right; }
.bubble-admin { background: #e6f4ea; }
.bubble small { display: block; margin-top: 4px; color: #666; font-size: 11px; }
.priority-low    { color: #2eca6a; font-weight: 600; }
.priority-medium { color: #ff9f43; font-weight: 600; }
.priority-high   { color: #dc3545; font-weight: 600; }
/* Custom ticket popup — not a Bootstrap modal (avoids JS interference) */
.ticket-popup-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.55); z-index: 1055; overflow-y: auto; padding: 40px 15px;
}
.ticket-popup-box {
    max-width: 820px; margin: 0 auto; background: #fff;
    border-radius: 8px; box-shadow: 0 6px 24px rgba(0,0,0,0.25);
}
.ticket-popup-header {
    padding: 16px 20px; border-bottom: 1px solid #dee2e6;
    display: flex; justify-content: space-between; align-items: flex-start;
}
.ticket-popup-body { padding: 20px; max-height: 65vh; overflow-y: auto; }
.ticket-popup-footer { padding: 12px 20px; border-top: 1px solid #dee2e6; text-align: right; }
</style>

<main id="main" class="main bqi-1447">
    <div class="pagetitle">
        <h1>Support Queries</h1>
    </div>

    <section class="section">
        <div class="row">
            <?php require_once __DIR__ . '/inc/messages.php'; ?>

            <!-- Create Ticket -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-plus-circle-fill text-primary"></i> Open New Query</h5>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Subject <span class="required_star">*</span></label>
                                <select name="subject" class="form-select" required>
                                    <option value="" disabled selected>Select Subject...</option>
                                    <option value="Shaadi Tafheem">Shaadi Tafheem</option>
                                    <option value="Social Media Awareness">Social Media Awareness</option>
                                    <option value="Zakereen Farzando Training">Zakereen Farzando Training</option>
                                    <option value="General">General</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notification Email</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_email ?: 'No email on record') ?>" disabled>
                                <small class="text-muted">Reply notifications will be sent to this email.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description <span class="required_star">*</span></label>
                                <textarea name="description" class="form-control" rows="5" placeholder="Describe your issue in detail..." required></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="create_ticket" class="btn btn-primary">
                                    <i class="bi bi-send-fill"></i> Submit Ticket
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- My Tickets Table -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-ticket-detailed-fill text-primary"></i> My Queries</h5>
                        <div style="overflow-x: auto;">
                            <table class="table table-striped" id="datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Ticket No.</th>
                                        <th>Subject</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tickets as $i => $t): ?>
                                        <tr>
                                            <td><?php echo $i + 1 ?></td>
                                            <td><code><?php echo htmlspecialchars($t['ticket_number']) ?></code></td>
                                            <td><?php echo htmlspecialchars($t['subject']) ?></td>
                                            <td>
                                                <?php
                                                    $pc = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger'];
                                                    echo '<span class="badge bg-' . ($pc[$t['priority']] ?? 'secondary') . '">' . ucfirst($t['priority']) . '</span>';
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $sc = ['open' => 'primary', 'in_progress' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'];
                                                    $sl = ['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'];
                                                    echo '<span class="badge bg-' . ($sc[$t['status']] ?? 'secondary') . '">' . ($sl[$t['status']] ?? ucfirst($t['status'])) . '</span>';
                                                ?>
                                            </td>
                                            <td><?php echo date('d-M-Y', strtotime($t['created_at'])) ?></td>
                                            <td>
                                                <a href="?view=<?php echo $t['id'] ?>" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye-fill"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</main>

<!-- ── View Ticket Popup ──────────────────────────────────── -->
<?php if ($modal_ticket):
    $sc = ['open' => 'primary', 'in_progress' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'];
    $sl = ['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'];
    $pc = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger'];
?>
<div class="ticket-popup-overlay">
    <div class="ticket-popup-box">
        <div class="ticket-popup-header">
            <div>
                <h5 class="mb-1">
                    <code><?php echo htmlspecialchars($modal_ticket['ticket_number']) ?></code>
                    &mdash; <?php echo htmlspecialchars($modal_ticket['subject']) ?>
                </h5>
                <span class="badge bg-<?php echo $sc[$modal_ticket['status']] ?? 'secondary' ?>"><?php echo $sl[$modal_ticket['status']] ?? ucfirst($modal_ticket['status']) ?></span>
                <span class="badge bg-<?php echo $pc[$modal_ticket['priority']] ?? 'secondary' ?>">Priority: <?php echo ucfirst($modal_ticket['priority']) ?></span>
            </div>
            <a href="support_tickets.php" class="btn-close"></a>
        </div>
        <div class="ticket-popup-body">
            <p class="text-muted small mb-3">
                Submitted on <?php echo date('d-M-Y H:i', strtotime($modal_ticket['created_at'])) ?>
                &bull; <?php echo htmlspecialchars($modal_ticket['miqaat_mauze'] ?? '') ?>
            </p>
            <hr>

            <!-- Thread -->
            <div class="ticket-thread mb-3">
                <div class="bubble bubble-user">
                    <strong><?php echo htmlspecialchars($modal_ticket['added_by_name'] ?? 'You') ?></strong><br>
                    <?php echo nl2br(htmlspecialchars($modal_ticket['description'])) ?>
                    <small><?php echo date('d-M-Y H:i', strtotime($modal_ticket['created_at'])) ?></small>
                </div>
                <?php foreach ($modal_replies as $r): ?>
                    <?php if ($r['is_admin_reply']): ?>
                        <div class="bubble bubble-admin">
                            <strong><i class="bi bi-shield-check text-success"></i> BQI Office</strong><br>
                            <?php echo nl2br(htmlspecialchars($r['reply_text'])) ?>
                            <small><?php echo date('d-M-Y H:i', strtotime($r['created_at'])) ?></small>
                        </div>
                    <?php else: ?>
                        <div class="bubble bubble-user">
                            <strong><?php echo htmlspecialchars($r['replied_by_name'] ?? 'You') ?></strong><br>
                            <?php echo nl2br(htmlspecialchars($r['reply_text'])) ?>
                            <small><?php echo date('d-M-Y H:i', strtotime($r['created_at'])) ?></small>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Reply Form (only if not closed/resolved) -->
            <?php if (! in_array($modal_ticket['status'], ['resolved', 'closed'])): ?>
                <hr>
                <form method="post">
                    <input type="hidden" name="ticket_id" value="<?php echo $modal_ticket['id'] ?>">
                    <div class="mb-2">
                        <label class="form-label fw-bold">Add Reply</label>
                        <textarea name="reply_text" class="form-control" rows="3" placeholder="Add more details..." required></textarea>
                    </div>
                    <button type="submit" name="add_reply" class="btn btn-primary btn-sm">
                        <i class="bi bi-send"></i> Send Reply
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-secondary py-2 mb-0">
                    <i class="bi bi-lock-fill"></i> This ticket is <?php echo $sl[$modal_ticket['status']] ?>.
                </div>
            <?php endif; ?>
        </div>
        <div class="ticket-popup-footer">
            <a href="support_tickets.php" class="btn btn-secondary">Close</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
<?php require_once __DIR__ . '/inc/js-block.php'; ?>
