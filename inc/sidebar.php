<?php
require_once __DIR__ . '/../session.php';

/**
 * Clean & Modern Sidebar Refactor
 */

$navItems = [
    'General' => [
        ['page' => 'dashboard', 'icon' => 'bi bi-grid-fill', 'text' => 'Overview', 'link' => 'index.php'],
        ['page' => 'resources', 'icon' => 'bi bi-folder2-open', 'text' => 'Resources', 'link' => 'resources.php'],
    ],
    'Operational' => [
        ['page' => 'zakereen_parties', 'icon' => 'bi bi-shield-check', 'text' => 'Parties', 'link' => 'zakereen_parties.php'],
        ['page' => 'zakereen_farzando', 'icon' => 'bi bi-person-plus-fill', 'text' => 'Farzando', 'link' => 'zakereen_farzando.php'],
        ['page' => 'training_sessions', 'icon' => 'bi bi-journal-text', 'text' => 'Trainings', 'link' => 'training_sessions.php'],
    ],
    'Support' => [
        ['page' => 'important_contacts', 'icon' => 'bi bi-telephone-outbound', 'text' => 'Contacts', 'link' => 'important_contacts.php'],
        ['page' => 'support_tickets', 'icon' => 'bi bi-chat-left-dots-fill', 'text' => 'Help Desk', 'link' => 'support_tickets.php'],
    ]
];
?>

<aside id="sidebar" class="sidebar shadow-sm">
    <ul class="sidebar-nav" id="sidebar-nav">

        <?php if ($is_sub_admin): ?>
            <li class="nav-heading text-uppercase small fw-bold opacity-50 px-4 mb-2">Management</li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'bqi_dashboard') ? 'active' : 'collapsed' ?>" href="<?= MODULE_PATH ?>admin/bqi_dashboard.php">
                    <i class="bi bi-speedometer2"></i>
                    <span>Admin Hub</span>
                </a>
            </li>
        <?php else: ?>
            
            <?php foreach ($navItems as $heading => $items): ?>
                <li class="nav-heading text-uppercase small fw-bold opacity-50 px-4 mt-3 mb-2"><?= $heading ?></li>
                <?php foreach ($items as $item): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == $item['page']) ? 'active' : 'collapsed' ?>" href="<?= MODULE_PATH . $item['link'] ?>">
                            <i class="<?= $item['icon'] ?>"></i>
                            <span><?= $item['text'] ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <?php if ($is_admin): ?>
                <li class="nav-heading text-uppercase small fw-bold opacity-50 px-4 mt-3 mb-2">System</li>
                <li class="nav-item">
                    <a class="nav-link collapsed border-0 bg-transparent" data-bs-target="#admin-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-gear-fill"></i><span>Settings</span><i class="bi bi-chevron-down ms-auto small"></i>
                    </a>
                    <ul id="admin-nav" class="nav-content collapse list-unstyled ps-4 py-2" data-bs-parent="#sidebar-nav">
                        <li><a href="<?= MODULE_PATH ?>admin/manage_admin.php" class="small text-decoration-none d-block py-1 text-secondary hover-primary"><i class="bi bi-circle small me-2"></i>Admins</a></li>
                        <li><a href="<?= MODULE_PATH ?>admin/manage_mamureen.php" class="small text-decoration-none d-block py-1 text-secondary hover-primary"><i class="bi bi-circle small me-2"></i>Users</a></li>
                    </ul>
                </li>
            <?php endif; ?>

        <?php endif; ?>

    </ul>

    <!-- Quick Feedback Widget -->
    <div class="mt-auto px-4 pb-4">
        <div class="p-3 rounded-4 bg-light border">
            <h6 class="fw-bold small mb-1">Need help?</h6>
            <p class="text-muted" style="font-size: 0.75rem;">Contact support if you encounter issues.</p>
            <a href="support_tickets.php" class="btn btn-primary btn-sm w-100 rounded-pill">Open Ticket</a>
        </div>
    </div>
</aside>
