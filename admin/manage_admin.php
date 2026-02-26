<?php
$current_page = 'manage_admin';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../inc/header.php';

$message = null;

/**
 * Logic: Add Admin via ITS
 */
if (isset($_POST['submit'])) {
    $its_id = (int)$_POST['its_id'];
    $data = ApiService::getUserDetails($its_id);

    if ($data) {
        try {
            $nationality = $data['nationality'] ?? '';
            $miqaat_india_foreign = ($nationality === 'Indian') ? 'I' : 'F';
            $dob = $data['dob'] ?? '';
            $age = $dob ? (new DateTime())->diff(new DateTime($dob))->y : 0;

            $db->insert('users_admin', [
                'its_id' => $its_id,
                'fullname' => $data['full_name_en'],
                'fullname_ar' => $data['full_name_ar'],
                'email_id' => $data['email'],
                'mobile' => $data['mobile'],
                'miqaat_mauze' => $data['jamaat'],
                'kg_age' => $age,
                'miqaat_india_foreign' => $miqaat_india_foreign
            ]);
            $message = ['text' => "Admin access granted to {$data['full_name_en']}.", 'tag' => 'success'];
        } catch (Exception $e) {
            $message = ['text' => "Admin already exists or Database error.", 'tag' => 'danger'];
        }
    } else {
        $message = ['text' => "ITS ID $its_id not found.", 'tag' => 'warning'];
    }
}

/**
 * Logic: Revoke Access
 */
if (isset($_POST['delete'])) {
    $db->query("DELETE FROM users_admin WHERE its_id = ?", [(int)$_POST['its_id']]);
    $message = ['text' => "Admin access revoked.", 'tag' => 'info'];
}

$admins = $db->fetchAll("SELECT * FROM users_admin ORDER BY fullname ASC");
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">System Administrators</h1>
            <p class="text-muted small mb-0">Control global access and system-level permissions.</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message['tag']; ?> border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-info-circle-fill me-2"></i> <?= $message['text']; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Register Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-3">Grant Access</h5>
                <form method="post">
                    <div class="mb-4">
                        <label class="form-label small fw-bold">ITS ID</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-shield-lock"></i></span>
                            <input type="number" name="its_id" class="form-control" placeholder="8 Digit ID" required>
                        </div>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm">
                        Assign Admin Role
                    </button>
                </form>
            </div>
        </div>

        <!-- List Card -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0">Active Administrators</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Identity</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Contact</th>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $a): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= user_photo_url($a['its_id']) ?>" class="rounded-circle border" width="40" height="40">
                                            <div>
                                                <div class="fw-bold text-dark small"><?= h($a['fullname']) ?></div>
                                                <div class="text-muted" style="font-size: 0.7rem;">ITS: <?= $a['its_id'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="small text-dark"><?= h($a['email_id']) ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem;"><?= h($a['mobile']) ?></div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <form method="post" onsubmit="return confirm('Revoke admin access?');">
                                            <input type="hidden" name="its_id" value="<?= $a['its_id'] ?>">
                                            <button type="submit" name="delete" class="btn btn-light btn-sm rounded-pill text-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
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

<?php 
require_once __DIR__ . '/../inc/footer.php'; 
require_once __DIR__ . '/../inc/js-block.php'; 
?>
