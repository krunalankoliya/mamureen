<?php
require_once __DIR__ . '/../session.php';
$current_page = 'manage_tnc_members';
require_once __DIR__ . '/../inc/header.php';

// Admin-only
if (!$is_admin) {
    header('Location: ' . MODULE_PATH . 'index.php');
    exit();
}

// ── Handle Add ──────────────────────────────────────────────────────────────
if (isset($_POST['add_member'])) {
    $its_id   = (int) ($_POST['its_id'] ?? 0);
    $fullname = trim(mysqli_real_escape_string($mysqli, $_POST['fullname'] ?? ''));
    $mobile   = trim(mysqli_real_escape_string($mysqli, $_POST['mobile']   ?? ''));
    $email    = trim(mysqli_real_escape_string($mysqli, $_POST['email']    ?? ''));
    $jamiaat  = trim(mysqli_real_escape_string($mysqli, $_POST['jamiaat']  ?? ''));
    $jamaat   = trim(mysqli_real_escape_string($mysqli, $_POST['jamaat']   ?? ''));
    $post     = trim(mysqli_real_escape_string($mysqli, $_POST['post']     ?? ''));

    if (!$its_id || !$fullname || !$jamaat || !$post) {
        $message = ['tag' => 'danger', 'text' => 'ITS ID, Full Name, Jamaat and Post are required.'];
    } else {
        $dup = mysqli_query($mysqli, "SELECT id FROM `tnc_members_list` WHERE `its_id` = $its_id");
        if ($dup && $dup->num_rows > 0) {
            $message = ['tag' => 'danger', 'text' => "A member with ITS ID $its_id already exists."];
        } else {
            mysqli_query($mysqli,
                "INSERT INTO `tnc_members_list` (`its_id`,`fullname`,`mobile`,`email`,`jamiaat`,`jamaat`,`post`)
                 VALUES ('$its_id','$fullname','$mobile','$email','$jamiaat','$jamaat','$post')"
            );
            $message = ['tag' => 'success', 'text' => "Member '$fullname' added successfully."];
        }
    }
}

// ── Handle Edit ──────────────────────────────────────────────────────────────
if (isset($_POST['update_member'])) {
    $edit_id  = (int) $_POST['edit_id'];
    $its_id   = (int) ($_POST['its_id'] ?? 0);
    $fullname = trim(mysqli_real_escape_string($mysqli, $_POST['fullname'] ?? ''));
    $mobile   = trim(mysqli_real_escape_string($mysqli, $_POST['mobile']   ?? ''));
    $email    = trim(mysqli_real_escape_string($mysqli, $_POST['email']    ?? ''));
    $jamiaat  = trim(mysqli_real_escape_string($mysqli, $_POST['jamiaat']  ?? ''));
    $jamaat   = trim(mysqli_real_escape_string($mysqli, $_POST['jamaat']   ?? ''));
    $post     = trim(mysqli_real_escape_string($mysqli, $_POST['post']     ?? ''));

    if (!$its_id || !$fullname || !$jamaat || !$post) {
        $message = ['tag' => 'danger', 'text' => 'ITS ID, Full Name, Jamaat and Post are required.'];
    } else {
        mysqli_query($mysqli,
            "UPDATE `tnc_members_list`
             SET `its_id`='$its_id', `fullname`='$fullname', `mobile`='$mobile',
                 `email`='$email', `jamiaat`='$jamiaat', `jamaat`='$jamaat', `post`='$post'
             WHERE `id`='$edit_id'"
        );
        $message = ['tag' => 'success', 'text' => 'Member updated successfully.'];
    }
}

// ── Handle Delete ─────────────────────────────────────────────────────────────
if (isset($_POST['delete_member'])) {
    $delete_id = (int) $_POST['delete_id'];
    mysqli_query($mysqli, "DELETE FROM `tnc_members_list` WHERE `id` = $delete_id");
    $message = ['tag' => 'success', 'text' => 'Member deleted successfully.'];
}

