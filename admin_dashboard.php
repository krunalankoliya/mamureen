<?php

$current_page = 'admin_dashboard';
require_once(__DIR__ . '/inc/header.php');

$query = "SELECT `its_id` FROM `users_admin`";
$result = mysqli_query($mysqli, $query);
$admin_list = $result->fetch_all(MYSQLI_ASSOC);
$admin = array_column($admin_list, 'its_id');

function getCounts($query, $mysqli)
{
  $result = mysqli_query($mysqli, $query);
  $res = $result->fetch_assoc();

  return ($res && $res['count']) ? (int) $res['count'] : 0;
}


//House Visit Counts
// $total_house_visit_count = getCounts( "SELECT COUNT(*) `count` FROM `migrated_students` WHERE  `not_migrated` = '0' ", $mysqli);
$total_house_visit_count = getCounts( "SELECT COUNT(*) `count` FROM `migrated_students` WHERE  `not_migrated` = '0' AND `institute_city` IN ( SELECT `institute_city` FROM `map_city` WHERE `zone` != 'ignore')", $mysqli);
$house_visit_count = getCounts(
  "SELECT COUNT(*) `count` FROM (SELECT `hv_sub`.`student_its` FROM `hv_sub`
  LEFT JOIN `migrated_students` ON `hv_sub`.`student_its` = `migrated_students`.`its_id`
  WHERE  `not_migrated` = '0' GROUP BY `hv_sub`.`student_its`) q;",
  $mysqli
);

$house_visit_percent = ($total_house_visit_count > 0) ?  ($house_visit_count / $total_house_visit_count) * 100 : '-';
$house_visit_total_count = $total_house_visit_count;

//migrated out House Visit Counts
$total_migrated_out_house_visit_count = getCounts( "SELECT COUNT(*) `count` FROM `migrated_students` WHERE  `not_migrated` = '0' AND `institute_city` IN ( SELECT `institute_city` FROM `map_city` WHERE `zone` != 'ignore') ", $mysqli);
$migrated_out_house_visit_count = getCounts(
  "SELECT COUNT(*) `count` FROM (SELECT `migrated_out_hv`.`student_its` FROM `migrated_out_hv`
  LEFT JOIN `migrated_students` ON `migrated_out_hv`.`student_its` = `migrated_students`.`its_id`
  WHERE   `not_migrated` = '0' GROUP BY `migrated_out_hv`.`student_its`) q;",
  $mysqli
);

$migrated_out_house_visit_percent = ($total_migrated_out_house_visit_count > 0) ?  ($migrated_out_house_visit_count / $total_migrated_out_house_visit_count) * 100 : '-';


$new_identified_migrated_students_count = getCounts( "SELECT COUNT(*)  `count` FROM `migrated_students` WHERE `added_its` IS NOT NULL ", $mysqli);
// $total_meeting_photo_count = getCounts( "SELECT COUNT(*) `count` FROM `seminar_reports` WHERE `jamaat` = '$mauze' ", $mysqli);

$total_FMB_count = $house_visit_count;
$fmb_thali_percent = ($house_visit_count > 0) ?  ($total_FMB_count / $house_visit_count) * 100 : '-';

$taking_FMB_count = getCounts( "SELECT COUNT(*) AS `count` FROM (SELECT * FROM `hv_sub` WHERE `question_id` = 4 AND `answer` = 'YES' GROUP BY `student_its`) AS q", $mysqli);
$not_taking_FMB_count = getCounts( "SELECT COUNT(*) AS `count` FROM (SELECT * FROM `hv_sub` WHERE `question_id` = 4 AND `answer` = 'No' GROUP BY `student_its`) AS q;", $mysqli);
$total_migrated_student_count = getCounts( "SELECT COUNT(*) `count` FROM `migrated_students` WHERE `not_migrated` = 0 ", $mysqli);
$fmbThali = $taking_FMB_count + $not_taking_FMB_count;
$thaali_data_pending_count = $total_migrated_student_count - $fmbThali;

$total_hostel_committee_count = getCounts( "SELECT COUNT(*) `count` FROM `hostel_committee` ", $mysqli);
$total_hostel_accommodation_count = getCounts( "SELECT COUNT(*) `count` FROM `hostel_accommodation` ", $mysqli);
$total_hostel_new_count = getCounts( "SELECT COUNT(*) `count` FROM `hostel_new` ", $mysqli);

$total_guardians_count = getCounts( "SELECT COUNT(*) `count` FROM `supervisor_master` ", $mysqli);
$total_students_count = getCounts( "SELECT COUNT(*) `count` FROM `migrated_students` WHERE  `not_migrated` != 1", $mysqli);
$total_muraqib_tagging_count = getCounts( "SELECT COUNT(*) `count` FROM `migrated_students` WHERE `not_migrated` != 1 AND `supervisor` !=''", $mysqli);
$total_muraqib_tagging_percent = ($total_students_count > 0) ?  ($total_muraqib_tagging_count / $total_students_count) * 100 : '-';

