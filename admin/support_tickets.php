<?php
    $current_page = 'support_tickets_admin';
    require_once __DIR__ . '/../session.php';

    // Only admin or sub-admin
    if (! $is_admin && ! $is_sub_admin) {
    header('Location: ' . MODULE_PATH . 'index.php');
    exit();
    }

    require_once __DIR__ . '/../inc/header.php';
    require_once __DIR__ . '/../inc/smtp_mailer.php';

    // ── Handle: Admin Reply ───────────────────────────────────────
    if (isset($_POST['admin_reply'])) {
    $ticket_id  = (int) $_POST['ticket_id'];
    $reply_text = trim(mysqli_real_escape_string($mysqli, $_POST['reply_text']));
    $new_status = in_array($_POST['new_status'], ['open', 'in_progress', 'resolved', 'closed'])
        ? $_POST['new_status'] : 'in_progress';
    // Fetch replier name directly from the correct table (avoids empty $fullname for sub admins)
    $name_row = mysqli_query($mysqli,
        "SELECT fullname FROM users_admin WHERE its_id = '$user_its'
         UNION SELECT fullname FROM users_sub_admin WHERE its_id = '$user_its'
         LIMIT 1"
    )->fetch_assoc();
    $safe_name = mysqli_real_escape_string($mysqli, $name_row['fullname'] ?? $fullname ?? '');

    if ($ticket_id > 0 && ! empty($reply_text)) {
        // Insert reply
        mysqli_query($mysqli, "INSERT INTO `support_ticket_replies`
            (`ticket_id`, `reply_text`, `replied_by_its`, `replied_by_name`, `is_admin_reply`)
            VALUES ('$ticket_id', '$reply_text', '$user_its', '$safe_name', 1)");

        // Update ticket status
        mysqli_query($mysqli, "UPDATE `support_tickets` SET `status` = '$new_status', `updated_at` = NOW()
                                WHERE `id` = '$ticket_id'");

        // Fetch ticket for email
        $tkt = mysqli_query($mysqli, "SELECT * FROM `support_tickets` WHERE `id` = '$ticket_id'")->fetch_assoc();

        $mail_note  = '';
        $mail_error = '';

        if ($tkt && ! empty($tkt['email'])) {
            $status_labels_email = ['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'];
            $status_label        = $status_labels_email[$new_status] ?? ucfirst($new_status);

            $email_html = '
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px;">
  <div style="max-width: 600px; margin: auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <div style="background: #4154f1; padding: 20px 24px;">
      <h2 style="color: #fff; margin: 0;">Mamureen Support</h2>
    </div>
    <div style="padding: 24px;">
      <p>Dear <strong>' . htmlspecialchars($tkt['added_by_name'] ?? 'User') . '</strong>,</p>
      <p>Your support ticket has received a reply from our team.</p>
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
        <tr><td style="padding: 6px 10px; background: #f8f9fa; font-weight: 600; width: 130px;">Ticket No.</td>
            <td style="padding: 6px 10px;">' . htmlspecialchars($tkt['ticket_number']) . '</td></tr>
        <tr><td style="padding: 6px 10px; background: #f8f9fa; font-weight: 600;">Subject</td>
            <td style="padding: 6px 10px;">' . htmlspecialchars($tkt['subject']) . '</td></tr>
        <tr><td style="padding: 6px 10px; background: #f8f9fa; font-weight: 600;">Status</td>
            <td style="padding: 6px 10px;"><strong>' . $status_label . '</strong></td></tr>
      </table>
      <div style="background: #e8f0fe; border-left: 4px solid #4154f1; padding: 14px 16px; border-radius: 4px; margin-bottom: 16px;">
        <p style="margin: 0 0 4px 0; font-weight: 600;">Reply from Support Team:</p>
        <p style="margin: 0;">' . nl2br(htmlspecialchars($_POST['reply_text'])) . '</p>
      </div>
      <p style="color: #888; font-size: 13px;">You can log in to view the full conversation and add your reply.</p>
    </div>
    <div style="background: #f4f6f9; padding: 14px 24px; text-align: center; font-size: 12px; color: #888;">
      Barnamaj al-Quran wal Ilm &mdash; Mamureen Dashboard
    </div>
  </div>
</body>
</html>';

            $mail_sent = sendSupportMail(
                $tkt['email'],
                $tkt['added_by_name'] ?? '',
                "[{$tkt['ticket_number']}] Reply — {$tkt['subject']}",
                $email_html,
                $mail_error
            );

            $mail_note = $mail_sent
                ? ' Email sent to <strong>' . htmlspecialchars($tkt['email']) . '</strong>.'
                : ' <span class="text-danger">Email failed (' . htmlspecialchars($mail_error) . ').</span>';
        } else {
            $mail_note = ' <span class="text-warning">No email on record — notification not sent.</span>';
        }

        $message = ['text' => 'Reply saved and status updated.' . $mail_note, 'tag' => 'success'];
    }
    }

    // ── Status filter ─────────────────────────────────────────────
    $filter = isset($_GET['status']) && in_array($_GET['status'], ['open', 'in_progress', 'resolved', 'closed'])
    ? $_GET['status'] : '';
    $where_status = $filter ? "WHERE t.status = '$filter'" : '';

    // ── Fetch tickets ─────────────────────────────────────────────
    $tickets = mysqli_query($mysqli,
    "SELECT t.*, um.fullname AS user_fullname
     FROM `support_tickets` t
     LEFT JOIN `users_mamureen` um ON um.its_id = t.added_its
     $where_status
     ORDER BY
       FIELD(t.status, 'open', 'in_progress', 'resolved', 'closed'),
       t.created_at DESC"
    )->fetch_all(MYSQLI_ASSOC);

    // Counts per status for tabs
    $counts = [];
    foreach (['open', 'in_progress', 'resolved', 'closed'] as $s) {
    $r          = mysqli_query($mysqli, "SELECT COUNT(*) c FROM `support_tickets` WHERE `status` = '$s'")->fetch_assoc();
    $counts[$s] = (int) $r['c'];
    }
    $counts['all'] = array_sum($counts);

    // ── Fetch ticket thread for modal ─────────────────────────────
    $modal_ticket  = null;
    $modal_replies = [];
    if (isset($_GET['view']) && (int) $_GET['view'] > 0) {
    $view_id = (int) $_GET['view'];
    $res     = mysqli_query($mysqli, "SELECT * FROM `support_tickets` WHERE `id` = '$view_id'");
    if ($res && $res->num_rows) {
        $modal_ticket  = $res->fetch_assoc();
        $modal_replies = mysqli_query($mysqli,
            "SELECT * FROM `support_ticket_replies` WHERE `ticket_id` = '$view_id' ORDER BY `created_at` ASC"
        )->fetch_all(MYSQLI_ASSOC);
    }
    }

    $status_labels   = ['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'];
    $status_colors   = ['open' => 'primary', 'in_progress' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'];
    $priority_colors = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger'];
?>

<style>
.ticket-thread { max-height: 380px; overflow-y: auto; }
.bubble        { border-radius: 12px; padding: 10px 14px; margin-bottom: 10px; }
.bubble-user   { background: #e8f0fe; max-width: 85%; }
.bubble-admin  { background: #e6f4ea; max-width: 85%; margin-left: auto; text-align: right; }
.bubble small  { display: block; margin-top: 4px; color: #666; font-size: 11px; }
.filter-tab    { cursor: pointer; }
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
        <h1>Manage Queries</h1>
    </div>

    <section class="section">
        <div class="row">
            <?php require_once __DIR__ . '/../inc/messages.php'; ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-ticket-detailed-fill text-primary"></i> All Queries</h5>

                        <!-- Status Filter Tabs -->
                        <div class="mb-3">
                            <a href="?<?php echo $filter ? '' : 'status=' ?>" class="btn btn-sm <?php echo ! $filter ? 'btn-primary' : 'btn-outline-primary' ?> me-1">
                                All <span class="badge bg-light text-dark ms-1"><?php echo $counts['all'] ?></span>
                            </a>
                            <a href="?status=open" class="btn btn-sm <?php echo $filter === 'open' ? 'btn-primary' : 'btn-outline-primary' ?> me-1">
                                Open <span class="badge bg-light text-dark ms-1"><?php echo $counts['open'] ?></span>
                            </a>
                            <a href="?status=in_progress" class="btn btn-sm <?php echo $filter === 'in_progress' ? 'btn-warning' : 'btn-outline-warning' ?> me-1">
                                In Progress <span class="badge bg-light text-dark ms-1"><?php echo $counts['in_progress'] ?></span>
                            </a>
                            <a href="?status=resolved" class="btn btn-sm <?php echo $filter === 'resolved' ? 'btn-success' : 'btn-outline-success' ?> me-1">
                                Resolved <span class="badge bg-light text-dark ms-1"><?php echo $counts['resolved'] ?></span>
                            </a>
                            <a href="?status=closed" class="btn btn-sm <?php echo $filter === 'closed' ? 'btn-secondary' : 'btn-outline-secondary' ?> me-1">
                                Closed <span class="badge bg-light text-dark ms-1"><?php echo $counts['closed'] ?></span>
                            </a>
                        </div>

                        <div style="overflow-x: auto;">
                            <table class="table table-striped" id="datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Ticket No.</th>
                                        <th>Subject</th>
                                        <th>From</th>
                                        <th>Mauze</th>
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
                                            <td><?php echo htmlspecialchars($t['user_fullname'] ?? $t['added_by_name'] ?? $t['added_its']) ?></td>
                                            <td><?php echo htmlspecialchars($t['miqaat_mauze'] ?? '') ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $priority_colors[$t['priority']] ?? 'secondary' ?>">
                                                    <?php echo ucfirst($t['priority']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $status_colors[$t['status']] ?? 'secondary' ?>">
                                                    <?php echo $status_labels[$t['status']] ?? ucfirst($t['status']) ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d-M-Y', strtotime($t['created_at'])) ?></td>
                                            <td>
                                                <a href="?<?php echo $filter ? 'status=' . $filter . '&' : '' ?>view=<?php echo $t['id'] ?>"
                                                   class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-reply-fill"></i> Reply
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

<!-- ── View & Reply Popup ─────────────────────────────────── -->
<?php if ($modal_ticket): ?>
<div class="ticket-popup-overlay">
    <div class="ticket-popup-box">
        <div class="ticket-popup-header">
            <div>
                <h5 class="mb-1">
                    <code><?php echo htmlspecialchars($modal_ticket['ticket_number']) ?></code>
                    &mdash; <?php echo htmlspecialchars($modal_ticket['subject']) ?>
                </h5>
                <span class="badge bg-<?php echo $status_colors[$modal_ticket['status']] ?? 'secondary' ?>">
                    <?php echo $status_labels[$modal_ticket['status']] ?? ucfirst($modal_ticket['status']) ?>
                </span>
                <span class="badge bg-<?php echo $priority_colors[$modal_ticket['priority']] ?? 'secondary' ?>">
                    Priority: <?php echo ucfirst($modal_ticket['priority']) ?>
                </span>
            </div>
            <a href="?<?php echo $filter ? 'status=' . $filter : '' ?>" class="btn-close"></a>
        </div>
        <div class="ticket-popup-body">
            <div class="row mb-2 text-muted small">
                <div class="col-sm-6">
                    <strong>From:</strong> <?php echo htmlspecialchars($modal_ticket['added_by_name'] ?? '') ?>
                    (ITS: <?php echo $modal_ticket['added_its'] ?>)<br>
                    <strong>Email:</strong> <?php echo htmlspecialchars($modal_ticket['email'] ?? '—') ?><br>
                    <strong>Mauze:</strong> <?php echo htmlspecialchars($modal_ticket['miqaat_mauze'] ?? '—') ?>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <strong>Submitted:</strong> <?php echo date('d-M-Y H:i', strtotime($modal_ticket['created_at'])) ?><br>
                    <?php if ($modal_ticket['updated_at']): ?>
                        <strong>Last Updated:</strong> <?php echo date('d-M-Y H:i', strtotime($modal_ticket['updated_at'])) ?>
                    <?php endif; ?>
                </div>
            </div>
            <hr>

            <!-- Conversation Thread -->
            <div class="ticket-thread mb-3">
                <div class="bubble bubble-user">
                    <strong><?php echo htmlspecialchars($modal_ticket['added_by_name'] ?? 'User') ?></strong><br>
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
                            <strong><?php echo htmlspecialchars($r['replied_by_name'] ?? 'User') ?></strong><br>
                            <?php echo nl2br(htmlspecialchars($r['reply_text'])) ?>
                            <small><?php echo date('d-M-Y H:i', strtotime($r['created_at'])) ?></small>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <hr>
            <!-- Admin Reply Form -->
            <form method="post">
                <input type="hidden" name="ticket_id" value="<?php echo $modal_ticket['id'] ?>">
                <div class="row mb-2">
                    <div class="col-sm-8">
                        <label class="form-label fw-bold">Reply to User</label>
                        <textarea name="reply_text" class="form-control" rows="4" placeholder="Type your reply..." required></textarea>
                        <small class="text-muted">An email notification will be sent to: <strong><?php echo htmlspecialchars($modal_ticket['email'] ?? '—') ?></strong></small>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label fw-bold">Update Status</label>
                        <select name="new_status" class="form-select">
                            <?php foreach ($status_labels as $val => $lbl): ?>
                                <option value="<?php echo $val ?>" <?php echo $modal_ticket['status'] === $val ? 'selected' : '' ?>><?php echo $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" name="admin_reply" class="btn btn-primary">
                    <i class="bi bi-send-fill"></i> Send Reply &amp; Update Status
                </button>
            </form>
        </div>
        <div class="ticket-popup-footer">
            <a href="?<?php echo $filter ? 'status=' . $filter : '' ?>" class="btn btn-secondary">Close</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
<?php require_once __DIR__ . '/../inc/js-block.php'; ?>