// ── Fetch all members ─────────────────────────────────────────────────────────
$result  = mysqli_query($mysqli, "SELECT * FROM `tnc_members_list` ORDER BY `jamaat`, `fullname`");
$members = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<style>
.form-section {
    background: #f8f9fa;
    border-left: 4px solid #4154f1;
    border-radius: 0 0.5rem 0.5rem 0;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
}
.form-section .form-label { font-weight: 600; font-size: 0.85rem; }
.tbl-avatar {
    width: 34px; height: 34px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.72rem; color: #fff; background: #4154f1;
    flex-shrink: 0;
}
</style>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Manage TNC Members</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo MODULE_PATH ?>index.php">Home</a></li>
                <li class="breadcrumb-item active">Manage TNC Members</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <?php require_once __DIR__ . '/../inc/messages.php'; ?>

        <!-- ── Add Member Form ── -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-person-plus-fill text-primary me-2"></i>Add New Member</h5>

                <div class="form-section">
                    <form method="post" autocomplete="off">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">ITS ID <span class="text-danger">*</span></label>
                                <input type="number" name="its_id" class="form-control" placeholder="e.g. 30488588" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="fullname" class="form-control" placeholder="Full name" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Post / Role <span class="text-danger">*</span></label>
                                <input type="text" name="post" class="form-control" placeholder="e.g. Secretary" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Mobile</label>
                                <input type="text" name="mobile" class="form-control" placeholder="+91 XXXXX XXXXX">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="email@example.com">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jamaat (Mauze) <span class="text-danger">*</span></label>
                                <input type="text" name="jamaat" class="form-control" placeholder="e.g. MASAKIN SAIFIYAH (INDORE)" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jamiaat (City / Region)</label>
                                <input type="text" name="jamiaat" class="form-control" placeholder="e.g. Indore">
                            </div>
                            <div class="col-md-12 text-end">
                                <button type="submit" name="add_member" class="btn btn-primary px-4">
                                    <i class="bi bi-plus-circle me-1"></i>Add Member
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── Members Table ── -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    All TNC Members
                    <span class="badge bg-secondary ms-1"><?php echo count($members) ?></span>
                </h5>

                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>ITS ID</th>
                                <th>Full Name</th>
                                <th>Post</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>Jamaat</th>
                                <th>Jamiaat</th>
                                <th style="width:100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $i => $m): ?>
                            <tr>
                                <td><?php echo $i + 1 ?></td>
                                <td><?php echo htmlspecialchars($m['its_id']) ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="tbl-avatar"><?php
                                            $parts = explode(' ', trim($m['fullname']));
                                            echo mb_strtoupper(mb_substr($parts[0], 0, 1) . (isset($parts[1]) ? mb_substr($parts[1], 0, 1) : ''));
                                        ?></div>
                                        <?php echo htmlspecialchars($m['fullname']) ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($m['post']) ?></td>
                                <td>
                                    <?php if ($m['mobile']): ?>
                                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $m['mobile']) ?>" target="_blank" class="text-decoration-none text-success">
                                            <i class="bi bi-whatsapp me-1"></i><?php echo htmlspecialchars($m['mobile']) ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($m['email']): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($m['email']) ?>"><?php echo htmlspecialchars($m['email']) ?></a>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($m['jamaat']) ?></td>
                                <td><?php echo htmlspecialchars($m['jamiaat']) ?></td>
                                <td>
                                    <!-- Edit -->
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-btn"
                                        data-id="<?php echo $m['id'] ?>"
                                        data-its="<?php echo htmlspecialchars($m['its_id']) ?>"
                                        data-name="<?php echo htmlspecialchars($m['fullname'], ENT_QUOTES) ?>"
                                        data-post="<?php echo htmlspecialchars($m['post'], ENT_QUOTES) ?>"
                                        data-mobile="<?php echo htmlspecialchars($m['mobile'], ENT_QUOTES) ?>"
                                        data-email="<?php echo htmlspecialchars($m['email'], ENT_QUOTES) ?>"
                                        data-jamaat="<?php echo htmlspecialchars($m['jamaat'], ENT_QUOTES) ?>"
                                        data-jamiaat="<?php echo htmlspecialchars($m['jamiaat'], ENT_QUOTES) ?>"
                                        data-bs-toggle="modal" data-bs-target="#editModal"
                                        title="Edit">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <!-- Delete -->
                                    <form method="post" class="d-inline delete-form">
                                        <input type="hidden" name="delete_id" value="<?php echo $m['id'] ?>">
                                        <button type="submit" name="delete_member"
                                            class="btn btn-sm btn-outline-danger delete-btn" title="Delete">
                                            <i class="bi bi-trash-fill"></i>
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

    </section>
</main>

<!-- ── Edit Modal ── -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editModalLabel"><i class="bi bi-pencil-fill me-2"></i>Edit Member</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">ITS ID <span class="text-danger">*</span></label>
                            <input type="number" name="its_id" id="edit_its" class="form-control" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Post / Role <span class="text-danger">*</span></label>
                            <input type="text" name="post" id="edit_post" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Mobile</label>
                            <input type="text" name="mobile" id="edit_mobile" class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Jamaat (Mauze) <span class="text-danger">*</span></label>
                            <input type="text" name="jamaat" id="edit_jamaat" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jamiaat (City)</label>
                            <input type="text" name="jamiaat" id="edit_jamiaat" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_member" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
<script src="../assets/js/manage_tnc_members.js"></script>
<?php require_once __DIR__ . '/../inc/js-block.php'; ?>