$minhatQuery = "SELECT SUM(`total_amount`) AS `amount`, `currency` FROM `minhat_talimiyah` GROUP BY `currency`";
$minhatResult = mysqli_query($mysqli, $minhatQuery);
$minhatRow = $minhatResult->fetch_all(MYSQLI_ASSOC);

$today = date("Y-m-d");
$yesterday = date("Y-m-d", strtotime("-1 day"));

$migratedInCount = getCounts( "SELECT COUNT(*) `count` FROM `hv_master` ", $mysqli);
$migratedInTodayCount = getCounts( "SELECT  COUNT(*) `count` FROM `hv_master` WHERE added_ts LIKE '$today%' OR added_ts LIKE '$yesterday%'", $mysqli);

$migratedOutCount = getCounts( "SELECT COUNT(*) `count` FROM `migrated_out_hv` ", $mysqli);
$migratedOutTodayCount = getCounts( "SELECT  COUNT(*) `count` FROM `migrated_out_hv` WHERE added_ts LIKE '$today%' OR added_ts LIKE '$yesterday%'", $mysqli);

$identifiedCount = getCounts( "SELECT COUNT(*) `count` FROM `identified_students` ", $mysqli);
$identifiedTodayCount = getCounts( "SELECT  COUNT(*) `count` FROM `identified_students` WHERE added_ts LIKE '$today%' OR added_ts LIKE '$yesterday%'", $mysqli);

$muraqibCount = getCounts( "SELECT COUNT(*) `count` FROM `supervisor_master` ", $mysqli);
?>

