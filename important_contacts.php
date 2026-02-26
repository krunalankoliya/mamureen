<?php
$current_page = 'important_contacts';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/inc/header.php';

$contacts = [
    'Social Media' => [
        ['name' => 'Shabbir Bs bin Ubai Bs', 'phone' => '+91 81287 55144', 'initials' => 'SH', 'color' => '#6366f1'],
        ['name' => 'M Murtaza Mullajiwala', 'phone' => '+91 90048 81852', 'initials' => 'MU', 'color' => '#6366f1'],
    ],
    'ITNC - Shaadi Mutalliq' => [
        ['name' => 'M Burhanuddin Kathawala', 'phone' => '+91 88668 07939', 'initials' => 'BU', 'color' => '#8b5cf6'],
        ['name' => 'M Adnan Bhanpurawala', 'phone' => '+91 77389 25152', 'initials' => 'AD', 'color' => '#8b5cf6'],
    ],
    'Idarat al-Zakereen' => [
        ['name' => 'Helpline', 'phone' => '+91 77478 61553', 'initials' => 'IZ', 'color' => '#ef4444'],
    ],
    'BQI Back Office' => [
        ['name' => 'Shk Mufadddal Shakir', 'phone' => '+91 96194 90152', 'initials' => 'MF', 'color' => '#10b981'],
        ['name' => 'Shk Aliasger Bhanpurawala', 'phone' => '+91 98927 26516', 'initials' => 'AB', 'color' => '#10b981'],
        ['name' => 'Shk Aliasger Hamid', 'phone' => '+91 81530 19172', 'initials' => 'AH', 'color' => '#10b981'],
        ['name' => 'M Mansoor Shakir', 'phone' => '+91 99306 56777', 'initials' => 'MA', 'color' => '#10b981'],
        ['name' => 'M Moiz Nasir', 'phone' => '+91 99796 60952', 'initials' => 'MO', 'color' => '#10b981'],
    ]
];
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Important Contacts</h1>
            <p class="text-muted small mb-0">Direct support and departmental contacts for BQI assistance.</p>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach ($contacts as $dept => $list): ?>
            <div class="<?= count($list) > 2 ? 'col-12' : 'col-lg-4' ?>">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center gap-2">
                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-person-lines-fill small"></i>
                        </div>
                        <h6 class="fw-bold mb-0"><?= $dept ?></h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <?php foreach ($list as $c): ?>
                                <div class="<?= count($list) > 2 ? 'col-md-4' : 'col-12' ?> border-top p-4 d-flex align-items-center gap-3 hover-bg-light transition-all">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm" 
                                         style="width: 48px; height: 48px; background: <?= $c['color'] ?>; flex-shrink: 0; font-size: 0.8rem;">
                                        <?= $c['initials'] ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark small"><?= h($c['name']) ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= $c['phone'] ?></div>
                                    </div>
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $c['phone']) ?>" target="_blank" class="btn btn-light btn-sm rounded-circle shadow-sm" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; color: #25d366;">
                                        <i class="bi bi-whatsapp h5 mb-0"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php 
require_once __DIR__ . '/inc/footer.php'; 
require_once __DIR__ . '/inc/js-block.php'; 
?>
