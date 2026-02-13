<?php

function display_header($jamaat, $jamiat)
{
    return '
    <div class="row">
            <div class="col-3 text-center">
                <img src="/mamureen/assets/img/logo_1.jpeg" class="img-fluid rounded float-left">
                <!-- img src="/mamureen/assets/img/logo_1.jpeg" style="max-height: 150px" / -->
            </div>
            <div class="col-6 text-center m-auto">
                <h3>' . $jamaat . '</h3>
                <h3>' . $jamiat . '</h3>
            </div>
            <div class="col-3 text-left">
                <img src="/mamureen/assets/img/logo_2.jpeg" class="img-fluid rounded float-right">
                <!-- img src="/mamureen/assets/img/logo_2.jpeg" style="max-height: 150px" / -->
            </div>
        </div>
    ';
}

function display_closure_header($jamaat)
{
    return '
    <div class="row">
        <div class="col-6 text-center m-auto">
            <h3>' . $jamaat . '</h3>
        </div>
    </div>
    ';
}

function display_date_range($startDate, $endDate)
{
    return '
        <div class="row">
            <div class="col">
                <h5 class="card-title text-left">Date : ' . $startDate . ' - ' . $endDate . '</h5>
            </div>
        </div>
    ';
}

function display_info_card($title, int $achived, $target = false, $is_seminar = false)
{
    $display_target = $target ? " / $target" : '';
    // if( $achived <= 0 ) return '';
    if($is_seminar){
        $display_target = "$achived Programs<br>$target attendees";
    }else{
        $display_target = $target ? "$achived / $target" : '';
    }
    return '
        <div class="col-4">
            <div class="card info-card customers-card">
                <div class="card-body">
                    <h5 class="card-title">' . $title . '</h5>
                    <div class="d-flex align-items-center">
                        <div class="ps-3">
                            <h6>' . $display_target . '</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    ';
}

function disply_report_question($jamaat, $report_name, $mysqli){
    $query = "SELECT * FROM `closure_report` WHERE `jamaat` = '$jamaat' AND  `report_name` = '$report_name' ";
    $result = mysqli_query($mysqli, $query);
    $res = $result->fetch_all(MYSQLI_ASSOC);
    return $res;
}

function user_detail($its_id, $mysqli){
    $query = "SELECT its_id FROM users_mamureen WHERE its_id = 50476733 UNION SELECT its_id FROM users_admin WHERE its_id = '$its_id' ";
    $result = mysqli_query($mysqli, $query);
    $res = $result->fetch_all(MYSQLI_ASSOC);
    return $res;
}
