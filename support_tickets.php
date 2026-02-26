<?php
$current_page = 'support_tickets';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/inc/header.php';

// Fetch user email from appropriate table
$email_table = $is_admin ? 'users_admin' : ($is_sub_admin ? 'users_sub_admin' : 'users_mamureen');
$user_email = $db->fetch("SELECT `email_id` FROM `{$email_table}` WHERE `its_id` = ?", [$user_its])['email_id'] ?? '';

$message = null;

/**
 * Logic: Create Ticket
 */
if (isset($_POST['create_ticket'])) {
    $subject = trim($_POST['subject'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    
    if ($subject && $desc) {
        $id = $db->insert('support_tickets', [
            'ticket_number' => 'TMP',
            'subject' => $subject,
            'description' => $desc,
            'priority' => $_POST['priority'],
            'added_its' => $user_its,
            'added_by_name' => $fullname,
            'email' => $user_email,
            'miqaat_mauze' => $mauze
        ]);
        $ticket_no = 'TKT-' . str_pad($id, 6, '0', STR_PAD_LEFT);
        $db->query("UPDATE support_tickets SET ticket_number = ? WHERE id = ?", [$ticket_no, $id]);
        $message = ['text' => "Ticket <strong>$ticket_no</strong> created successfully.", 'tag' => 'success'];
    }
}

/**
 * Logic: Add Reply
 */
if (isset($_POST['add_reply'])) {
    $tid = (int)$_POST['ticket_id'];
    $reply = trim($_POST['reply_text']);
    
    // Ensure ownership
    $owned = $db->fetch("SELECT id FROM support_tickets WHERE id = ? AND added_its = ?", [$tid, $user_its]);
    if ($owned && $reply) {
        $db->insert('support_ticket_replies', [
            'ticket_id' => $tid,
            'reply_text' => $reply,
            'replied_by_its' => $user_its,
            'replied_by_name' => $fullname,
            'is_admin_reply' => 0
        ]);
        $db->query("UPDATE support_tickets SET updated_at = NOW() WHERE id = ?", [$tid]);
        $message = ['text' => 'Reply posted.', 'tag' => 'success'];
    }
}

// Fetch Tickets
$tickets = $db->fetchAll("SELECT * FROM support_tickets WHERE added_its = ? ORDER BY created_at DESC", [$user_its]);

// View Logic
$view_ticket = null;
$replies = [];
if (isset($_GET['view'])) {
    $view_ticket = $db->fetch("SELECT * FROM support_tickets WHERE id = ? AND added_its = ?", [(int)$_GET['view'], $user_its]);
    if ($view_ticket) {
        $replies = $db->fetchAll("SELECT * FROM support_ticket_replies WHERE ticket_id = ? ORDER BY created_at ASC", [$view_ticket['id']]);
    }
}
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Help & Support</h1>
            <p class="text-muted small mb-0">Submit queries and track status of your support tickets.</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message['tag']; ?> border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-info-circle-fill me-2"></i> <?= $message['text']; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- New Ticket -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-3">Open New Query</h5>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Department / Subject</label>
                        <select name="subject" class="form-select" required>
                            <option value="">Select Topic...</option>
                            <option value="Shaadi Tafheem">Shaadi Tafheem</option>
                            <option value="Social Media Awareness">Social Media Awareness</option>
                            <option value="Zakereen Farzando Training">Zakereen Farzando Training</option>
                            <option value="General">General Technical Issue</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Please provide details..." required></textarea>
                    </div>
                    <button type="submit" name="create_ticket" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">
                        <i class="bi bi-send me-2"></i> Submit Ticket
                    </button>
                </form>
            </div>
        </div>

        <!-- Ticket List -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0">My Support History</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Ticket ID</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Subject</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Status</th>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $t): 
                                $status_badge = [
                                    'open' => 'bg-primary',
                                    'in_progress' => 'bg-warning',
                                    'resolved' => 'bg-success',
                                    'closed' => 'bg-secondary'
                                ];
                            ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <code class="fw-bold"><?= $t['ticket_number'] ?></code>
                                        <div class="text-muted small" style="font-size: 0.65rem;"><?= date('d M, Y', strtotime($t['created_at'])) ?></div>
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-bold text-dark small"><?= h($t['subject']) ?></div>
                                        <div class="text-muted truncate small" style="max-width: 250px; font-size: 0.75rem;"><?= h($t['description']) ?></div>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge <?= $status_badge[$t['status']] ?? 'bg-light' ?> bg-opacity-10 <?= str_replace('bg-', 'text-', $status_badge[$t['status']]) ?> rounded-pill px-3">
                                            <?= ucfirst(str_replace('_', ' ', $t['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <a href="?view=<?= $t['id'] ?>" class="btn btn-light btn-sm rounded-pill px-3">
                                            <i class="bi bi-chat-text me-1"></i> View Thread
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
</main>

<!-- Ticket Thread Popover (Non-modal Overlay) -->
<?php if ($view_ticket): ?>
    <div class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center" style="z-index: 9999; backdrop-filter: blur(4px);">
        <div class="bg-white rounded-4 shadow-lg w-100 mx-3" style="max-width: 800px; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column;">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0"><?= $view_ticket['ticket_number'] ?> &bull; <?= h($view_ticket['subject']) ?></h5>
                    <span class="badge bg-light text-muted border rounded-pill small">Priority: <?= ucfirst($view_ticket['priority']) ?></span>
                </div>
                <a href="support_tickets.php" class="btn-close"></a>
            </div>
            <div class="p-4" style="overflow-y: auto; flex-grow: 1; background: #f8fafc;">
                <!-- Main Issue -->
                <div class="bg-white p-3 rounded-4 border shadow-sm mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold small text-primary"><?= h($view_ticket['added_by_name']) ?> (You)</span>
                        <span class="text-muted" style="font-size: 0.7rem;"><?= date('d M, Y H:i', strtotime($view_ticket['created_at'])) ?></span>
                    </div>
                    <div class="text-dark small"><?= nl2br(h($view_ticket['description'])) ?></div>
                </div>

                <!-- Replies -->
                <?php foreach ($replies as $r): ?>
                    <div class="mb-4 d-flex <?= $r['is_admin_reply'] ? 'justify-content-start' : 'justify-content-end' ?>">
                        <div class="p-3 rounded-4 shadow-sm border <?= $r['is_admin_reply'] ? 'bg-light' : 'bg-primary text-white' ?>" style="max-width: 80%;">
                            <div class="d-flex justify-content-between gap-4 mb-1">
                                <span class="fw-bold" style="font-size: 0.7rem;"><?= $r['is_admin_reply'] ? 'Office Response' : 'You' ?></span>
                                <span class="opacity-75" style="font-size: 0.65rem;"><?= date('d/m H:i', strtotime($r['created_at'])) ?></span>
                            </div>
                            <div class="small"><?= nl2br(h($r['reply_text'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!in_array($view_ticket['status'], ['resolved', 'closed'])): ?>
                <div class="p-4 bg-white border-top">
                    <form method="post" class="row g-2">
                        <input type="hidden" name="ticket_id" value="<?= $view_ticket['id'] ?>">
                        <div class="col-10">
                            <textarea name="reply_text" class="form-control border-0 bg-light" placeholder="Write your message here..." rows="2" required></textarea>
                        </div>
                        <div class="col-2 d-flex align-items-end">
                            <button type="submit" name="add_reply" class="btn btn-primary w-100 rounded-4 py-3">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="p-4 bg-light border-top text-center text-muted small">
                    This ticket is now <strong><?= ucfirst($view_ticket['status']) ?></strong> and cannot be replied to.
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php 
require_once __DIR__ . '/inc/footer.php'; 
require_once __DIR__ . '/inc/js-block.php'; 
?>
