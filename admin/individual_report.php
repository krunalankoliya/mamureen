<?php
require_once(__DIR__ . '/../session.php');
require_once(__DIR__ . '/includes/report_code_snippets.php');

$date = date('Y-m-d');

$startDate = $endDate = date('Y-m-d');

$startDate = $_GET['startDate'];
$endDate = $_GET['endDate'];
$jamiat = trim($_GET['jamiat']);

$jamiatQuery = $jamiat ? " WHERE `m`.`jamiat` = '$jamiat'" : '';
$queryStartData = "$startDate 00:00:00";
$queryEndData = "$endDate 23:59:59";

// $query = "SELECT
// 	`m`.`jamaat`, `m`.`jamiat`,
// 	(SELECT IFNULL((SUM(`is_contacted`)),0) FROM `deeni_taalim` `da` WHERE `da`.`jamaat` = `m`.`jamaat` AND (`update_ts` BETWEEN '$queryStartData' AND '$queryEndData')) `deeni_achived`,
// 	(SELECT COUNT(*) AS `deeni_target` FROM `deeni_taalim` `dt` WHERE `dt`.`jamaat` = `m`.`jamaat`) `deeni_target`,
// 	(SELECT IFNULL((SUM(`is_contacted`)),0) FROM `counselling_students` `ca` WHERE `ca`.`jamaat` = `m`.`jamaat` AND (`update_ts` BETWEEN '$queryStartData' AND '$queryEndData')) `counselling_achived`,
// 	(SELECT COUNT(*) AS `counselling_target` FROM `counselling_students` `ct` WHERE `ct`.`jamaat` = `m`.`jamaat`) `counselling_target`,
// 	(SELECT IFNULL((SUM(`is_contacted`)),0) FROM `surat_balad` `ba` WHERE `ba`.`jamaat` = `m`.`jamaat` AND (`update_ts` BETWEEN '$queryStartData' AND '$queryEndData')) `balad_achived`,
// 	(SELECT COUNT(*) AS `counselling_target` FROM `surat_balad` `bt` WHERE `bt`.`jamaat` = `m`.`jamaat`) `balad_target`,
// 	(SELECT IFNULL((SUM(`is_contacted`)),0) FROM `taalim_quran` `qa` WHERE `qa`.`jamaat` = `m`.`jamaat` AND (`update_ts` BETWEEN '$queryStartData' AND '$queryEndData')) `quran_achived`,
// 	(SELECT COUNT(*) AS `counselling_target` FROM `taalim_quran` `qt` WHERE `qt`.`jamaat` = `m`.`jamaat`) `quran_target`
//     FROM `mauze_master` `m` $jamiatQuery";
$query = "SELECT * FROM (SELECT
	`m`.`jamaat`, `m`.`jamiat`,
	(SELECT COUNT(`is_contacted`) FROM `deeni_taalim` `dy` WHERE `is_contacted` = 1 AND `dy`.`jamaat` = `m`.`jamaat` AND (`update_ts` BETWEEN '$queryStartData' AND '$queryEndData')) `deeni_yes`,
    (SELECT COUNT(`is_contacted`) FROM `deeni_taalim` `dn` WHERE `is_contacted` = 0 AND `dn`.`jamaat` = `m`.`jamaat` AND (`update_ts` BETWEEN '$queryStartData' AND '$queryEndData')) `deeni_no`,
	(SELECT COUNT(*) FROM `deeni_taalim` `dt` WHERE `dt`.`jamaat` = `m`.`jamaat`) `deeni_target`,
	(SELECT COUNT(`is_contacted`) FROM `counselling_students` `cy` WHERE `is_contacted` = 1 AND `cy`.`jamaat` = `m`.`jamaat` AND (`update_ts` BETWEEN '$queryStartData' AND '$queryEndData')) `counselling_yes`,
    (SELECT COUNT(`is_contacted`) FROM `counselling_students` `cn` WHERE `is_contacted` = 0 AND `cn`.`jamaat` = `m`.`jamaat` AND (`update_ts` BETWEEN '$queryStartData' AND '$queryEndData')) `counselling_no`,
	(SELECT COUNT(*) FROM `counselling_students` `ct` WHERE `ct`.`jamaat` = `m`.`jamaat`) `counselling_target`,
	(SELECT COUNT(`is_contacted`) FROM `surat_balad` `by` WHERE `is_contacted` = 1 AND `by`.`jamaat` = `m`.`jamaat` AND (`update_ts` BETWEEN '$queryStartData' AND '$queryEndData')) `balad_yes`,
    (SELECT COUNT(`is_contacted`) FROM `surat_balad` `bn` WHERE `is_contacted` = 0 AND `bn`.`jamaat` = `m`.`jamaat` AND (`update_ts` BETWEEN '$queryStartData' AND '$queryEndData')) `balad_no`,
	(SELECT COUNT(*) FROM `surat_balad` `bt` WHERE `bt`.`jamaat` = `m`.`jamaat`) `balad_target`,
	(SELECT COUNT(`is_contacted`) FROM `taalim_quran` `qy` WHERE `is_contacted` = 1 AND `qy`.`jamaat` = `m`.`jamaat` AND (`update_ts` BETWEEN '$queryStartData' AND '$queryEndData')) `quran_yes`,
    (SELECT COUNT(`is_contacted`) FROM `taalim_quran` `qn` WHERE `is_contacted` = 0 AND `qn`.`jamaat` = `m`.`jamaat` AND (`update_ts` BETWEEN '$queryStartData' AND '$queryEndData')) `quran_no`,
	(SELECT COUNT(*) FROM `taalim_quran` `qt` WHERE `qt`.`jamaat` = `m`.`jamaat`) `quran_target`,
	(SELECT COUNT(`is_contacted`) FROM `migrated_students` `my` WHERE `is_contacted` = 1 AND `my`.`institute_city` = `m`.`city` AND (`update_ts` BETWEEN '$queryStartData' AND '$queryEndData')) `migrated_yes`,
    (SELECT COUNT(`is_contacted`) FROM `migrated_students` `mn` WHERE `is_contacted` = 0 AND `mn`.`institute_city` = `m`.`city` AND (`update_ts` BETWEEN '$queryStartData' AND '$queryEndData')) `migrated_no`,
	(SELECT COUNT(*) FROM `migrated_students` `mt` WHERE `mt`.`institute_city` = `m`.`city`) `migrated_target`
