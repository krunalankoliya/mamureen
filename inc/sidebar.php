<?php
    require_once __DIR__ . '/../session.php';

    // Function to generate a sidebar item
    function generateSidebarItem($page, $current_page, $icon, $text, $link)
    {
    $activeClass = $current_page === $page ? 'collapsed' : '';
    echo "
    <li class='nav-item'>
        <a class='nav-link $activeClass' href='" . MODULE_PATH . "$link'>
            <i class='$icon'></i>
            <span>$text</span>
        </a>
    </li>";
    }

    // Function to generate a collapsible sidebar item with sub-items
    function generateCollapsibleSidebarItem($pages, $current_page, $icon, $text, $subItems, $name)
    {
    $activeClass    = in_array($current_page, $pages) ? 'show' : '';
    $collapsedClass = in_array($current_page, $pages) ? 'collapsed' : '';
    echo "
    <li class='nav-item'>
        <a class='nav-link $collapsedClass' data-bs-target='#$name-nav' data-bs-toggle='collapse' href='#'>
            <i class='$icon'></i>
            <span>$text</span>
            <i class='bi bi-chevron-down ms-auto'></i>
        </a>
        <ul id='$name-nav' class='nav-content collapse $activeClass' data-bs-parent='#sidebar-nav'>";
    foreach ($subItems as $item) {
        $activeSubClass = $current_page === $item['page'] ? 'active' : '';
        echo "
            <li class='nav-item'>
                <a class='nav-link $activeSubClass' href='" . MODULE_PATH . "{$item['link']}'>
                    <i class='{$item['icon']}'></i>
                    <span>{$item['text']}</span>
                </a>
            </li>";
    }
    echo "
        </ul>
    </li>";
    }

    // Function to generate admin section (admin only)
    function generateAdminSection($user_its, $admin_its, $current_page)
    {
    if (in_array($user_its, $admin_its)) {
        $adminPages = [
            ['page' => 'bqi_dashboard',                'link' => 'admin/bqi_dashboard.php',                'text' => 'BQI Dashboard',            'icon' => 'bi bi-speedometer2'],
            ['page' => 'manage_admin',                 'link' => 'admin/manage_admin.php',                 'text' => 'Manage Admin',             'icon' => 'bi bi-circle'],
            ['page' => 'manage_sub_admin',             'link' => 'admin/manage_sub_admin.php',             'text' => 'Manage Sub Admin',         'icon' => 'bi bi-circle'],
            ['page' => 'manage_mamureen',              'link' => 'admin/manage_mamureen.php',              'text' => 'Manage Mamureen',          'icon' => 'bi bi-circle'],
            ['page' => 'Manage_Resources',             'link' => 'admin/manage-download.php',              'text' => 'Manage Resources',         'icon' => 'bi bi-circle'],
            ['page' => 'Manage_link',                  'link' => 'admin/manage-link.php',                  'text' => 'Manage Links',             'icon' => 'bi bi-circle'],
            ['page' => 'manage_google_forms',          'link' => 'admin/manage_google_forms.php',          'text' => 'Manage Feedback Forms',    'icon' => 'bi bi-circle'],
            ['page' => 'manage_bqi_categories',        'link' => 'admin/manage_bqi_categories.php',        'text' => 'BQI 1447 Categories',      'icon' => 'bi bi-circle'],
            ['page' => 'manage_tafheem_reasons',       'link' => 'admin/manage_tafheem_reasons.php',       'text' => 'Tafheem Reasons',          'icon' => 'bi bi-circle'],
            ['page' => 'manage_program_titles',        'link' => 'admin/manage_program_titles.php',        'text' => 'Program Titles',           'icon' => 'bi bi-circle'],
            // ── BQI Reports ──
            ['page' => 'report_zakereen_parties',      'link' => 'admin/report_zakereen_parties.php',      'text' => 'Report: Zakereen Parties',      'icon' => 'bi bi-bar-chart'],
            ['page' => 'report_zakereen_farzando',     'link' => 'admin/report_zakereen_farzando.php',     'text' => 'Report: Zakereen Farzando',     'icon' => 'bi bi-bar-chart'],
            ['page' => 'report_individual_tafheem',    'link' => 'admin/report_individual_tafheem.php',    'text' => 'Report: Individual Tafheem',    'icon' => 'bi bi-bar-chart'],
            ['page' => 'report_training_sessions',     'link' => 'admin/report_training_sessions.php',     'text' => 'Report: Training Sessions',     'icon' => 'bi bi-bar-chart'],
            ['page' => 'report_challenges_solutions',  'link' => 'admin/report_challenges_solutions.php',  'text' => 'Report: Challenges & Solutions','icon' => 'bi bi-bar-chart'],
            ['page' => 'report_noteworthy_experiences','link' => 'admin/report_noteworthy_experiences.php','text' => 'Report: Noteworthy Experiences', 'icon' => 'bi bi-bar-chart'],
            ['page' => 'report_khidmat_preparations',  'link' => 'admin/report_khidmat_preparations.php',  'text' => 'Report: Khidmat Preparations',  'icon' => 'bi bi-bar-chart'],
        ];
        generateCollapsibleSidebarItem(
            array_column($adminPages, 'page'),
            $current_page,
            'bi bi-person',
            'Admin',
            $adminPages,
            'admin'
        );
    }
    }

