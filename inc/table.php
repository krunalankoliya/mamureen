<?php
require_once(__DIR__ . '/../session.php');

$query = "SELECT its_id,miqaat_mauze,miqaat_jamiat,`zone`,fullname FROM users_mamureen WHERE its_id = $user_its UNION SELECT its_id,miqaat_mauze,miqaat_jamiat,`zone`,fullname FROM users_admin WHERE its_id = $user_its";
$result = mysqli_query($mysqli, $query);
$rowdata = $result->fetch_assoc();

$mauze = $rowdata['miqaat_mauze'];
$zone = $rowdata['zone'];
$jamiat = $rowdata['miqaat_jamiat'];

function getCounts($query, $mysqli)
{
    $result = mysqli_query($mysqli, $query);
    $res = $result->fetch_assoc();
    return ($res && $res['count']) ? (int) $res['count'] : 0;
}
// ================ Zonal counts Start ===============
$new_identified_migrated_in_students_count = getCounts("SELECT COUNT(*) `count` FROM `identified_students` WHERE  `institute_city` IN (SELECT `institute_city` FROM `map_city` WHERE `zone` = '$zone' ) AND added_its IN (SELECT its_id FROM users_mamureen WHERE zone = '$zone')", $mysqli);
$new_identified_migrated_out_students_count = getCounts("SELECT COUNT(*) `count` FROM `identified_students` WHERE institute_city  NOT IN (SELECT `institute_city` FROM `map_city` WHERE `zone` = '$zone' ) AND added_its IN (SELECT its_id FROM users_mamureen WHERE zone = '$zone')", $mysqli);
$house_visit_count = getCounts(
    "SELECT COUNT(*) `count` FROM (SELECT `hv_sub`.`student_its` FROM `hv_sub` LEFT JOIN `migrated_students` ON `hv_sub`.`student_its` = `migrated_students`.`its_id`  WHERE institute_city IN (SELECT `institute_city` FROM `map_city` WHERE `zone` = '$zone' ) GROUP BY `hv_sub`.`student_its`) q;",
    $mysqli
);
$migrated_out_house_visit_count = getCounts(
    "SELECT COUNT(*) `count` FROM (SELECT `migrated_out_hv`.`student_its` FROM `migrated_out_hv`
  LEFT JOIN `migrated_students` ON `migrated_out_hv`.`student_its` = `migrated_students`.`its_id`
  WHERE institute_city NOT IN (SELECT `institute_city` FROM `map_city` WHERE `zone` = '$zone' ) AND `jamaat` IN  (SELECT `miqaat_mauze` FROM `users_mamureen` WHERE `zone` = '$zone')
GROUP BY `migrated_out_hv`.`student_its`) q;",
    $mysqli
);
$taking_FMB_count = getCounts("SELECT COUNT(*) `count` FROM (SELECT * FROM `hv_sub` WHERE `question_id` = 4 AND `answer` = 'YES' AND student_its IN (SELECT its_id FROM `migrated_students` WHERE `institute_city` IN (SELECT `institute_city` FROM `map_city` WHERE `zone` = '$zone' )) GROUP BY `student_its`) q;", $mysqli);
$total_muraqib_tagging_count = getCounts("SELECT COUNT(*) `count` FROM `migrated_students` WHERE institute_city  IN (SELECT `institute_city` FROM `map_city` WHERE `zone` = '$zone' ) AND `not_migrated` != 1 AND `supervisor` !=''", $mysqli);
$total_guardians_count = getCounts("SELECT COUNT(*) `count` FROM `supervisor_master` WHERE`jamaat` LIKE '%$zone%' OR `jamaat` IN (SELECT `miqaat_mauze` FROM `users_mamureen`  WHERE  `zone` = '$zone')  ", $mysqli);
$total_hostel_accommodation_count = getCounts("SELECT COUNT(*) `count` FROM `accommodation_survey` WHERE `its_id` IN (SELECT its_id FROM users_mamureen WHERE zone = '$zone')", $mysqli);
$new_hostel_proposal_count = getCounts("SELECT COUNT(*) `count` FROM `hostel_accommodation` WHERE added_its IN (SELECT its_id FROM users_mamureen WHERE zone = '$zone')", $mysqli);
$minhat_taalimiyah_participants_count = getCounts("SELECT SUM(mumin_count) `count` FROM `minhat_talimiyah` WHERE user_its IN (SELECT its_id FROM users_mamureen WHERE zone = '$zone');", $mysqli);
// ================ Zonal counts END ===============