FROM `mauze_master` `m` $jamiatQuery) `q1`  WHERE
deeni_yes > 0 OR deeni_no > 0 OR counselling_yes > 0 OR counselling_no > 0 OR balad_yes > 0 OR balad_no > 0 OR quran_yes > 0 OR quran_no > 0 OR migrated_yes > 0 OR migrated_no > 0";

$result = mysqli_query($mysqli, $query);
$rows = $result->fetch_all(MYSQLI_ASSOC);

require_once(__DIR__ . '/includes/report_html_header.php');
?>

<section class="section dashboard">
    <?php

    foreach ($rows as $data) {

        echo display_header($data['jamaat'], $data['jamiat']);

        echo display_date_range($startDate, $endDate);

    ?>

        <div class="row">
            <div class="col">
                <!--<h5 class="card-title text-center">Overall Progress</h5>-->
            </div>
        </div>
        <div class="row">
            <div class="col">
                <p>Barnamaj al-Quran wa al-Ilm' Mamureen names - <?= $data['jamaat'] ?></p>
            </div>
        </div>
        <div class="row" style="page-break-after: always;">
            <table>
                <thead>
                    <tr>
                        <th scope="col" class="hide-on-mobile">Sr No.</th>
                        <th scope="col">Photo</th>
                        <th scope="col">Name</th>
                        <th scope="col">Age</th>
                        <th scope="col">Mamur Idarah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users = "SELECT * FROM `users` WHERE `miqaat_mauze` = '$data[jamaat]' AND `is_musaid` = 0 ";
                    $result = mysqli_query($mysqli, $users);
                    $user_list = $result->fetch_all(MYSQLI_ASSOC);
                    $i = 1;
                    foreach ($user_list as $user) {
                    ?>
                        <tr>
                            <td scope="col"><?= $i++ ?></td>
                            <td scope="col"><img src="https://www.talabulilm.com/mumin_images/<?= $user['its_id'] ?>.png" alt="Profile" class="rounded-circle" style="width:60px;"></td>
                            <td scope="col"><?= $user['fullname'] ?></td>
                            <td scope="col"><?= $user['kg_age'] ?></td>
                            <td scope="col"><?= $user['avs_idara'] ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
            <p>&nbsp;</p>
            <table>
                <thead>
                    <tr>
                        <th scope="col">Photo</th>
                        <th scope="col">Amilsaheb Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT `u`.`full_name_en`,`j`.`primary_email`,`j`.`aamil_masool_its_id` FROM `its_jamaat_master` `j`
                            LEFT JOIN user `u` ON `j`.`aamil_masool_its_id` = `u`.`its_id`
                            WHERE `j`.`jamaat` = '$data[jamaat]'";
                    $result = mysqli_query($coremysqli, $query);
                    $row = $result->fetch_assoc();
                    ?>
                    <tr>
                        <td scope="col"><img src="https://www.talabulilm.com/mumin_images/<?= $row['aamil_masool_its_id'] ?>.png" alt="Profile" class="rounded-circle" style="width:60px;" /></td>
                        <td scope="col"><?= $row['full_name_en'] ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php
        echo display_header($data['jamaat'], $data['jamiat']);

        echo display_date_range($startDate, $endDate);

        ?>

        <div class="row mt-5" style="page-break-after: always;">
            <?php
            // if ($data['balad_target'] !== '0')
                echo display_info_card('Mumineen who completed hifz till Surat alBalad', $data['balad_yes'], $data['balad_target']);

            // if ($data['quran_target'] !== '0')
                echo display_info_card('Mumineen who learnt to recite Quran Majeed', $data['quran_yes'], $data['quran_target']);

            // if ($data['deeni_target'] !== '0')
                echo display_info_card('New enrollments in Madaris Imaniyah', $data['deeni_yes'], $data['deeni_target']);

            // if ($data['counselling_target'] !== '0')
                echo display_info_card('One-on-one career counselling of students', $data['counselling_yes'], $data['counselling_target']);
                echo display_info_card('Khabargiri of Migrated Students', $data['migrated_yes'], $data['migrated_target']);

            $query = "SELECT COUNT(*) `count` FROM `umoor_taalimiyah_members` WHERE jamaat = '$data[jamaat]' AND `expertise` != ''";
            $result = mysqli_query($mysqli, $query);
            $res = $result->fetch_assoc();

            if ($res['count'] !== '0')
                echo display_info_card('Subject Matter Experts identified', $res['count']);

            $query = "SELECT COUNT(`s`.`id`) `seminar_count`, SUM(`s`.`haazereen_count`) `seminar_attendees` FROM `seminar_reports` `s`
                WHERE `s`.`jamaat` = '$data[jamaat]' AND (`s`.`date_of_barnamaj` BETWEEN '$queryStartData' AND '$queryEndData') AND `s`.`barnamaj` IN ('Sabaq 1', 'Sabaq 2', 'Sabaq 3') GROUP BY `s`.`jamaat`";
            $result = mysqli_query($mysqli, $query);
            $asbaaq_conducted = $result->fetch_assoc();

            if ($asbaaq_conducted)
                echo display_info_card('Asbaaq conducted and participants count', $asbaaq_conducted['seminar_count'], $asbaaq_conducted['seminar_attendees'], true);

            $query = "SELECT COUNT(`s`.`id`) `seminar_count`, SUM(`s`.`haazereen_count`) `seminar_attendees` FROM `seminar_reports` `s`
                WHERE `s`.`jamaat` = '$data[jamaat]' AND (`s`.`date_of_barnamaj` BETWEEN '$queryStartData' AND '$queryEndData') AND `s`.`barnamaj` = 'Social Media Awareness' GROUP BY `s`.`barnamaj`";
            $result = mysqli_query($mysqli, $query);
            $social_media = $result->fetch_assoc();

            if ($social_media)
                    echo display_info_card('Social Media Awareness program', $social_media['seminar_count'], $social_media['seminar_attendees'], true);

            $query = "SELECT COUNT(`s`.`id`) `seminar_count`, SUM(`s`.`haazereen_count`) `seminar_attendees` FROM `seminar_reports` `s`
                WHERE `s`.`jamaat` = '$data[jamaat]' AND (`s`.`date_of_barnamaj` BETWEEN '$queryStartData' AND '$queryEndData') AND `s`.`barnamaj` = 'Group Counselling' GROUP BY `s`.`barnamaj`";
            $result = mysqli_query($mysqli, $query);
            $group_counselling = $result->fetch_assoc();

            if ($group_counselling)
                    echo display_info_card('Group Counselling session', $group_counselling['seminar_count'], $group_counselling['seminar_attendees'], true);

            $query = "SELECT COUNT(`s`.`id`) `seminar_count`, SUM(`s`.`haazereen_count`) `seminar_attendees` FROM `seminar_reports` `s`
                WHERE `s`.`jamaat` = '$data[jamaat]' AND (`s`.`date_of_barnamaj` BETWEEN '$queryStartData' AND '$queryEndData') AND `s`.`barnamaj` = 'Importance of Education' GROUP BY `s`.`barnamaj`";
            $result = mysqli_query($mysqli, $query);
            $education = $result->fetch_assoc();

            if ($education)
                    echo display_info_card('Importance of Education', $education['seminar_count'], $education['seminar_attendees'], true);

            $query = "SELECT COUNT(`s`.`id`) `seminar_count`, SUM(`s`.`haazereen_count`) `seminar_attendees` FROM `seminar_reports` `s`
                WHERE `s`.`jamaat` = '$data[jamaat]' AND (`s`.`date_of_barnamaj` BETWEEN '$queryStartData' AND '$queryEndData') AND `s`.`barnamaj` = 'Minhat Talimiyah' GROUP BY `s`.`barnamaj`";
            $result = mysqli_query($mysqli, $query);
            $minhat = $result->fetch_assoc();

            if ($minhat)
                    echo display_info_card('Minhat Taalimiyah', $minhat['seminar_count'], $minhat['seminar_attendees'], true);

            $query = "SELECT COUNT(`s`.`id`) `seminar_count`, SUM(`s`.`haazereen_count`) `seminar_attendees` FROM `seminar_reports` `s`
                WHERE `s`.`jamaat` = '$data[jamaat]' AND (`s`.`date_of_barnamaj` BETWEEN '$queryStartData' AND '$queryEndData') AND `s`.`barnamaj` = 'Muharramaat' GROUP BY `s`.`barnamaj`";
            $result = mysqli_query($mysqli, $query);
            $muharramaat = $result->fetch_assoc();

            if ($muharramaat)
                    echo display_info_card('Muharramaat', $muharramaat['seminar_count'], $muharramaat['seminar_attendees'], true);

            $query = "SELECT COUNT(`s`.`id`) `seminar_count`, SUM(`s`.`haazereen_count`) `seminar_attendees` FROM `seminar_reports` `s`
                WHERE `s`.`jamaat` = '$data[jamaat]' AND (`s`.`date_of_barnamaj` BETWEEN '$queryStartData' AND '$queryEndData') AND `s`.`barnamaj` = 'Meeting with Umoor Taleemiyah' GROUP BY `s`.`barnamaj`";
            $result = mysqli_query($mysqli, $query);
            $umoor = $result->fetch_assoc();
            if ($umoor)
                     echo display_info_card('Umoor Taalimiyah Meeting', $umoor['seminar_count'], $umoor['seminar_attendees'], true);
            ?>

            <!-- <div class="col-md-4-hdr">
                <div class="card info-card customers-card">
                    <div class="card-body">
                    <h5 class="card-title">Taharat Barnamaj</h5>
                    <div class="d-flex align-items-center">
                        <div class="ps-3">
                        <h6>0 / 0</h6>
                        </div>
                    </div>
                    </div>
                </div>
            </div> -->
            <?php
                $query = "SELECT COUNT(`s`.`id`) `seminar_count`, SUM(`s`.`haazereen_count`) `seminar_attendees` FROM `seminar_reports` `s`
                WHERE `s`.`jamaat` = '$data[jamaat]' AND (`s`.`date_of_barnamaj` BETWEEN '$queryStartData' AND '$queryEndData')
                AND `s`.`barnamaj` NOT IN ('Sabaq 1', 'Sabaq 2', 'Sabaq 3', 'Minhat Talimiyah', 'Importance of Education', 'Social Media Awareness', 'Group Counselling', 'Muharramaat', 'Meeting with Umoor Taleemiyah')";
                $result = mysqli_query($mysqli, $query);
                $other = $result->fetch_assoc();

                if ($other['seminar_attendees'])
                     echo display_info_card('Count of other Barnamaj/Seminars - total participants', $other['seminar_count'], $other['seminar_attendees'],true);
            ?>

        </div>
    <?php
    }
    ?>
</section>