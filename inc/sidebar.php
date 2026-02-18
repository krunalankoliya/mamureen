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

    // Function to generate admin section if user is an admin
    function generateAdminSection($user_its, $admin_its, $current_page)
    {
    if (in_array($user_its, $admin_its)) {
        $adminPages = [
            // ['page' => 'city_name_correction', 'link' => 'admin/city_name_correction.php', 'text' => 'City Name Correction', 'icon' => 'bi bi-circle'],
            // ['page' => 'course_name_correction', 'link' => 'admin/course_name_correction.php', 'text' => 'Course Name Correction', 'icon' => 'bi bi-circle'],
            // ['page' => 'institute_name_name_correction', 'link' => 'admin/institute_name_name_correction.php', 'text' => 'Institute Name Correction', 'icon' => 'bi bi-circle'],
            // ['page' => 'admin_dashboard', 'link' => 'admin_dashboard.php', 'text' => 'Admin Dashboard', 'icon' => 'bi bi-circle'],
            ['page' => 'manage_admin', 'link' => 'admin/manage_admin.php', 'text' => 'Manage Admin', 'icon' => 'bi bi-circle'],
            ['page' => 'manage_mamureen', 'link' => 'admin/manage_mamureen.php', 'text' => 'Manage Mamureen', 'icon' => 'bi bi-circle'],
            // ['page' => 'meetings_data', 'link' => 'admin/meetings_data.php', 'text' => 'Meetings Data', 'icon' => 'bi bi-circle'],
            // ['page' => 'manage_identified_students', 'link' => 'admin/manage_identified_students.php', 'text' => 'Manage Identified Students', 'icon' => 'bi bi-circle'],
            ['page' => 'Manage_Resources', 'link' => 'admin/manage-download.php', 'text' => 'Manage Resources', 'icon' => 'bi bi-circle'],
            // ['page' => 'Manage_link', 'link' => 'admin/manage-link.php', 'text' => 'Manage Links', 'icon' => 'bi bi-circle'],
            // ['page' => 'list_download_reports', 'link' => 'admin/list_download_reports.php', 'text' => 'Downloads Report', 'icon' => 'bi bi-circle'],
            // ['page' => 'manage_google_forms', 'link' => 'admin/manage_google_forms.php', 'text' => 'Manage Feddback Forms', 'icon' => 'bi bi-circle'],
            // ['page' => 'manage_hifz_nasaih', 'link' => 'admin/manage_hifz_nasaih.php', 'text' => 'Hifz al-Nasa’ih Data', 'icon' => 'bi bi-circle'],
            // ['page' => 'manage_listan-al-dawat', 'link' => 'admin/manage_ld.php', 'text' => 'Data Entry - LD', 'icon' => 'bi bi-circle'],
            // ['page' => 'manage_bqi_17_session_attendees', 'link' => 'admin/manage_bqi_17_session_attendees.php', 'text' => 'BQI 17 Session Attendees', 'icon' => 'bi bi-circle'],
            // ['page' => 'manage-ticket', 'link' => 'admin/manage-ticket.php', 'text' => 'Manage Tickets', 'icon' => 'bi bi-circle'],
            // ['page' => 'manage_house_visits', 'link' => 'admin/manage_house_visits.php', 'text' => 'Manage Migrated In Student House Visit', 'icon' => 'bi bi-circle'],
            // ['page' => 'manage_migrated_out_student_house_visits', 'link' => 'admin/manage_migrated_out_student_house_visits.php', 'text' => 'Manage Migrated Out Student House Visit', 'icon' => 'bi bi-circle'],
            // ['page' => 'manage_minhat_talimiyah', 'link' => 'admin/manage_minhat_talimiyah.php', 'text' => 'Manage Minhat Talimiyah', 'icon' => 'bi bi-circle'],
            // ['page' => 'manage_cities', 'link' => 'admin/manage_cities.php', 'text' => 'Manage Cities', 'icon' => 'bi bi-circle'],
            // ['page' => 'manage_education_raza_coordinators', 'link' => 'admin/manage_education_raza_coordinators.php', 'text' => 'Manage Education Raza Coordinators', 'icon' => 'bi bi-circle'],
            // ['page' => 'manage_mamureen_suggestions', 'link' => 'admin/manage_mamureen_suggestions.php', 'text' => 'Manage Mamureen Suggestions', 'icon' => 'bi bi-circle'],
            // ['page' => 'manage_journal', 'link' => 'admin/manage_journal.php', 'text' => 'Manage Journal', 'icon' => 'bi bi-circle'],
            ['page' => 'manage_bqi_categories', 'link' => 'admin/manage_bqi_categories.php', 'text' => 'BQI 1447 Categories', 'icon' => 'bi bi-circle'],
            ['page' => 'manage_tafheem_reasons', 'link' => 'admin/manage_tafheem_reasons.php', 'text' => 'Tafheem Reasons', 'icon' => 'bi bi-circle'],
            // ['page' => 'list_closure_reports', 'link' => 'admin/list_closure_reports.php', 'text' => 'Closure Reports Master', 'icon' => 'bi bi-circle'],
            // ['page' => 'zone_master', 'link' => 'admin/zone_master.php', 'text' => 'Zone Master Counts', 'icon' => 'bi bi-circle'],
        ];
        generateCollapsibleSidebarItem(
            [
                'manage_admin',
                'manage_mamureen',
                // 'meetings_data',
                'Manage_Resources',
                // 'Manage_link',
                // 'list_download_reports',
                // 'manage_google_forms',
                // 'manage_hifz_nasaih',
                // 'manage_listan-al-dawat',
                // 'manage_bqi_17_session_attendees',
                // 'manage_journal',
                'manage_bqi_categories',
                'manage_tafheem_reasons',
                // 'list_closure_reports',
            ],
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
        <!-- Dashboard -->
        <?php generateSidebarItem('dashboard', $current_page, 'bi bi-grid', 'Dashboard', 'index.php'); ?>

        <!-- BQI 1447 Pages -->
        <?php generateSidebarItem('zakereen_parties', $current_page, 'bi bi-clipboard-data', 'Zakereen Parties', 'zakereen_parties.php'); ?>
        <?php generateSidebarItem('zakereen_farzando', $current_page, 'bi bi-clipboard-data', 'Zakereen Farzando', 'zakereen_farzando.php'); ?>
        <?php generateSidebarItem('individual_tafheem', $current_page, 'bi bi-clipboard-data', 'Individual Tafheem', 'individual_tafheem.php'); ?>
        <?php generateSidebarItem('training_sessions', $current_page, 'bi bi-clipboard-data', 'Training Sessions', 'training_sessions.php'); ?>
        <?php generateSidebarItem('challenges_solutions', $current_page, 'bi bi-clipboard-data', 'Challenges / Solutions', 'challenges_solutions.php'); ?>
        <?php generateSidebarItem('noteworthy_experiences', $current_page, 'bi bi-clipboard-data', 'Noteworthy Experiences', 'noteworthy_experiences.php'); ?>
        <?php generateSidebarItem('khidmat_preparations', $current_page, 'bi bi-clipboard-data', 'Khidmat Preparations', 'khidmat_preparations.php'); ?>
        <?php generateSidebarItem('maaraz', $current_page, 'bi bi-clipboard-data', "Ma'araz", 'maaraz.php'); ?>

        <!-- Admin Section -->
        <?php generateAdminSection($user_its, $admin_its, $current_page); ?>
    </ul>
</aside><!-- End Sidebar-->