// ================ Mauze counts START ===============
$new_identified_migrated_in_students_mauze_count = getCounts("SELECT COUNT(*) `count` FROM `identified_students` WHERE  `institute_city` IN (SELECT `institute_city` FROM `map_city` WHERE `zone` = '$zone' ) AND added_its IN (SELECT its_id FROM users_mamureen WHERE miqaat_mauze = '$mauze')", $mysqli);
$new_identified_migrated_out_students_mauze_count = getCounts("SELECT COUNT(*) `count` FROM `identified_students` WHERE institute_city  NOT IN (SELECT `institute_city` FROM `map_city` WHERE `zone` = '$zone' ) AND added_its IN (SELECT its_id FROM users_mamureen WHERE miqaat_mauze = '$mauze')", $mysqli);
$house_visit_mauze_count = getCounts(
    "SELECT COUNT(*) `count` FROM (SELECT `hv_sub`.`student_its` FROM `hv_sub` LEFT JOIN `migrated_students` ON `hv_sub`.`student_its` = `migrated_students`.`its_id`  WHERE institute_city IN (SELECT `institute_city` FROM `map_city` WHERE `zone` = '$zone' ) AND visitor_its IN (SELECT its_id FROM users_mamureen WHERE miqaat_mauze = '$mauze') GROUP BY `hv_sub`.`student_its`) q;",
    $mysqli
);
$migrated_out_house_visit_mauze_count = getCounts(
    "SELECT COUNT(*) `count` FROM (SELECT `migrated_out_hv`.`student_its` FROM `migrated_out_hv`
  LEFT JOIN `migrated_students` ON `migrated_out_hv`.`student_its` = `migrated_students`.`its_id`
  WHERE institute_city NOT IN (SELECT `institute_city` FROM `map_city` WHERE `zone` = '$zone' ) AND `jamaat` IN  (SELECT `miqaat_mauze` FROM `users_mamureen` WHERE `zone` = '$zone')
  AND visitor_its IN (SELECT its_id FROM users_mamureen WHERE miqaat_mauze = '$mauze')
GROUP BY `migrated_out_hv`.`student_its`) q;",
    $mysqli
);
$taking_FMB_mauze_count = getCounts("SELECT COUNT(*) `count` FROM (SELECT * FROM `hv_sub` WHERE `question_id` = 4 AND `answer` = 'YES' AND visitor_its IN (SELECT its_id FROM users_mamureen WHERE miqaat_mauze = '$mauze') AND student_its IN (SELECT its_id FROM `migrated_students` WHERE `institute_city` IN (SELECT `institute_city` FROM `map_city` WHERE `zone` = '$zone' )) GROUP BY `student_its`) q;", $mysqli);
$total_hostel_accommodation_mauze_count = getCounts("SELECT COUNT(*) `count` FROM `accommodation_survey` WHERE `its_id` IN (SELECT its_id FROM users_mamureen WHERE miqaat_mauze = '$mauze')", $mysqli);

$new_hostel_proposal_mauze_count = getCounts("SELECT COUNT(*) `count` FROM `hostel_accommodation` WHERE added_its IN (SELECT its_id FROM users_mamureen WHERE miqaat_mauze = '$mauze')", $mysqli);
$minhat_taalimiyah_participants_mauze_count = getCounts("SELECT SUM(mumin_count) `count` FROM `minhat_talimiyah` WHERE user_its IN (SELECT its_id FROM users_mamureen WHERE miqaat_mauze = '$mauze');", $mysqli);
// ================ mauze counts END ===============
?>
<div class="col-md-12">
    <!-- <h1>Will be live soon..</h1> -->
    <h5 class="card-title">Tasks achievement in Shehrullah 1445H</h5>
    <table class="table table-bordered cell-border" id="datatable0">
        <thead>
            <tr>
                <th scope="col">SR. NO</th>
                <th scope="col">Task</th>
                <th scope="col">Mawze report</th>
                <th scope="col">Zonal report</th>

            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Data collection (In)</td>
                <td><?= $new_identified_migrated_in_students_mauze_count ?></td>
                <td><?= $new_identified_migrated_in_students_count ?></td>
            </tr>
            <tr>
                <td>2</td>
                <td>Data collection (Out)</td>
                <td><?= $new_identified_migrated_out_students_mauze_count ?></td>
                <td><?= $new_identified_migrated_out_students_count ?></td>
            </tr>
            <tr>
                <td>3</td>
                <td>House visit (in)</td>
                <td><?= $house_visit_mauze_count ?></td>
                <td><?= $house_visit_count ?></td>
            </tr>
            <tr>
                <td>4</td>
                <td>Tafheem/House visit (out)</td>
                <td><?= $migrated_out_house_visit_mauze_count ?></td>
                <td><?= $migrated_out_house_visit_count ?></td>
            </tr>
            <tr>
                <td>5</td>
                <td>Recieves FMB thaali</td>
                <td><?= $taking_FMB_mauze_count ?></td>
                <td><?= $taking_FMB_count ?></td>
            </tr>
            <tr>
                <td>6</td>
                <td>Muraqibeen identification</td>
                <td>N/A</td>
                <td><?= $total_guardians_count ?></td>
            </tr>
            <tr>
                <td>7</td>
                <td>Muraqibeen tagging</td>
                <td>N/A</td>
                <td><?= $total_muraqib_tagging_count  ?></td>
            </tr>
            <tr>
                <td>8</td>
                <td>Existing accommodations identified</td>
                <td><?= $total_hostel_accommodation_mauze_count ?></td>
                <td><?= $total_hostel_accommodation_count ?></td>
            </tr>
            <tr>
                <td>9</td>
                <td>Proposal for hostel </td>
                <td><?= $new_hostel_proposal_mauze_count ?></td>
                <td><?= $new_hostel_proposal_count ?></td>
            </tr>
            <tr>
                <td>10</td>
                <td>Participants in Minhat Taalimiyah</td>
                <td><?= $minhat_taalimiyah_participants_mauze_count ?></td>
                <td><?= $minhat_taalimiyah_participants_count ?></td>
            </tr>

        </tbody>
    </table>
    <div class="text-center">
        <a href="?form_id=form_1" class="btn btn-success">Next</a>
    </div>
</div>