?>

<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">

        <?php if ($is_sub_admin): ?>
            <!-- Sub Admin: BQI Dashboard + all report pages as direct links -->
            <?php generateSidebarItem('bqi_dashboard',                $current_page, 'bi bi-speedometer2',  'BQI Dashboard',            'admin/bqi_dashboard.php'); ?>
            <?php generateSidebarItem('report_zakereen_parties',      $current_page, 'bi bi-bar-chart',     'Zakereen Parties',         'admin/report_zakereen_parties.php'); ?>
            <?php generateSidebarItem('report_zakereen_farzando',     $current_page, 'bi bi-bar-chart',     'Zakereen Farzando',        'admin/report_zakereen_farzando.php'); ?>
            <?php generateSidebarItem('report_individual_tafheem',    $current_page, 'bi bi-bar-chart',     'Individual Tafheem',       'admin/report_individual_tafheem.php'); ?>
            <?php generateSidebarItem('report_training_sessions',     $current_page, 'bi bi-bar-chart',     'Training Sessions',        'admin/report_training_sessions.php'); ?>
            <?php generateSidebarItem('report_challenges_solutions',  $current_page, 'bi bi-bar-chart',     'Challenges & Solutions',   'admin/report_challenges_solutions.php'); ?>
            <?php generateSidebarItem('report_noteworthy_experiences',$current_page, 'bi bi-bar-chart',     'Noteworthy Experiences',   'admin/report_noteworthy_experiences.php'); ?>
            <?php generateSidebarItem('report_khidmat_preparations',  $current_page, 'bi bi-bar-chart',     'Khidmat Preparations',     'admin/report_khidmat_preparations.php'); ?>

        <?php else: ?>
            <!-- Regular mamureen + Admin pages -->
            <?php generateSidebarItem('dashboard',            $current_page, 'bi bi-grid',           'Dashboard',             'index.php'); ?>
            <?php generateSidebarItem('resources',            $current_page, 'bi bi-download',        'Resources & Downloads', 'resources.php'); ?>
            <?php generateSidebarItem('zakereen_parties',     $current_page, 'bi bi-clipboard-data',  'Zakereen Parties',      'zakereen_parties.php'); ?>
            <?php generateSidebarItem('zakereen_farzando',    $current_page, 'bi bi-clipboard-data',  'Zakereen Farzando',     'zakereen_farzando.php'); ?>
            <?php generateSidebarItem('individual_tafheem',   $current_page, 'bi bi-clipboard-data',  'Individual Tafheem',    'individual_tafheem.php'); ?>
            <?php generateSidebarItem('training_sessions',    $current_page, 'bi bi-clipboard-data',  'Training Sessions',     'training_sessions.php'); ?>
            <?php generateSidebarItem('challenges_solutions', $current_page, 'bi bi-clipboard-data',  'Challenges / Solutions','challenges_solutions.php'); ?>
            <?php generateSidebarItem('noteworthy_experiences',$current_page,'bi bi-clipboard-data',  'Noteworthy Experiences','noteworthy_experiences.php'); ?>
            <?php generateSidebarItem('khidmat_preparations', $current_page, 'bi bi-clipboard-data',  'Khidmat Preparations',  'khidmat_preparations.php'); ?>
            <?php generateSidebarItem('maaraz',               $current_page, 'bi bi-clipboard-data',  "Ma'araz",               'maaraz.php'); ?>
            <?php generateSidebarItem('google_form_list',     $current_page, 'bi bi-clipboard-data',  'Daily Reports',         'google_form_list.php'); ?>
            <?php generateSidebarItem('important_contacts',   $current_page, 'bi bi-telephone-fill',  'Important Contacts',    'important_contacts.php'); ?>
            <!-- Admin Section (includes BQI Reports) -->
            <?php generateAdminSection($user_its, $admin_its, $current_page); ?>

        <?php endif; ?>

    </ul>
</aside><!-- End Sidebar-->