<main id="main" class="main">

  <div class="pagetitle">
    <h1>Master Report of all Mawaze</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
        <li class="breadcrumb-item active">Dashboard</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  <section class="section dashboard">
    <div class="row">

        <div class="col-lg-12">
            <div class="card info-card sales-card pt-4 pb-0">
                <div class="card-body">
                    <div class="row">
                        <div class="pagetitle">
                            <h1>Huzur e Aala Report</h1>
                        </div>
                        <div class="col-xxl-3 col-md-6">
                            <div class="card info-card sales-card pb-0">
                                <div class="card-body">
                                    <h5 class="card-title">Migrated In Count</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-arrow-90deg-down"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6><?= $migratedInCount?></h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-md-6">
                            <div class="card info-card sales-card pb-0">
                                <div class="card-body">
                                    <h5 class="card-title">Migrated In Today Count</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-calendar-check"></i>
                                        </div>
                                        <div class="ps-3">
                                        <h6><?= $migratedInTodayCount ?></h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-md-6">
                                <div class="card info-card sales-card pb-0">
                                    <div class="card-body">
                                        <h5 class="card-title">Migrated Out Count</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-arrow-90deg-right"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6><?= $migratedOutCount?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                        </div>

                        <div class="col-xxl-3 col-md-6">
                            <div class="card info-card sales-card pb-0">
                                <div class="card-body">
                                    <h5 class="card-title">Migrated Out Today Count</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-calendar-check"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6><?= $migratedOutTodayCount?></h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-md-6">
                            <div class="card info-card sales-card pb-0">
                                <div class="card-body">
                                    <h5 class="card-title">Identified Student Count</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bx bx-bookmark"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6><?= $identifiedCount?></h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-md-6">
                            <div class="card info-card sales-card pb-0">
                                <div class="card-body">
                                    <h5 class="card-title">Identified Student Today Count</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-calendar-check"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6><?= $identifiedTodayCount?></h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-md-6">
                            <div class="card info-card sales-card pb-0">
                                <div class="card-body">
                                    <h5 class="card-title">Muraqib Count</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bx bx-bookmark"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6><?= $muraqibCount?></h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card info-card sales-card pt-4 pb-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xxl-3 col-md-6">
                            <a href="<?= MODULE_PATH?>admin/manage_house_visits.php">
                                <div class="card info-card sales-card pb-0">
                                    <div class="card-body">
                                        <h5 class="card-title">House Visits - Migrated In (Students)</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bx bx-book-bookmark"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6><?= $house_visit_count . ' / ' . $house_visit_total_count ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    if($house_visit_percent != '-'){
                                        echo "<div class=\"progress-bar-bottom\" style=\"width: $house_visit_percent%;\" aria-valuenow=\"$house_visit_count\" aria-valuemin=\"0\" aria-valuemax=\"$house_visit_total_count\"></div>";
                                    }
                                    ?>
                                </div>
                            </a>
                        </div>

                        <div class="col-xxl-3 col-md-6">
                            <div class="card info-card sales-card pb-0">
                                <div class="card-body">
                                    <h5 class="card-title">House Visits - Migrated Out (Parents)</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bx bx-book-bookmark"></i>
                                        </div>
                                        <div class="ps-3">
                                        <h6><?= $migrated_out_house_visit_count . ' / ' . $total_migrated_out_house_visit_count ?></h6>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                if($migrated_out_house_visit_percent != '-'){
                                    echo "<div class=\"progress-bar-bottom\" style=\"width: $migrated_out_house_visit_percent%;\" aria-valuenow=\"$migrated_out_house_visit_count\" aria-valuemin=\"0\" aria-valuemax=\"$total_migrated_out_house_visit_count\"></div>";
                                }
                                ?>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-md-6">
                                <div class="card info-card customers-card pb-0">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Migrated Students</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bx bx-bookmark"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6><?= $house_visit_total_count?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                        </div>

                        <div class="col-xxl-3 col-md-6">
                            <a href="<?= MODULE_PATH?>admin/manage_identified_students.php">
                                <div class="card info-card customers-card pb-0">
                                    <div class="card-body">
                                        <h5 class="card-title">New Identified Migrated Students</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bx bx-bookmark"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6><?= $new_identified_migrated_students_count?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card info-card sales-card pt-4 pb-0">
                <div class="card-body">
                    <div class="row">
                        <div class="pagetitle">
                            <h1>FMB</h1>
                        </div>
                        <div class="col-xxl-4 col-md-6">
                        <div class="card info-card sales-card pb-0">
                            <div class="card-body">
                            <h5 class="card-title">Taking F.M.B</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bx bx-book-bookmark"></i>
                                </div>
                                <div class="ps-3">
                                <h6><?= $taking_FMB_count ?></h6>
                                </div>
                            </div>
                            </div>
                        </div>
                        </div>

                        <div class="col-xxl-4 col-md-6">
                            <div class="card info-card customers-card pb-0">
                                <div class="card-body">
                                <h5 class="card-title">Not Taking F.M.B</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-bookmark"></i>
                                    </div>
                                    <div class="ps-3">
                                    <h6><?= $not_taking_FMB_count?></h6>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-4 col-md-6" >
                            <div class="card info-card revenue-card pb-0">
                                <div class="card-body">
                                    <h5 class="card-title">Thaali Data Pending</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bx bxs-book-reader"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?= $thaali_data_pending_count ?></h6>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card info-card sales-card pt-4 pb-0">
                <div class="card-body">
                    <div class="row">
                        <div class="pagetitle">
                            <h1>Hostel Reporting Structure</h1>
                        </div>
                        <div class="col-xxl-4 col-md-6">
                            <!-- <a href="<?= MODULE_PATH?>hostel_visits.php"> -->
                            <div class="card info-card sales-card pb-0">
                                <div class="card-body">
                                <h5 class="card-title">Hostel Committee Members</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-book-bookmark"></i>
                                    </div>
                                    <div class="ps-3">
                                    <h6><?= $total_hostel_committee_count ?></h6>
                                    </div>
                                </div>
                                </div>
                            </div>
                            <!-- </a> -->
                        </div>

                        <div class="col-xxl-4 col-md-6">
                            <!-- <a href="<?= MODULE_PATH?>identify_existing_accommodation.php"> -->
                            <div class="card info-card customers-card pb-0">
                                <div class="card-body">
                                <h5 class="card-title">Existing Accommodation Facilities in Mawze</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-bookmark"></i>
                                    </div>
                                    <div class="ps-3">
                                    <h6><?= $total_hostel_accommodation_count?></h6>
                                    </div>
                                </div>
                                </div>
                            </div>
                            <!-- </a> -->
                        </div>

                        <div class="col-xxl-4 col-md-6" >
                            <!-- <a href="<?= MODULE_PATH?>new_hostel.php"> -->
                            <div class="card info-card revenue-card pb-0">
                                <div class="card-body">
                                    <h5 class="card-title">New Hostel Project Documents</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bx bxs-book-reader"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?= $total_hostel_new_count ?></h6>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <!-- </a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card info-card sales-card pt-4 pb-0">
                <div class="card-body">
                    <div class="row">
                        <div class="pagetitle">
                            <h1>Muraqabat Nizaam</h1>
                        </div>
                        <div class="col-xxl-4 col-md-6">
                            <!-- <a href="<?= MODULE_PATH?>supervisors.php"> -->
                            <div class="card info-card sales-card pb-0">
                                <div class="card-body">
                                <h5 class="card-title">Guardians (Muraqebeen)</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-book-bookmark"></i>
                                    </div>
                                    <div class="ps-3">
                                    <h6><?= $total_guardians_count ?></h6>
                                    </div>
                                </div>
                                </div>
                            </div>
                            <!-- </a> -->
                        </div>

                        <div class="col-xxl-4 col-md-6">
                            <!-- <a href="<?= MODULE_PATH?>muraqib_tagging.php"> -->
                            <div class="card info-card sales-card pb-0">
                                <div class="card-body">
                                <h5 class="card-title">Muraqib Tagging</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-book-bookmark"></i>
                                    </div>
                                    <div class="ps-3">
                                    <h6><?= $total_muraqib_tagging_count . ' / ' . $total_students_count ?></h6>
                                    </div>
                                </div>
                                </div>
                                <?php
                                if($total_muraqib_tagging_percent != '-'){
                                    echo "<div class=\"progress-bar-bottom\" style=\"width: $total_muraqib_tagging_percent%;\" aria-valuenow=\"$total_muraqib_tagging_count\" aria-valuemin=\"0\" aria-valuemax=\"$total_students_count\"></div>";
                                }
                                ?>
                            </div>
                            <!-- </a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card info-card sales-card pt-4 pb-0">
                <div class="card-body">
                    <div class="row">
                        <div class="pagetitle">
                            <h1>Minhat Collection</h1>
                        </div>
                        <?php
                            if($minhatRow){
                            foreach ($minhatRow as $key => $data) {
                        ?>
                                <div class="col-xxl-3 col-md-6">
                                <a href="<?= MODULE_PATH?>admin/manage_minhat_talimiyah.php">
                                    <div class="card info-card revenue-card  pb-0">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= $data['currency'] ?></h5>
                                        <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bx bx-rupee"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6><?= number_format($data['amount'], 2)?></h6>
                                        </div>
                                        </div>
                                    </div>
                                    </div>
                                </a>
                                </div>
                        <?php
                            }
                            } else{
                        ?>
                            <div class="col-xxl-3 col-md-6">
                            <a href="<?= MODULE_PATH?>admin/manage_minhat_talimiyah.php">
                                <div class="card info-card revenue-card  pb-0">
                                <div class="card-body">
                                    <h5 class="card-title">Minhat Talimiyah</h5>
                                    <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bx bx-rupee"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>0</h6>
                                    </div>
                                    </div>
                                </div>
                                </div>
                            </a>
                            </div>
                        <?php
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>

      <!-- Left side columns -->
      <div class="col-lg-12">
        <?php
            $query = "SELECT *  FROM `seminar_reports`";
            $result = mysqli_query($mysqli, $query);
            $row = $result->fetch_all(MYSQLI_ASSOC);

        ?>
        <div class="row">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Meetings Photo Report</h5>
                    <table class="table table-striped" id="datatable10">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Barnamaj</th>
                                <th scope="col">Haazereen</th>
                                <th scope="col">Date</th>
                                <th scope="col">Uploads</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($row as $key => $data) {
                                    $submit_date = strtotime($data['date_of_barnamaj']);
                                    $date = date('Y-m-d', $submit_date);
                            ?>
                                    <tr>
                                        <th scope="row"><?= $data['id'] ?></th>
                                        <td><?= $data['barnamaj'] ?></td>
                                        <td><?= $data['haazereen_count'] ?></td>
                                        <td><?= $date ?></td>
                                        <td><?= $data['uploaded_file_count'] ?></td>
                                    </tr>
                            <?php
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
      </div><!-- End Left side columns -->

<!--
        <div class="card">
            <div class="card-body p-2 text-center">
                <a href="csvdownload.php" class="btn btn-primary"><i class="dwn"></i> Download Latest Report</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body p-2 text-center">
                <a href="seminarcsvdownload.php" class="btn btn-primary"><i class="dwn"></i> Download Seminar Report</a>
            </div>
        </div> -->
  </section>

</main><!-- End #main -->

<?php
require_once(__DIR__ . '/inc/footer.php');
?>

<?php
require_once(__DIR__ . '/inc/js-block.php');
?>
<script>
    $(document).ready(function () {
    $('#datatable10').DataTable( {
      dom: 'Bfrtip',
      pageLength: 10,
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, 'All']
      ],
      buttons: [
        {
          extend: 'copy',
          filename: function() {
              return 'meeting_photo_report -'  + Date.now();
          }
        },
        {
          extend: 'csv',
          filename: function() {
              return 'meeting_photo_report -'  + Date.now();
          }
        },
        {
          extend: 'excel',
          filename: function() {
              return 'meeting_photo_report -'  + Date.now();
          }
        }
      ]
    });
});
</script>